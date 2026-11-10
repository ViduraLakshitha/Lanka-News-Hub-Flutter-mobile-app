import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  static late SharedPreferences _prefs;

  static Future<void> init() async {
    _prefs = await SharedPreferences.getInstance();
  }

  // Keys
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'cached_user';
  static const String _keyLanguage = 'selected_language';
  static const String _keyThemeMode = 'theme_mode';
  static const String _keyFontScale = 'font_scale';
  static const String _keySavedArticleIds = 'saved_article_ids';
  static const String _keyOfflineArticles = 'offline_articles_cache';
  static const String _keySavedAdIds = 'saved_classified_ids';
  static const String _keyRecentSearches = 'recent_searches';
  static const String _keyNotifPrefs = 'notification_preferences';
  static const String _keyBaseUrl = 'custom_base_url';

  // --- Base URL Configuration ---
  static Future<void> saveBaseUrl(String url) async => await _prefs.setString(_keyBaseUrl, url);
  static String? getBaseUrl() => _prefs.getString(_keyBaseUrl);
  static Future<void> removeBaseUrl() async => await _prefs.remove(_keyBaseUrl);

  // --- Auth & User ---
  static Future<void> saveToken(String token) async => await _prefs.setString(_keyToken, token);
  static String? getToken() => _prefs.getString(_keyToken);
  static Future<void> removeToken() async => await _prefs.remove(_keyToken);

  static Future<void> saveUser(Map<String, dynamic> user) async =>
      await _prefs.setString(_keyUser, jsonEncode(user));
  static Map<String, dynamic>? getUser() {
    final str = _prefs.getString(_keyUser);
    return str != null ? jsonDecode(str) as Map<String, dynamic> : null;
  }
  static Future<void> removeUser() async => await _prefs.remove(_keyUser);

  // --- Language ---
  static Future<void> saveLanguage(String lang) async => await _prefs.setString(_keyLanguage, lang);
  static String getLanguage() => _prefs.getString(_keyLanguage) ?? 'en';

  // --- Theme Mode ---
  static Future<void> saveThemeMode(String mode) async => await _prefs.setString(_keyThemeMode, mode);
  static String getThemeMode() => _prefs.getString(_keyThemeMode) ?? 'system';

  // --- Font Scale ---
  static Future<void> saveFontScale(double scale) async => await _prefs.setDouble(_keyFontScale, scale);
  static double getFontScale() => _prefs.getDouble(_keyFontScale) ?? 1.0;

  // --- Bookmarks & Offline Articles ---
  static List<int> getSavedArticleIds() {
    final list = _prefs.getStringList(_keySavedArticleIds) ?? [];
    return list.map((e) => int.tryParse(e) ?? 0).where((id) => id > 0).toList();
  }

  static Future<void> toggleSavedArticleId(int id) async {
    final ids = getSavedArticleIds();
    if (ids.contains(id)) {
      ids.remove(id);
    } else {
      ids.add(id);
    }
    await _prefs.setStringList(_keySavedArticleIds, ids.map((e) => e.toString()).toList());
  }

  static bool isArticleSaved(int id) => getSavedArticleIds().contains(id);

  // Offline Articles Full Cache
  static List<Map<String, dynamic>> getOfflineArticles() {
    final raw = _prefs.getString(_keyOfflineArticles);
    if (raw == null) return [];
    try {
      final decoded = jsonDecode(raw) as List;
      return decoded.map((e) => Map<String, dynamic>.from(e as Map)).toList();
    } catch (_) {
      return [];
    }
  }

  static Future<void> saveOfflineArticle(Map<String, dynamic> article) async {
    final articles = getOfflineArticles();
    articles.removeWhere((item) => item['id'] == article['id']);
    articles.insert(0, article);
    // Keep up to 50 offline articles
    if (articles.length > 50) articles.removeLast();
    await _prefs.setString(_keyOfflineArticles, jsonEncode(articles));
  }

  static Future<void> removeOfflineArticle(int id) async {
    final articles = getOfflineArticles();
    articles.removeWhere((item) => item['id'] == id);
    await _prefs.setString(_keyOfflineArticles, jsonEncode(articles));
  }

  static bool isArticleOffline(int id) {
    final articles = getOfflineArticles();
    return articles.any((item) => item['id'] == id);
  }

  // --- Saved Classifieds ---
  static List<int> getSavedAdIds() {
    final list = _prefs.getStringList(_keySavedAdIds) ?? [];
    return list.map((e) => int.tryParse(e) ?? 0).where((id) => id > 0).toList();
  }

  static Future<void> toggleSavedAdId(int id) async {
    final ids = getSavedAdIds();
    if (ids.contains(id)) {
      ids.remove(id);
    } else {
      ids.add(id);
    }
    await _prefs.setStringList(_keySavedAdIds, ids.map((e) => e.toString()).toList());
  }

  static bool isAdSaved(int id) => getSavedAdIds().contains(id);

  // --- Recent Searches ---
  static List<String> getRecentSearches() => _prefs.getStringList(_keyRecentSearches) ?? [];

  static Future<void> addRecentSearch(String term) async {
    if (term.trim().isEmpty) return;
    final list = getRecentSearches();
    list.remove(term);
    list.insert(0, term);
    if (list.length > 10) list.removeLast();
    await _prefs.setStringList(_keyRecentSearches, list);
  }

  static Future<void> clearRecentSearches() async => await _prefs.remove(_keyRecentSearches);

  // --- Notification Preferences ---
  static Map<String, dynamic> getNotificationPreferences() {
    final str = _prefs.getString(_keyNotifPrefs);
    if (str != null) {
      try {
        return jsonDecode(str) as Map<String, dynamic>;
      } catch (_) {}
    }
    return {
      'breaking_news': true,
      'category_alerts': true,
      'ad_updates': true,
      'promotions': true,
    };
  }

  static Future<void> saveNotificationPreferences(Map<String, dynamic> prefs) async =>
      await _prefs.setString(_keyNotifPrefs, jsonEncode(prefs));
}

