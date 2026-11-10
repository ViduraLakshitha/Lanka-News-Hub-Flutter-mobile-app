import 'dart:async';
import 'package:flutter/foundation.dart';
import '../network/api_client.dart';
import '../constants/api_constants.dart';
import '../storage/storage_service.dart';

class NotificationService {
  static final StreamController<Map<String, dynamic>> _notificationStreamController =
      StreamController<Map<String, dynamic>>.broadcast();

  static Stream<Map<String, dynamic>> get onNotificationReceived =>
      _notificationStreamController.stream;

  static Future<void> init() async {
    debugPrint('🔔 [NotificationService] Initializing push notification channels...');

    // Load saved preferences
    final prefs = StorageService.getNotificationPreferences();
    debugPrint('🔔 [NotificationService] Subscribed channels: $prefs');

    // Register simulated/actual device token with backend
    await _syncDeviceTokenWithBackend();
  }

  static Future<void> _syncDeviceTokenWithBackend() async {
    try {
      // Generated device token (in production with Firebase: await FirebaseMessaging.instance.getToken())
      final dummyToken = 'fcm_token_device_${DateTime.now().millisecondsSinceEpoch}';
      
      final activeTopics = <String>[];
      final prefs = StorageService.getNotificationPreferences();
      if (prefs['breaking_news'] == true) activeTopics.add('breaking_news');
      if (prefs['category_alerts'] == true) activeTopics.add('category_alerts');
      if (prefs['ad_updates'] == true) activeTopics.add('ad_updates');
      if (prefs['promotions'] == true) activeTopics.add('promotions');

      await ApiClient.post(ApiConstants.deviceToken, body: {
        'device_token': dummyToken,
        'platform': defaultTargetPlatform.name,
        'topics': activeTopics,
      });
      debugPrint('🔔 [NotificationService] Device token registered with backend.');
    } catch (e) {
      debugPrint('🔔 [NotificationService] Token registration failed or offline: $e');
    }
  }

  static Future<void> updatePreferences(Map<String, dynamic> newPrefs) async {
    await StorageService.saveNotificationPreferences(newPrefs);
    try {
      await ApiClient.post(ApiConstants.notificationPreferences, body: {
        'breaking_news': newPrefs['breaking_news'] ?? true,
        'ad_updates': newPrefs['ad_updates'] ?? true,
        'promotions': newPrefs['promotions'] ?? true,
      });
    } catch (_) {}
    await _syncDeviceTokenWithBackend();
  }

  // Trigger local simulation of notification
  static void simulateIncomingAlert({
    required String title,
    required String body,
    required String type, // 'breaking_news', 'advertisement_update', 'promotion'
    Map<String, dynamic>? payload,
  }) {
    final alert = {
      'title': title,
      'body': body,
      'type': type,
      'payload': payload,
      'timestamp': DateTime.now().toIso8601String(),
    };
    _notificationStreamController.add(alert);
  }

  static void dispose() {
    _notificationStreamController.close();
  }
}

