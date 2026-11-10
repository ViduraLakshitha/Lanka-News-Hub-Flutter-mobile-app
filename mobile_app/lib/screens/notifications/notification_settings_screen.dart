import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/notification_provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/localization/app_localizations.dart';

class NotificationSettingsScreen extends StatelessWidget {
  const NotificationSettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<NotificationProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('notification_settings')),
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // Breaking Alerts Switch
          SwitchListTile(
            activeThumbColor: AppColors.primary,
            title: Text(
              context.tr('breaking_alerts'),
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            subtitle: Text(context.tr('breaking_alerts_desc')),
            value: provider.breakingNewsEnabled,
            onChanged: (val) => provider.updatePreferences(breakingNews: val),
          ),
          const Divider(),

          // Category Based Alerts
          SwitchListTile(
            activeThumbColor: AppColors.primary,
            title: Text(
              context.tr('category_notifications'),
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            subtitle: const Text('Notifications for subscribed topics (Politics, Sports, Business)'),
            value: provider.categoryAlertsEnabled,
            onChanged: (val) => provider.updatePreferences(categoryAlerts: val),
          ),
          const Divider(),

          // Advertisement Approvals
          SwitchListTile(
            activeThumbColor: AppColors.primary,
            title: Text(
              context.tr('ad_notifications'),
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            subtitle: Text(context.tr('ad_notifications_desc')),
            value: provider.adUpdatesEnabled,
            onChanged: (val) => provider.updatePreferences(adUpdates: val),
          ),
          const Divider(),

          // Promotional Offers
          SwitchListTile(
            activeThumbColor: AppColors.primary,
            title: Text(
              context.tr('promo_notifications'),
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            subtitle: const Text('Updates regarding marketplace highlights and special features'),
            value: provider.promotionsEnabled,
            onChanged: (val) => provider.updatePreferences(promotions: val),
          ),
        ],
      ),
    );
  }
}

