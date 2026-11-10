import 'package:flutter/material.dart';
import '../models/article_model.dart';
import '../models/category_model.dart';
import '../models/live_update_model.dart';
import '../core/network/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/constants/starter_data.dart';
import '../core/storage/storage_service.dart';
import '../core/analytics/analytics_service.dart';
import '../core/crash/crash_service.dart';

class NewsProvider extends ChangeNotifier {
  // Feed state
  List<ArticleModel> _latestNews = [];
  List<ArticleModel> _breakingNews = [];
  List<ArticleModel> _featuredNews = [];
  List<ArticleModel> _videoNews = [];
  List<ArticleModel> _galleryNews = [];
  List<CategoryModel> _categories = [];
  List<LiveUpdateModel> _liveUpdates = [];

  // Search state
  List<ArticleModel> _searchResults = [];
  bool _isSearching = false;
  String _searchQuery = '';

  // Status flags
  bool _isLoading = false;
  bool _isLoadingMore = false;
  bool _hasMore = true;
  int _currentPage = 1;
  String? _errorMessage;
  bool _isOfflineMode = false;

  // Active Category filter
  String? _selectedCategorySlug;

  // Getters
  List<ArticleModel> get latestNews => _latestNews;
  List<ArticleModel> get breakingNews => _breakingNews;
  List<ArticleModel> get featuredNews => _featuredNews;
  List<ArticleModel> get videoNews => _videoNews;
  List<ArticleModel> get galleryNews => _galleryNews;
  List<CategoryModel> get categories => _categories;
  List<LiveUpdateModel> get liveUpdates => _liveUpdates;
  List<ArticleModel> get searchResults => _searchResults;
  bool get isSearching => _isSearching;
  String get searchQuery => _searchQuery;
  bool get isLoading => _isLoading;
  bool get isLoadingMore => _isLoadingMore;
  bool get hasMore => _hasMore;
  String? get errorMessage => _errorMessage;
  bool get isOfflineMode => _isOfflineMode;
  String? get selectedCategorySlug => _selectedCategorySlug;

  NewsProvider() {
    _loadStarterData();
    initApp();
  }

  void _loadStarterData() {
    if (_categories.isEmpty) {
      _categories = StarterData.categories.map((c) => CategoryModel.fromMap(c)).toList();
    }
    if (_latestNews.isEmpty) {
      final starter = StarterData.articles.map((a) => ArticleModel.fromMap(a)).toList();
      _latestNews = starter;
      _breakingNews = starter.where((a) => a.isBreaking).toList();
      _featuredNews = starter.where((a) => a.isFeatured).toList();
    }
  }

