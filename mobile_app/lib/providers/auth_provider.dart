import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../core/network/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/storage/storage_service.dart';
import '../core/crash/crash_service.dart';

class AuthProvider extends ChangeNotifier {
  UserModel? _user;
  bool _isLoading = false;
  String? _errorMessage;

  UserModel? get user => _user;
  bool get isAuthenticated => _user != null && StorageService.getToken() != null;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  AuthProvider() {
    _autoLogin();
  }

  void _autoLogin() {
    final cached = StorageService.getUser();
    final token = StorageService.getToken();
    if (cached != null && token != null) {
      _user = UserModel.fromMap(cached, token: token);
      notifyListeners();
      // Fetch fresh profile in background
      fetchProfile();
    }
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await ApiClient.post(ApiConstants.login, body: {
        'email': email.trim().toLowerCase(),
        'password': password,
      });

      final data = res['data'];
      final token = data['token'] as String;
      final userMap = data['user'] as Map<String, dynamic>;

      await StorageService.saveToken(token);
      await StorageService.saveUser(userMap);

      _user = UserModel.fromMap(userMap, token: token);
      CrashService.addBreadcrumb('User logged in: ${_user?.email}');
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      CrashService.recordError(e, null, reason: 'Login failed');
      notifyListeners();
      return false;
    }
  }

  Future<bool> register({
    required String name,
    required String email,
    required String password,
    String? phone,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final res = await ApiClient.post(ApiConstants.register, body: {
        'name': name.trim(),
        'email': email.trim().toLowerCase(),
        'password': password,
        'phone': phone?.trim(),
      });

      final data = res['data'];
      final token = data['token'] as String;
      final userMap = data['user'] as Map<String, dynamic>;

      await StorageService.saveToken(token);
      await StorageService.saveUser(userMap);

      _user = UserModel.fromMap(userMap, token: token);
      CrashService.addBreadcrumb('User registered: ${_user?.email}');
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      CrashService.recordError(e, null, reason: 'Registration failed');
      notifyListeners();
      return false;
    }
  }

  Future<void> fetchProfile() async {
    try {
      final res = await ApiClient.get(ApiConstants.me);
      final userMap = res['data'] as Map<String, dynamic>;
      _user = UserModel.fromMap(userMap, token: StorageService.getToken());
      await StorageService.saveUser(userMap);
      notifyListeners();
    } catch (_) {}
  }

  Future<bool> updateProfile({String? name, String? password}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final body = <String, dynamic>{};
      if (name != null) body['name'] = name;
      if (password != null && password.isNotEmpty) body['password'] = password;

      final res = await ApiClient.post(ApiConstants.updateProfile, body: body);
      final userMap = res['data'] as Map<String, dynamic>;
      _user = UserModel.fromMap(userMap, token: StorageService.getToken());
      await StorageService.saveUser(userMap);

      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    await StorageService.removeToken();
    await StorageService.removeUser();
    _user = null;
    CrashService.addBreadcrumb('User signed out');
    notifyListeners();
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}
