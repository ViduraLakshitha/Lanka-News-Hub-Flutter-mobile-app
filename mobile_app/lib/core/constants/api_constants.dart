import '../storage/storage_service.dart';

class ApiConstants {
  // Remote Production URL (when deployed to web hosting)
  static const String productionBaseUrl = 'https://news.liontech.lk/api/v1';

  // Local Wi-Fi Development URL (Host PC IP on Wi-Fi/Hotspot)
  static const String defaultDevUrl = 'http://172.20.10.5:8080/api/v1';

  // Android Emulator local loopback
  static const String emulatorBaseUrl = 'http://10.0.2.2:8080/api/v1';

  // Localhost
  static const String localhostBaseUrl = 'http://127.0.0.1:8080/api/v1';

  // Active base URL - Default to live cPanel production
  static String activeBaseUrl = productionBaseUrl;

  static void initBaseUrl() {
    final saved = StorageService.getBaseUrl();
    if (saved != null && saved.trim().isNotEmpty) {
      // Auto-migrate any previously saved local development IPs to live cPanel
      if (saved.contains('172.20.10.5') || saved.contains('127.0.0.1') || saved.contains('10.0.2.2')) {
        activeBaseUrl = productionBaseUrl;
        StorageService.saveBaseUrl(productionBaseUrl);
      } else {
        activeBaseUrl = saved.trim();
      }
      return;
    }

    activeBaseUrl = productionBaseUrl;
  }

  static Future<void> setCustomBaseUrl(String url) async {
    activeBaseUrl = url.trim().replaceAll(RegExp(r'/+$'), '');
    await StorageService.saveBaseUrl(activeBaseUrl);
  }

  static Future<void> resetBaseUrl() async {
    activeBaseUrl = productionBaseUrl;
    await StorageService.saveBaseUrl(productionBaseUrl);
  }

  // Endpoints
  static const String bootstrap = '/bootstrap';
  static const String news = '/news';
  static const String liveUpdates = '/live-updates';
  static const String classifieds = '/classifieds';
  static const String accountAds = '/account/ads';
  static const String accountFavorites = '/account/favorites';
  static const String register = '/auth/register';
  static const String login = '/auth/login';
  static const String me = '/auth/me';
  static const String updateProfile = '/auth/profile';
  static const String savedArticles = '/account/saved-articles';
  static const String notifications = '/notifications';
  static const String notificationPreferences = '/notifications/preferences';
  static const String deviceToken = '/notifications/device-token';

  // Request timeout
  static const Duration timeout = Duration(seconds: 15);
}
