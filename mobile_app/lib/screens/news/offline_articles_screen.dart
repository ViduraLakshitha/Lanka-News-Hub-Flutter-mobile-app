import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/bookmark_provider.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/article_card.dart';
import '../../widgets/empty_state_view.dart';
import 'article_detail_screen.dart';

class OfflineArticlesScreen extends StatelessWidget {
  const OfflineArticlesScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final bookmarkProvider = context.watch<BookmarkProvider>();
    final offlineArticles = bookmarkProvider.offlineArticles;

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('offline_articles')),
      ),
      body: offlineArticles.isEmpty
          ? const EmptyStateView(
              icon: Icons.bookmark_border_rounded,
              title: 'No saved articles yet',
              subtitle: 'Tap the bookmark icon on any article to save it for reading anytime, even offline.',
            )
          : ListView.builder(
              padding: const EdgeInsets.symmetric(vertical: 8),
              itemCount: offlineArticles.length,
              itemBuilder: (context, index) {
                final article = offlineArticles[index];
                return Dismissible(
                  key: Key('offline_${article.id}'),
                  direction: DismissDirection.endToStart,
                  background: Container(
                    color: Colors.red,
                    alignment: Alignment.centerRight,
                    padding: const EdgeInsets.only(right: 20),
                    child: const Icon(Icons.delete, color: Colors.white),
                  ),
                  onDismissed: (_) {
                    bookmarkProvider.removeOffline(article.id);
                  },
                  child: ArticleCard(
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
                );
              },
            ),
    );
  }
}