  Future<void> initApp() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await Future.wait([
        fetchBootstrap(),
        fetchLatestFeed(refresh: true),
        fetchBreakingNews(),
        fetchFeaturedNews(),
        fetchVideoNews(),
        fetchGalleryNews(),
        fetchLiveUpdates(),
      ]);
      _isOfflineMode = false;
    } catch (e) {
      _isOfflineMode = true;
      _errorMessage = 'Offline mode: showing saved/cached content';
      _loadFallbackOffline();
      CrashService.recordError(e, null, reason: 'Failed to fetch feeds');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void _loadFallbackOffline() {
    final offline = StorageService.getOfflineArticles();
    if (offline.isNotEmpty) {
      _latestNews = offline.map((e) => ArticleModel.fromMap(e)).toList();
    } else {
      _loadStarterData();
    }
  }

  Future<void> fetchBootstrap() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.bootstrap,
        cacheDuration: const Duration(minutes: 10),
      );
      final data = res['data'];
      if (data != null && data['categories'] is List) {
        final rawCats = (data['categories'] as List)
            .map((c) => CategoryModel.fromMap(c as Map<String, dynamic>))
            .toList();

        // Deduplicate and filter out empty duplicate category IDs (8 and 9)
        final seenNames = <String>{};
        final cleanList = <CategoryModel>[];
        for (final cat in rawCats) {
          if (cat.id == 8 || cat.id == 9) continue; // Skip duplicate dummy records

          final key = cat.nameEn.trim().toLowerCase();
          if (seenNames.contains(key)) continue;
          seenNames.add(key);
          cleanList.add(cat);
        }

        if (cleanList.isNotEmpty) {
          _categories = cleanList;
        }
      }
    } catch (_) {}
  }

  Future<void> fetchLatestFeed({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
      _hasMore = true;
    }

    try {
      final query = <String, dynamic>{
        'page': _currentPage,
        'limit': 15,
      };
      if (_selectedCategorySlug != null) {
        query['category'] = CategoryModel.resolveQueryParam(_selectedCategorySlug);
      }

      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: query,
        cacheDuration: const Duration(minutes: 2),
        forceRefresh: refresh,
      );

      final List rawList = res['data'] ?? [];
      final savedIds = StorageService.getSavedArticleIds();

      final newArticles = rawList.map((map) {
        final article = ArticleModel.fromMap(map as Map<String, dynamic>);
        article.isSaved = savedIds.contains(article.id);
        article.isOffline = StorageService.isArticleOffline(article.id);
        return article;
      }).toList();

      if (refresh) {
        _latestNews = newArticles;
      } else {
        _latestNews.addAll(newArticles);
      }

      final pagination = res['pagination'];
      if (pagination != null) {
        _hasMore = pagination['has_more'] == true;
      } else {
        _hasMore = newArticles.length >= 15;
      }
    } catch (e) {
      if (refresh && _latestNews.isEmpty) {
        _loadFallbackOffline();
      }
    }
  }

  Future<void> loadMore() async {
    if (_isLoadingMore || !_hasMore) return;
    _isLoadingMore = true;
    _currentPage++;
    notifyListeners();

    await fetchLatestFeed(refresh: false);

    _isLoadingMore = false;
    notifyListeners();
  }

  Future<void> selectCategory(String? slug) async {
    if (_selectedCategorySlug == slug) return;
    _selectedCategorySlug = slug;
    _isLoading = true;
    notifyListeners();

    await fetchLatestFeed(refresh: true);

    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchBreakingNews() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: {'section': 'breaking', 'limit': 6},
        cacheDuration: const Duration(minutes: 1),
      );
      final List raw = res['data'] ?? [];
      _breakingNews = raw.map((e) => ArticleModel.fromMap(e as Map<String, dynamic>)).toList();
    } catch (_) {}
  }

  Future<void> fetchFeaturedNews() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: {'section': 'featured', 'limit': 5},
        cacheDuration: const Duration(minutes: 5),
      );
      final List raw = res['data'] ?? [];
      _featuredNews = raw.map((e) => ArticleModel.fromMap(e as Map<String, dynamic>)).toList();
    } catch (_) {}
  }

  Future<void> fetchVideoNews() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: {'section': 'videos', 'limit': 8},
        cacheDuration: const Duration(minutes: 5),
      );
      final List raw = res['data'] ?? [];
      _videoNews = raw.map((e) => ArticleModel.fromMap(e as Map<String, dynamic>)).toList();
    } catch (_) {}
  }

  Future<void> fetchGalleryNews() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: {'section': 'gallery', 'limit': 6},
        cacheDuration: const Duration(minutes: 5),
      );
      final List raw = res['data'] ?? [];
      _galleryNews = raw.map((e) => ArticleModel.fromMap(e as Map<String, dynamic>)).toList();
    } catch (_) {}
  }

  Future<void> fetchLiveUpdates() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.liveUpdates,
        cacheDuration: const Duration(seconds: 30),
      );
      final List raw = res['data'] ?? [];
      _liveUpdates = raw.map((e) => LiveUpdateModel.fromMap(e as Map<String, dynamic>)).toList();
    } catch (_) {}
  }

  Future<ArticleModel?> fetchArticleDetail(int id, {String? slug}) async {
    try {
      final target = slug ?? id.toString();
      final res = await ApiClient.get('/news/$target');
      final data = res['data'] as Map<String, dynamic>;
      final article = ArticleModel.fromMap(data);
      article.isSaved = StorageService.isArticleSaved(article.id);
      article.isOffline = StorageService.isArticleOffline(article.id);

      // Track analytics
      AnalyticsService.logArticleRead(
        articleId: article.id,
        title: article.title,
        category: article.categoryName,
      );

      return article;
    } catch (e) {
      // Check offline store
      final offline = StorageService.getOfflineArticles();
      final match = offline.where((a) => a['id'] == id || a['slug'] == slug).firstOrNull;
      if (match != null) {
        return ArticleModel.fromMap(match);
      }
      return null;
    }
  }

  // --- Search Functionality ---
  Future<void> search(String query) async {
    final cleanQuery = query.trim();
    if (cleanQuery.isEmpty) {
      _searchResults = [];
      _searchQuery = '';
      _isSearching = false;
      notifyListeners();
      return;
    }

    _searchQuery = cleanQuery;
    _isSearching = true;
    notifyListeners();

    await StorageService.addRecentSearch(cleanQuery);
    AnalyticsService.logSearch(cleanQuery);

    try {
      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: {'q': cleanQuery, 'limit': 30},
      );
      final List raw = res['data'] ?? [];
      final savedIds = StorageService.getSavedArticleIds();
      _searchResults = raw.map((e) {
        final a = ArticleModel.fromMap(e as Map<String, dynamic>);
        a.isSaved = savedIds.contains(a.id);
        return a;
      }).toList();
    } catch (e) {
      _searchResults = [];
    } finally {
      _isSearching = false;
      notifyListeners();
    }
  }

  void clearSearch() {
    _searchResults = [];
    _searchQuery = '';
    _isSearching = false;
    notifyListeners();
  }
}

