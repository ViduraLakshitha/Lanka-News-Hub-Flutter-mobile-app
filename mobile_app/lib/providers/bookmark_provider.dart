import 'package:flutter/material.dart';
import '../models/article_model.dart';
import '../core/storage/storage_service.dart';
import '../core/network/api_client.dart';

class BookmarkProvider extends ChangeNotifier {
  List<int> _savedIds = [];
  List<ArticleModel> _offlineArticles = [];

  List<int> get savedIds => _savedIds;
  List<ArticleModel> get offlineArticles => _offlineArticles;

  BookmarkProvider() {
    _loadFromStorage();
  }

  void _loadFromStorage() {
    _savedIds = StorageService.getSavedArticleIds();
    _loadOfflineArticles();
    notifyListeners();
  }

  void _loadOfflineArticles() {
    final raw = StorageService.getOfflineArticles();
    _offlineArticles = raw.map((e) => ArticleModel.fromMap(e)).toList();
  }

  bool isSaved(int articleId) => _savedIds.contains(articleId);

  bool isOffline(int articleId) => StorageService.isArticleOffline(articleId);

  // Toggle bookmark / save article
  Future<void> toggleBookmark(ArticleModel article) async {
    final id = article.id;
    await StorageService.toggleSavedArticleId(id);
    _savedIds = StorageService.getSavedArticleIds();
    article.isSaved = _savedIds.contains(id);

    // Also download full article for offline reading if bookmarked
    if (article.isSaved) {
      await saveForOffline(article);
    } else {
      await removeOffline(id);
    }

    notifyListeners();

    // Sync with backend if user logged in
    try {
      if (StorageService.getToken() != null) {
        await ApiClient.post('/articles/$id/save');
      }
    } catch (_) {}
  }

  // Explicit Save for Offline Reading
  Future<void> saveForOffline(ArticleModel article) async {
    article.isOffline = true;
    await StorageService.saveOfflineArticle(article.toMap());
    _loadOfflineArticles();
    notifyListeners();
  }

  // Remove from Offline
  Future<void> removeOffline(int articleId) async {
    await StorageService.removeOfflineArticle(articleId);
    _loadOfflineArticles();
    notifyListeners();
  }
}
