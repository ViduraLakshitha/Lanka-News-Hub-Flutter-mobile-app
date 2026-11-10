import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/live_update_model.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/news_provider.dart';
import '../../widgets/empty_state_view.dart';
import 'article_detail_screen.dart';

class LiveUpdatesScreen extends StatefulWidget {
  const LiveUpdatesScreen({super.key});

  @override
  State<LiveUpdatesScreen> createState() => _LiveUpdatesScreenState();
}

class _LiveUpdatesScreenState extends State<LiveUpdatesScreen> {
  @override
  void initState() {
    super.initState();
    Provider.of<NewsProvider>(context, listen: false).fetchLiveUpdates();
  }

  @override
  Widget build(BuildContext context) {
    final newsProvider = context.watch<NewsProvider>();
    final liveUpdates = newsProvider.liveUpdates;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            Container(
              width: 10,
              height: 10,
              decoration: const BoxDecoration(
                color: Colors.greenAccent,
                shape: BoxShape.circle,
              ),
            ),
            const SizedBox(width: 8),
            const Text('Live Breaking Updates'),
          ],
        ),
      ),
      body: RefreshIndicator(
        onRefresh: () => newsProvider.fetchLiveUpdates(),
        child: liveUpdates.isEmpty
            ? const EmptyStateView(
                icon: Icons.update_rounded,
                title: 'No live events currently streaming',
              )
            : ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: liveUpdates.length,
                itemBuilder: (context, index) {
                  final update = liveUpdates[index];
                  final isLatest = index == 0;

                  return IntrinsicHeight(
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Timeline indicator
                        Column(
                          children: [
                            Container(
                              width: 14,
                              height: 14,
                              decoration: BoxDecoration(
                                color: isLatest ? AppColors.breakingRed : Colors.grey,
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: Colors.white,
                                  width: 2,
                                ),
                              ),
                            ),
                            Expanded(
                              child: Container(
                                width: 2,
                                color: isDark ? AppColors.darkBorder : Colors.grey.shade300,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(width: 12),

                        // Timeline Card
                        Expanded(
                          child: Container(
                          margin: const EdgeInsets.only(bottom: 16),
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: Theme.of(context).cardTheme.color,
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(
                              color: isLatest
                                  ? AppColors.breakingRed.withOpacity(0.5)
                                  : (isDark ? AppColors.darkBorder : AppColors.lightBorder),
                            ),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.04),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  if (update.isBreaking) ...[
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      margin: const EdgeInsets.only(right: 6),
                                      decoration: BoxDecoration(
                                        color: AppColors.breakingRed,
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: const Text(
                                        'BREAKING',
                                        style: TextStyle(
                                          color: Colors.white,
                                          fontSize: 9,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ],
                                  Text(
                                    update.categoryName.toUpperCase(),
                                    style: const TextStyle(
                                      color: AppColors.primary,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 10,
                                    ),
                                  ),
                                  const Spacer(),
                                  Text(
                                    update.publishedAt.split(' ').length > 1
                                        ? update.publishedAt.split(' ')[1].substring(0, 5)
                                        : update.publishedAt,
                                    style: const TextStyle(fontSize: 11, color: Colors.grey),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Text(
                                update.title,
                                style: const TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  fontFamily: 'serif',
                                ),
                              ),
                              if (update.summary.isNotEmpty) ...[
                                const SizedBox(height: 4),
                                Text(
                                  update.summary,
                                  maxLines: 3,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                                    height: 1.3,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                );
                },
              ),
      ),
    );
  }
}

