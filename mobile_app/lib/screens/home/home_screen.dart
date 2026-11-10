import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/news_provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/article_card.dart';
import '../../widgets/shimmer_loaders.dart';
import 'widgets/breaking_news_ticker.dart';
import 'widgets/featured_carousel.dart';
import 'widgets/category_pills.dart';
import 'widgets/video_reel_widget.dart';
import 'widgets/photo_gallery_preview.dart';
import '../news/article_detail_screen.dart';
import '../news/search_screen.dart';
import '../notifications/notifications_screen.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/server_config_dialog.dart';
import '../../core/constants/api_constants.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      Provider.of<NewsProvider>(context, listen: false).loadMore();
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final news = context.watch<NewsProvider>();
    final notifProvider = context.watch<NotificationProvider>();
    final unreadNotifs = notifProvider.unreadCount;

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 12,
        title: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(5),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(6),
              ),
              child: const Icon(Icons.newspaper_rounded, color: AppColors.primary, size: 18),
            ),
            const SizedBox(width: 8),
            const Flexible(
              child: Text(
                'LANKA NEWS HUB',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 16,
                  letterSpacing: 0.5,
                  fontFamily: 'serif',
                ),
              ),
            ),
          ],
        ),
        actions: [
          // Search Button
          IconButton(
            icon: const Icon(Icons.search_rounded),
            tooltip: context.tr('search'),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const SearchScreen()),
              );
            },
          ),

          // Notification Bell with Badge
          Stack(
            children: [
              IconButton(
                icon: const Icon(Icons.notifications_none_rounded),
                tooltip: context.tr('notifications'),
                onPressed: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(builder: (_) => const NotificationsScreen()),
                  );
                },
              ),
              if (unreadNotifs > 0)
                Positioned(
                  right: 8,
                  top: 8,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: const BoxDecoration(
                      color: AppColors.accentGold,
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                    child: Text(
                      '$unreadNotifs',
                      style: const TextStyle(
                        color: Colors.black,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),

          // Server Connection Setting
          IconButton(
            icon: const Icon(Icons.dns_rounded),
            tooltip: 'Server Connection',
            onPressed: () => ServerConfigDialog.show(context),
          ),
        ],
      ),
      body: news.isLoading
          ? ListView(
              children: const [
                SizedBox(height: 12),
                ArticleCardShimmer(),
                ArticleCardShimmer(),
                ArticleCardShimmer(),
              ],
            )
          : RefreshIndicator(
              onRefresh: () => news.initApp(),
              child: ListView(
                controller: _scrollController,
                children: [
                  // Offline Mode Alert Banner if device is offline
                  if (news.isOfflineMode) ...[
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      color: Colors.amber.shade800,
                      child: Row(
                        children: [
                          const Icon(Icons.wifi_off, color: Colors.white, size: 16),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              context.tr('showing_offline'),
                              style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],

                  // Breaking News Alert Ticker
                  if (news.breakingNews.isNotEmpty)
                    BreakingNewsTicker(breakingArticles: news.breakingNews),

                  // Featured Stories Carousel
                  if (news.featuredNews.isNotEmpty && news.selectedCategorySlug == null) ...[
                    const SizedBox(height: 8),
                    FeaturedCarousel(featuredArticles: news.featuredNews),
                  ],

                  // Category Selector Pills
                  const SizedBox(height: 12),
                  CategoryPills(
                    categories: news.categories,
                    selectedSlug: news.selectedCategorySlug,
                    onSelected: (slug) => news.selectCategory(slug),
                  ),
                  const SizedBox(height: 10),

                  // Latest News Headline
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 4),
                    child: Text(
                      news.selectedCategorySlug != null
                          ? '${news.categories.where((c) => c.slug == news.selectedCategorySlug).firstOrNull?.name ?? "Category"} Stories'
                          : context.tr('latest_news'),
                      style: const TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        fontFamily: 'serif',
                      ),
                    ),
                  ),

                  // Empty State Fallback
                  if (news.latestNews.isEmpty)
                    Container(
                      margin: const EdgeInsets.all(20),
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Theme.of(context).cardColor,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey.withOpacity(0.2)),
                      ),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.cloud_off_rounded, size: 48, color: AppColors.primary),
                          const SizedBox(height: 12),
                          const Text(
                            'No News Available',
                            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Server: ${ApiConstants.activeBaseUrl}',
                            style: const TextStyle(fontSize: 12, color: Colors.grey),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              ElevatedButton.icon(
                                onPressed: () => news.initApp(),
                                icon: const Icon(Icons.refresh, size: 16),
                                label: const Text('Retry'),
                              ),
                              const SizedBox(width: 10),
                              OutlinedButton.icon(
                                onPressed: () => ServerConfigDialog.show(context),
                                icon: const Icon(Icons.settings, size: 16),
                                label: const Text('Server Settings'),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                  // Latest Stories Feed
                  ...news.latestNews.asMap().entries.map((entry) {
                    final index = entry.key;
                    final article = entry.value;

                    // Insert Video reel after article 3
                    if (index == 3 && news.videoNews.isNotEmpty && news.selectedCategorySlug == null) {
                      return Column(
                        children: [
                          ArticleCard(
                            article: article,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => ArticleDetailScreen(article: article),
                                ),
                              );
                            },
                          ),
                          VideoReelWidget(videoArticles: news.videoNews),
                        ],
                      );
                    }

                    // Insert Photo Gallery highlight after article 7
                    if (index == 7 && news.galleryNews.isNotEmpty && news.selectedCategorySlug == null) {
                      return Column(
                        children: [
                          ArticleCard(
                            article: article,
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => ArticleDetailScreen(article: article),
                                ),
                              );
                            },
                          ),
                          PhotoGalleryPreview(galleryArticles: news.galleryNews),
                        ],
                      );
                    }

                    return ArticleCard(
                      article: article,
                      onTap: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => ArticleDetailScreen(article: article),
                          ),
                        );
                      },
                    );
                  }),

                  // Load More Spinner
                  if (news.isLoadingMore)
                    const Padding(
                      padding: EdgeInsets.symmetric(vertical: 20),
                      child: Center(child: CircularProgressIndicator()),
                    ),
                  const SizedBox(height: 24),
                ],
              ),
            ),
    );
  }
}

