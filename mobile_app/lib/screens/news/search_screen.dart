import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/news_provider.dart';
import '../../core/storage/storage_service.dart';
import '../../core/constants/app_colors.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/article_card.dart';
import '../../widgets/empty_state_view.dart';
import '../../widgets/shimmer_loaders.dart';
import 'article_detail_screen.dart';

class SearchScreen extends StatefulWidget {
  const SearchScreen({super.key});

  @override
  State<SearchScreen> createState() => _SearchScreenState();
}

class _SearchScreenState extends State<SearchScreen> {
  final TextEditingController _controller = TextEditingController();
  List<String> _recentSearches = [];

  static const List<String> _trendingTags = [
    'Colombo',
    'Cricket',
    'Economy',
    'Tourism',
    'Southern Expressway',
    'Technology',
    'Transport',
    'Elections',
  ];

  @override
  void initState() {
    super.initState();
    _recentSearches = StorageService.getRecentSearches();
  }

  void _onSearch(String term) {
    if (term.trim().isEmpty) return;
    _controller.text = term;
    Provider.of<NewsProvider>(context, listen: false).search(term);
    setState(() {
      _recentSearches = StorageService.getRecentSearches();
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final newsProvider = context.watch<NewsProvider>();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        titleSpacing: 0,
        title: Container(
          height: 42,
          margin: const EdgeInsets.only(right: 16),
          decoration: BoxDecoration(
            color: isDark ? AppColors.darkCard : Colors.white,
            borderRadius: BorderRadius.circular(24),
          ),
          child: TextField(
            controller: _controller,
            autofocus: true,
            textInputAction: TextInputAction.search,
            onSubmitted: _onSearch,
            decoration: InputDecoration(
              hintText: context.tr('search_placeholder'),
              hintStyle: const TextStyle(fontSize: 13, color: Colors.grey),
              prefixIcon: const Icon(Icons.search, size: 20, color: AppColors.primary),
              suffixIcon: _controller.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear, size: 18),
                      onPressed: () {
                        _controller.clear();
                        newsProvider.clearSearch();
                      },
                    )
                  : null,
              contentPadding: const EdgeInsets.symmetric(vertical: 10),
              border: InputBorder.none,
              enabledBorder: InputBorder.none,
              focusedBorder: InputBorder.none,
            ),
          ),
        ),
      ),
      body: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // If not currently showing search results, show Recent Searches & Trending Topics
          if (newsProvider.searchQuery.isEmpty) ...[
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Recent Searches
                    if (_recentSearches.isNotEmpty) ...[
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            context.tr('recent_searches'),
                            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                          ),
                          TextButton(
                            onPressed: () async {
                              await StorageService.clearRecentSearches();
                              setState(() {
                                _recentSearches = [];
                              });
                            },
                            child: const Text('Clear', style: TextStyle(fontSize: 12)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: _recentSearches.map((term) {
                          return ActionChip(
                            avatar: const Icon(Icons.history, size: 14),
                            label: Text(term, style: const TextStyle(fontSize: 12)),
                            onPressed: () => _onSearch(term),
                          );
                        }).toList(),
                      ),
                      const Divider(height: 32),
                    ],

                    // Trending Topics
                    Text(
                      context.tr('trending_topics'),
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                    ),
                    const SizedBox(height: 12),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: _trendingTags.map((tag) {
                        return ActionChip(
                          avatar: const Icon(Icons.trending_up, size: 14, color: AppColors.primary),
                          label: Text(tag, style: const TextStyle(fontSize: 12)),
                          onPressed: () => _onSearch(tag),
                        );
                      }).toList(),
                    ),
                  ],
                ),
              ),
            ),
          ] else ...[
            // Search Results
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Text(
                'Results for "${newsProvider.searchQuery}" (${newsProvider.searchResults.length})',
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: Colors.grey),
              ),
            ),
            Expanded(
              child: newsProvider.isSearching
                  ? ListView.builder(
                      itemCount: 4,
                      itemBuilder: (_, __) => const ArticleCardShimmer(),
                    )
                  : newsProvider.searchResults.isEmpty
                      ? EmptyStateView(
                          icon: Icons.search_off,
                          title: context.tr('no_results'),
                          subtitle: 'Try searching with different keywords',
                        )
                      : ListView.builder(
                          itemCount: newsProvider.searchResults.length,
                          itemBuilder: (context, index) {
                            final article = newsProvider.searchResults[index];
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
                          },
                        ),
            ),
          ],
        ],
      ),
    );
  }
}

