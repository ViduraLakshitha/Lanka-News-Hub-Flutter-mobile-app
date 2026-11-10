import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../constants/api_constants.dart';
import '../storage/storage_service.dart';
import 'api_exceptions.dart';

class ApiClient {
  static final http.Client _client = http.Client();

  // In-memory cache for fast loading & API optimization
  static final Map<String, _CachedResponse> _memoryCache = {};

  static Map<String, String> _buildHeaders({Map<String, String>? extra}) {
    final headers = <String, String>{
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-App-Language': StorageService.getLanguage(),
    };
    final token = StorageService.getToken();
    if (token != null && token.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    if (extra != null) {
      headers.addAll(extra);
    }
    return headers;
  }

  static Uri _buildUri(String endpoint, [Map<String, dynamic>? queryParams]) {
    final baseUrl = ApiConstants.activeBaseUrl;
    final cleanEndpoint = endpoint.startsWith('/') ? endpoint : '/$endpoint';
    final urlString = '$baseUrl$cleanEndpoint';

    if (queryParams != null && queryParams.isNotEmpty) {
      final cleanParams = <String, String>{};
      queryParams.forEach((key, value) {
        if (value != null) {
          cleanParams[key] = value.toString();
        }
      });
      return Uri.parse(urlString).replace(queryParameters: cleanParams);
    }
    return Uri.parse(urlString);
  }

  // --- GET Request with Optional Memory Cache ---
  static Future<dynamic> get(
    String endpoint, {
    Map<String, dynamic>? queryParams,
    Duration? cacheDuration,
    bool forceRefresh = false,
  }) async {
    final uri = _buildUri(endpoint, queryParams);
    final cacheKey = uri.toString();

    // Check memory cache
    if (!forceRefresh && cacheDuration != null && _memoryCache.containsKey(cacheKey)) {
      final cached = _memoryCache[cacheKey]!;
      if (!cached.isExpired) {
        return cached.data;
      }
    }

    try {
      final response = await _client
          .get(uri, headers: _buildHeaders())
          .timeout(ApiConstants.timeout);

      final data = _handleResponse(response);

      if (cacheDuration != null) {
        _memoryCache[cacheKey] = _CachedResponse(data, DateTime.now().add(cacheDuration));
      }
      return data;
    } on SocketException {
      throw NetworkException();
    } on TimeoutException {
      throw NetworkException('Request timed out. Please try again.');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(e.toString());
    }
  }

  // --- POST Request ---
  static Future<dynamic> post(
    String endpoint, {
    Map<String, dynamic>? body,
    Map<String, dynamic>? queryParams,
  }) async {
    final uri = _buildUri(endpoint, queryParams);
    try {
      final response = await _client
          .post(
            uri,
            headers: _buildHeaders(),
            body: body != null ? jsonEncode(body) : null,
          )
          .timeout(ApiConstants.timeout);

      return _handleResponse(response);
    } on SocketException {
      throw NetworkException();
    } on TimeoutException {
      throw NetworkException('Request timed out. Please try again.');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(e.toString());
    }
  }

  // --- MULTIPART POST (For Classified Ads with Images) ---
  static Future<dynamic> multipart(
    String endpoint, {
    required Map<String, String> fields,
    List<File>? files,
  }) async {
    final uri = _buildUri(endpoint);
    try {
      final request = http.MultipartRequest('POST', uri);
      request.headers.addAll(_buildHeaders(extra: {'Content-Type': 'multipart/form-data'}));
      request.fields.addAll(fields);

      if (files != null && files.isNotEmpty) {
        for (var i = 0; i < files.length; i++) {
          final file = files[i];
          if (await file.exists()) {
            final multipartFile = await http.MultipartFile.fromPath('images[]', file.path);
            request.files.add(multipartFile);
          }
        }
      }

      final streamed = await request.send().timeout(const Duration(seconds: 45));
      final response = await http.Response.fromStream(streamed);
      return _handleResponse(response);
    } on SocketException {
      throw NetworkException();
    } on TimeoutException {
      throw NetworkException('Upload timed out. Please check your connection.');
    } catch (e) {
      if (e is ApiException) rethrow;
      throw ApiException(e.toString());
    }
  }

  // --- Response Parser ---
  static dynamic _handleResponse(http.Response response) {
    dynamic jsonBody;
    try {
      jsonBody = jsonDecode(utf8.decode(response.bodyBytes));
    } catch (_) {
      jsonBody = null;
    }

    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (jsonBody is Map<String, dynamic>) {
        return jsonBody;
      }
      return jsonBody;
    }

    if (response.statusCode == 401) {
      StorageService.removeToken();
      throw UnauthorizedException(jsonBody?['message'] ?? 'Unauthorized');
    }

    final message = jsonBody?['message'] ?? 'An error occurred (${response.statusCode})';
    final errors = jsonBody?['errors'];
    throw ApiException(message, statusCode: response.statusCode, errors: errors);
  }

  static void clearCache() {
    _memoryCache.clear();
  }
}

class _CachedResponse {
  final dynamic data;
  final DateTime expiresAt;

  _CachedResponse(this.data, this.expiresAt);

  bool get isExpired => DateTime.now().isAfter(expiresAt);
}

