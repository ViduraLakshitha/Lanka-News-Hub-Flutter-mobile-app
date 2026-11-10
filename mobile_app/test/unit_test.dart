import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:lanka_news_hub/core/localization/app_localizations.dart';
import 'package:lanka_news_hub/core/storage/storage_service.dart';
import 'package:lanka_news_hub/models/article_model.dart';
import 'package:lanka_news_hub/models/classified_model.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('Localization Tests (Sinhala, English, Tamil)', () {
    test('English translations exist for core keys', () {
      final loc = AppLocalizations(const Locale('en'));
      expect(loc.translate('app_title'), 'Lanka News Hub');
      expect(loc.translate('nav_news'), 'News');
      expect(loc.translate('post_ad'), 'Post Advertisement');
    });

    test('Sinhala translations exist for core keys', () {
      final loc = AppLocalizations(const Locale('si'));
      expect(loc.translate('app_title'), 'ලංකා නිවුස් හබ්');
      expect(loc.translate('nav_news'), 'පුවත්');
      expect(loc.translate('post_ad'), 'දැන්වීමක් පළ කරන්න');
    });

    test('Tamil translations exist for core keys', () {
      final loc = AppLocalizations(const Locale('ta'));
      expect(loc.translate('app_title'), 'லங்கா நியூஸ் ஹப்');
      expect(loc.translate('nav_news'), 'செய்திகள்');
      expect(loc.translate('post_ad'), 'விளம்பரம் இடுக');
    });
  });

  group('Data Models Tests', () {
    test('ArticleModel parses and localizes correctly', () {
      final article = ArticleModel.fromMap({
        'id': 10,
        'slug': 'test-headline',
        'title': 'Default Headline',
        'titles': {
          'en': 'English Headline',
          'si': 'සිංහල සිරස්තලය',
          'ta': 'தமிழ் தலைப்பு',
        },
        'summary': 'Summary text',
        'summaries': {'en': 'En summary', 'si': 'Si summary'},
        'contents': {'en': 'En content'},
        'author_name': 'Reporter',
        'category_id': 1,
        'category_name': 'Local',
        'category_slug': 'local',
        'category_color': '#C41230',
        'is_breaking': true,
        'is_featured': false,
        'views': 120,
        'tags': ['Test', 'News'],
        'published_at': '2026-11-10 12:00:00',
      });

      expect(article.id, 10);
      expect(article.getLocalizedTitle('en'), 'English Headline');
      expect(article.getLocalizedTitle('si'), 'සිංහල සිරස්තලය');
      expect(article.getLocalizedTitle('ta'), 'தமிழ் தலைப்பு');
      expect(article.isBreaking, true);
    });

    test('ClassifiedModel parses with formatted price', () {
      final ad = ClassifiedModel.fromMap({
        'id': 5,
        'user_id': 1,
        'category_id': 3,
        'category_name': 'Vehicles',
        'category_slug': 'vehicles',
        'language': 'en',
        'title': 'Toyota Prius 2018',
        'description': 'Mint condition, first owner',
        'location': 'Colombo',
        'price': 8500000,
        'formatted_price': 'LKR 8,500,000.00',
        'contact_name': 'Sunil',
        'contact_email': 'sunil@example.com',
        'contact_phone': '+94771234567',
        'images': ['https://example.com/img1.jpg'],
        'status': 'approved',
        'is_premium': true,
        'premium_requested': true,
        'is_expired': false,
        'created_at': '2026-11-10 10:00:00',
        'updated_at': '2026-11-10 10:00:00',
      });

      expect(ad.id, 5);
      expect(ad.price, 8500000.0);
      expect(ad.isPremium, true);
      expect(ad.images.length, 1);
    });
  });

  group('StorageService Unit Tests', () {
    test('Stores and retrieves language, bookmarks, and font scale', () async {
      SharedPreferences.setMockInitialValues({});
      await StorageService.init();

      await StorageService.saveLanguage('si');
      expect(StorageService.getLanguage(), 'si');

      await StorageService.saveFontScale(1.15);
      expect(StorageService.getFontScale(), 1.15);

      await StorageService.toggleSavedArticleId(42);
      expect(StorageService.isArticleSaved(42), true);
      await StorageService.toggleSavedArticleId(42);
      expect(StorageService.isArticleSaved(42), false);
    });
  });
}

