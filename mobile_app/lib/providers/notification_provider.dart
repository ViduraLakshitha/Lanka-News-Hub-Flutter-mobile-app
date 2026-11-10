import 'package:flutter/material.dart';
import '../models/notification_item.dart';
import '../core/network/api_client.dart';
import '../core/constants/api_constants.dart';
import '../core/storage/storage_service.dart';
import '../core/notifications/notification_service.dart';

class NotificationProvider extends ChangeNotifier {
  List<NotificationItem> _notifications = [];
  bool _isLoading = false;

  // Preferences
  bool _breakingNewsEnabled = true;
  bool _categoryAlertsEnabled = true;
  bool _adUpdatesEnabled = true;
  bool _promotionsEnabled = true;

  List<NotificationItem> get notifications => _notifications;
  bool get isLoading => _isLoading;
  int get unreadCount => _notifications.where((n) => !n.isRead).length;

  bool get breakingNewsEnabled => _breakingNewsEnabled;
  bool get categoryAlertsEnabled => _categoryAlertsEnabled;
  bool get adUpdatesEnabled => _adUpdatesEnabled;
  bool get promotionsEnabled => _promotionsEnabled;

  NotificationProvider() {
    _loadPreferences();
    fetchNotifications();
    _listenToIncomingAlerts();
  }

  void _loadPreferences() {
    final prefs = StorageService.getNotificationPreferences();
    _breakingNewsEnabled = prefs['breaking_news'] ?? true;
    _categoryAlertsEnabled = prefs['category_alerts'] ?? true;
    _adUpdatesEnabled = prefs['ad_updates'] ?? true;
    _promotionsEnabled = prefs['promotions'] ?? true;
    notifyListeners();
  }

  void _listenToIncomingAlerts() {
    NotificationService.onNotificationReceived.listen((alert) {
      final newItem = NotificationItem(
        id: 'live_${DateTime.now().millisecondsSinceEpoch}',
        type: alert['type'] ?? 'breaking_news',
        title: alert['title'] ?? 'Alert',
        body: alert['body'] ?? '',
        timestamp: DateTime.now().toIso8601String(),
        isRead: false,
      );
      _notifications.insert(0, newItem);
      notifyListeners();
    });
  }

  Future<void> fetchNotifications() async {
    _isLoading = true;
    notifyListeners();

    try {
      final res = await ApiClient.get(ApiConstants.notifications);
      final List raw = res['data'] ?? [];
      _notifications = raw.map((e) => NotificationItem.fromMap(e as Map<String, dynamic>)).toList();
    } catch (_) {}

    _isLoading = false;
    notifyListeners();
  }

  void markAsRead(String id) {
    final idx = _notifications.indexWhere((n) => n.id == id);
    if (idx != -1) {
      _notifications[idx].isRead = true;
      notifyListeners();
    }
  }

  void markAllAsRead() {
    for (final n in _notifications) {
      n.isRead = true;
    }
    notifyListeners();
  }

  void clearAll() {
    _notifications.clear();
    notifyListeners();
  }

  Future<void> updatePreferences({
    bool? breakingNews,
    bool? categoryAlerts,
    bool? adUpdates,
    bool? promotions,
  }) async {
    if (breakingNews != null) _breakingNewsEnabled = breakingNews;
    if (categoryAlerts != null) _categoryAlertsEnabled = categoryAlerts;
    if (adUpdates != null) _adUpdatesEnabled = adUpdates;
    if (promotions != null) _promotionsEnabled = promotions;

    final updated = {
      'breaking_news': _breakingNewsEnabled,
      'category_alerts': _categoryAlertsEnabled,
      'ad_updates': _adUpdatesEnabled,
      'promotions': _promotionsEnabled,
    };

    await NotificationService.updatePreferences(updated);
    notifyListeners();
  }
}

