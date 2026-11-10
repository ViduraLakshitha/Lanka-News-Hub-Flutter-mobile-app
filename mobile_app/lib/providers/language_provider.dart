import 'package:flutter/material.dart';
import '../core/storage/storage_service.dart';
import '../core/network/api_client.dart';
import '../core/analytics/analytics_service.dart';

class LanguageProvider extends ChangeNotifier {
  Locale _locale = const Locale('en');

  Locale get locale => _locale;
  String get languageCode => _locale.languageCode;

  LanguageProvider() {
    _loadLanguage();
  }

  void _loadLanguage() {
    final savedCode = StorageService.getLanguage();
    _locale = Locale(savedCode);
    notifyListeners();
  }

  Future<void> setLanguage(String code) async {
    if (!['en', 'si', 'ta'].contains(code)) return;
    if (_locale.languageCode == code) return;

    _locale = Locale(code);
    await StorageService.saveLanguage(code);
    ApiClient.clearCache();
    await AnalyticsService.logLanguageChanged(code);
    notifyListeners();
  }

  String get languageName {
    return switch (_locale.languageCode) {
      'si' => 'සිංහල',
      'ta' => 'தமிழ்',
      _ => 'English',
    };
  }
}

