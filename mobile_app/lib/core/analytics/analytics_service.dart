import 'package:flutter/foundation.dart';

class AnalyticsService {
  static bool _enabled = true;

  static void setEnabled(bool enabled) {
    _enabled = enabled;
  }

  // Track screen view
  static Future<void> logScreenView(String screenName) async {
    if (!_enabled) return;
    debugPrint('📊 [Analytics] ScreenView: $screenName');
    // Integration point for FirebaseAnalytics.instance.logScreenView(screenName: screenName)
  }

  // Track article read
  static Future<void> logArticleRead({
    required int articleId,
    required String title,
    required String category,
  }) async {
    if (!_enabled) return;
    debugPrint('📊 [Analytics] ArticleRead: #$articleId | Category: $category | Title: $title');
    // Integration point for FirebaseAnalytics.instance.logEvent(...)
  }

  // Track search query
  static Future<void> logSearch(String query) async {
    if (!_enabled) return;
    debugPrint('📊 [Analytics] SearchQuery: "$query"');
  }

  // Track classified ad view
  static Future<void> logClassifiedView({
    required int adId,
    required String category,
    required String location,
  }) async {
    if (!_enabled) return;
    debugPrint('📊 [Analytics] ClassifiedView: #$adId | Cat: $category | Loc: $location');
  }

  // Track contact action
  static Future<void> logContactAction({
    required int adId,
    required String actionType, // 'call', 'whatsapp', 'email'
  }) async {
    if (!_enabled) return;
    debugPrint('📊 [Analytics] ContactAction: $actionType for Ad #$adId');
  }

  // Track ad post
  static Future<void> logAdSubmission({
    required String category,
    required bool isPremium,
  }) async {
    if (!_enabled) return;
    debugPrint('📊 [Analytics] AdSubmission: Cat: $category | Premium: $isPremium');
  }

  // Track language change
  static Future<void> logLanguageChanged(String langCode) async {
    if (!_enabled) return;
    debugPrint('📊 [Analytics] LanguageChanged: $langCode');
  }
}

