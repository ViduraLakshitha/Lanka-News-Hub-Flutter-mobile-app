import 'dart:io';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../models/classified_model.dart';
import '../models/category_model.dart';
import '../core/network/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/storage/storage_service.dart';
import '../core/analytics/analytics_service.dart';
import '../core/crash/crash_service.dart';

class ClassifiedProvider extends ChangeNotifier {
  List<ClassifiedModel> _classifieds = [];
  List<ClassifiedModel> _myAds = [];
  List<ClassifiedModel> _favorites = [];
  List<CategoryModel> _categories = [];

  // Summary counts for My Ads Dashboard
  int _activeCount = 0;
  int _pendingCount = 0;
  int _expiredCount = 0;

  // Filter and pagination state
  bool _isLoading = false;
  bool _isLoadingMore = false;
  bool _hasMore = true;
  int _currentPage = 1;
  String? _errorMessage;

  // Filter params
  String? _selectedCategory;
  String _searchQuery = '';
  String _locationFilter = '';
  double? _minPrice;
  double? _maxPrice;
  String _sortBy = 'newest'; // 'newest', 'price_asc', 'price_desc', 'premium'

  // Getters
  List<ClassifiedModel> get classifieds => _classifieds;
  List<ClassifiedModel> get myAds => _myAds;
  List<ClassifiedModel> get favorites => _favorites;
  List<CategoryModel> get categories => _categories;
  int get activeCount => _activeCount;
  int get pendingCount => _pendingCount;
  int get expiredCount => _expiredCount;
  bool get isLoading => _isLoading;
  bool get isLoadingMore => _isLoadingMore;
  bool get hasMore => _hasMore;
  String? get errorMessage => _errorMessage;

  String? get selectedCategory => _selectedCategory;
  String get searchQuery => _searchQuery;
  String get locationFilter => _locationFilter;
  double? get minPrice => _minPrice;
  double? get maxPrice => _maxPrice;
  String get sortBy => _sortBy;

  ClassifiedProvider() {
    initClassifieds();
  }

