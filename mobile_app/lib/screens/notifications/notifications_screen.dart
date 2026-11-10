import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/notification_provider.dart';
import '../../providers/news_provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/empty_state_view.dart';
import 'notification_settings_screen.dart';
import '../news/article_detail_screen.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<NotificationProvider>();
    final notifications = provider.notifications;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('notifications')),
        actions: [
          IconButton(
            icon: const Icon(Icons.settings_outlined),
            tooltip: context.tr('notification_settings'),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const NotificationSettingsScreen()),
              );
            },
          ),
          if (notifications.isNotEmpty)
            PopupMenuButton(
              itemBuilder: (_) => [
                PopupMenuItem(
                  value: 'read_all',
                  child: const Text('Mark all as read'),
                ),
                PopupMenuItem(
                  value: 'clear_all',
                  child: Text(context.tr('clear_all')),
                ),
              ],
              onSelected: (val) {
                if (val == 'read_all') {
                  provider.markAllAsRead();
                } else if (val == 'clear_all') {
                  provider.clearAll();
                }
              },
            ),
        ],
      ),
      body: provider.isLoading
          ? const Center(child: CircularProgressIndicator())
          : notifications.isEmpty
              ? EmptyStateView(
                  icon: Icons.notifications_none_rounded,
                  title: context.tr('no_notifications'),
                  subtitle: 'You will receive breaking alerts and ad updates here.',
                )
              : ListView.separated(
                  padding: const EdgeInsets.symmetric(vertical: 8),
                  itemCount: notifications.length,
                  separatorBuilder: (_, __) => const Divider(height: 1),
                  itemBuilder: (context, index) {
                    final item = notifications[index];

                    final icon = switch (item.type) {
                      'breaking_news' => Icons.flash_on_rounded,
                      'advertisement_update' => Icons.storefront_rounded,
                      _ => Icons.campaign_rounded,
                    };

                    final iconColor = switch (item.type) {
                      'breaking_news' => AppColors.breakingRed,
                      'advertisement_update' => Colors.green,
                      _ => AppColors.accentGoldDark,
                    };

                    return ListTile(
                      tileColor: item.isRead
                          ? null
                          : (isDark ? AppColors.darkCard : const Color(0xFFFFF7F8)),
                      leading: CircleAvatar(
                        backgroundColor: iconColor.withOpacity(0.15),
                        child: Icon(icon, color: iconColor, size: 20),
                      ),
                      title: Row(
                        children: [
                          Expanded(
                            child: Text(
                              item.title,
                              style: TextStyle(
                                fontWeight: item.isRead ? FontWeight.w500 : FontWeight.bold,
                                fontSize: 14,
                              ),
                            ),
                          ),
                          Text(
                            item.timestamp.split(' ').first,
                            style: const TextStyle(fontSize: 11, color: Colors.grey),
                          ),
                        ],
                      ),
                      subtitle: Padding(
                        padding: const EdgeInsets.only(top: 4),
                        child: Text(
                          item.body,
                          style: TextStyle(
                            fontSize: 13,
                            color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                          ),
                        ),
                      ),
                      onTap: () async {
                        provider.markAsRead(item.id);
                        if (item.articleId != null) {
                          final news = Provider.of<NewsProvider>(context, listen: false);
                          final article = await news.fetchArticleDetail(item.articleId!);
                          if (article != null && context.mounted) {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => ArticleDetailScreen(article: article),
                              ),
                            );
                          }
                        }
                      },
                    );
                  },
                ),
    );
  }
}