  Future<void> initClassifieds() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await Future.wait([
        fetchCategories(),
        fetchClassifieds(refresh: true),
      ]);
      if (StorageService.getToken() != null) {
        await Future.wait([
          fetchMyAds(),
          fetchFavorites(),
        ]);
      }
    } catch (e) {
      _errorMessage = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchCategories() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.bootstrap,
        cacheDuration: const Duration(minutes: 10),
      );
      final data = res['data'];
      if (data['classified_categories'] is List) {
        _categories = (data['classified_categories'] as List)
            .map((c) => CategoryModel.fromMap(c as Map<String, dynamic>))
            .toList();
      }
    } catch (_) {}
  }

  Future<void> fetchClassifieds({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _hasMore = true;
    }

    try {
      final query = <String, dynamic>{
        'page': _currentPage,
        'limit': 15,
        'sort': _sortBy,
      };

      if (_selectedCategory != null && _selectedCategory!.isNotEmpty) {
        query['category'] = _selectedCategory;
      }
      if (_searchQuery.isNotEmpty) {
        query['q'] = _searchQuery;
      }
      if (_locationFilter.isNotEmpty) {
        query['location'] = _locationFilter;
      }
      if (_minPrice != null) {
        query['min_price'] = _minPrice;
      }
      if (_maxPrice != null) {
        query['max_price'] = _maxPrice;
      }

      final res = await ApiClient.get(
        ApiConstants.classifieds,
        queryParams: query,
        cacheDuration: const Duration(minutes: 2),
        forceRefresh: refresh,
      );

      final List rawList = res['data'] ?? [];
      final savedAdIds = StorageService.getSavedAdIds();

      final newItems = rawList.map((map) {
        final ad = ClassifiedModel.fromMap(map as Map<String, dynamic>);
        ad.isFavorite = savedAdIds.contains(ad.id) || ad.isFavorite;
        return ad;
      }).toList();

      if (refresh) {
        _classifieds = newItems;
      } else {
        _classifieds.addAll(newItems);
      }

      final pagination = res['pagination'];
      if (pagination != null) {
        _hasMore = pagination['has_more'] == true;
      } else {
        _hasMore = newItems.length >= 15;
      }
    } catch (e) {
      _errorMessage = e.toString();
    }
  }

  Future<void> loadMore() async {
    if (_isLoadingMore || !_hasMore) return;
    _isLoadingMore = true;
    _currentPage++;
    notifyListeners();

    await fetchClassifieds(refresh: false);

    _isLoadingMore = false;
    notifyListeners();
  }

  void setFilter({
    String? category,
    String? query,
    String? location,
    double? minPrice,
    double? maxPrice,
    String? sortBy,
  }) {
    _selectedCategory = category;
    if (query != null) _searchQuery = query;
    if (location != null) _locationFilter = location;
    _minPrice = minPrice;
    _maxPrice = maxPrice;
    if (sortBy != null) _sortBy = sortBy;
    fetchClassifieds(refresh: true);
    notifyListeners();
  }

  void resetFilters() {
    _selectedCategory = null;
    _searchQuery = '';
    _locationFilter = '';
    _minPrice = null;
    _maxPrice = null;
    _sortBy = 'newest';
    fetchClassifieds(refresh: true);
    notifyListeners();
  }

  // --- Submit Advertisement ---
  Future<bool> submitAdvertisement({
    required int categoryId,
    required String language,
    required String title,
    required String description,
    required String location,
    double? price,
    required String contactName,
    required String contactEmail,
    required String contactPhone,
    required bool premiumRequested,
    required List<File> imageFiles,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final fields = <String, String>{
        'category_id': categoryId.toString(),
        'language': language,
        'title': title,
        'description': description,
        'location': location,
        'contact_name': contactName,
        'contact_email': contactEmail,
        'contact_phone': contactPhone,
        'premium_requested': premiumRequested ? '1' : '0',
      };
      if (price != null) {
        fields['price'] = price.toString();
      }

      await ApiClient.multipart(
        ApiConstants.classifieds,
        fields: fields,
        files: imageFiles,
      );

      AnalyticsService.logAdSubmission(
        category: categoryId.toString(),
        isPremium: premiumRequested,
      );

      await fetchMyAds();
      _isLoading = false;
      notifyListeners();
      return true;
    } catch (e) {
      _isLoading = false;
      _errorMessage = e.toString();
      CrashService.recordError(e, null, reason: 'Ad submission failed');
      notifyListeners();
      return false;
    }
  }

  // --- Renew Advertisement ---
  Future<bool> renewAdvertisement(int adId) async {
    try {
      await ApiClient.post('/classifieds/$adId/renew');
      await fetchMyAds();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      return false;
    }
  }

  // --- Withdraw / Delete Advertisement ---
  Future<bool> withdrawAdvertisement(int adId) async {
    try {
      await ApiClient.post('/classifieds/$adId/delete');
      await fetchMyAds();
      return true;
    } catch (e) {
      _errorMessage = e.toString();
      return false;
    }
  }

  // --- Fetch My Ads Dashboard ---
  Future<void> fetchMyAds() async {
    try {
      final res = await ApiClient.get(ApiConstants.accountAds, forceRefresh: true);
      final data = res['data'];
      final List rawList = data['ads'] ?? [];
      _myAds = rawList.map((e) => ClassifiedModel.fromMap(e as Map<String, dynamic>)).toList();

      final summary = data['summary'];
      if (summary != null) {
        _activeCount = summary['active'] ?? 0;
        _pendingCount = summary['pending'] ?? 0;
        _expiredCount = summary['expired'] ?? 0;
      }
      notifyListeners();
    } catch (_) {}
  }

  // --- Toggle Favorite Ad ---
  Future<void> toggleFavorite(ClassifiedModel ad) async {
    final id = ad.id;
    await StorageService.toggleSavedAdId(id);
    ad.isFavorite = StorageService.isAdSaved(id);
    notifyListeners();

    try {
      if (StorageService.getToken() != null) {
        await ApiClient.post('/classifieds/$id/favorite');
        await fetchFavorites();
      }
    } catch (_) {}
  }

  // --- Fetch Favorites ---
  Future<void> fetchFavorites() async {
    try {
      final res = await ApiClient.get(ApiConstants.accountFavorites, forceRefresh: true);
      final List rawList = res['data'] ?? [];
      _favorites = rawList.map((e) {
        final item = ClassifiedModel.fromMap(e as Map<String, dynamic>);
        item.isFavorite = true;
        return item;
      }).toList();
      notifyListeners();
    } catch (_) {}
  }

  // --- Contact Advertiser Actions ---
  Future<void> callAdvertiser(String phone, int adId) async {
    AnalyticsService.logContactAction(adId: adId, actionType: 'call');
    final clean = phone.replaceAll(RegExp(r'[^\d+]'), '');
    final uri = Uri.parse('tel:$clean');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Future<void> whatsappAdvertiser(String phone, String title, int adId) async {
    AnalyticsService.logContactAction(adId: adId, actionType: 'whatsapp');
    final clean = phone.replaceAll(RegExp(r'[^\d]'), '');
    final message = Uri.encodeComponent("Hello, I am inquiring about your advertisement: '$title' on Lanka News Hub.");
    final uri = Uri.parse('https://wa.me/$clean?text=$message');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  Future<void> emailAdvertiser(String email, String title, int adId) async {
    AnalyticsService.logContactAction(adId: adId, actionType: 'email');
    final subject = Uri.encodeComponent("Inquiry: $title");
    final body = Uri.encodeComponent("Hello,\n\nI am contacting you regarding your advertisement on Lanka News Hub:\n$title\n\nPlease let me know if it is still available.\n\nThank you.");
    final uri = Uri.parse('mailto:$email?subject=$subject&body=$body');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }
}

