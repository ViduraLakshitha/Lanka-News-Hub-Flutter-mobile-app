import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/article_model.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/news_provider.dart';
import '../../providers/language_provider.dart';
import '../../providers/font_size_provider.dart';
import '../../providers/bookmark_provider.dart';
import '../../providers/theme_provider.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/full_screen_image_viewer.dart';
import '../../widgets/article_card.dart';
import '../../widgets/html_content_renderer.dart';

class ArticleDetailScreen extends StatefulWidget {
  final ArticleModel article;

  const ArticleDetailScreen({
    super.key,
    required this.article,
  });

  @override
  State<ArticleDetailScreen> createState() => _ArticleDetailScreenState();
}

class _ArticleDetailScreenState extends State<ArticleDetailScreen> {
  ArticleModel? _fullArticle;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadFullDetails();
  }

  Future<void> _loadFullDetails() async {
    final newsProvider = Provider.of<NewsProvider>(context, listen: false);
    final detail = await newsProvider.fetchArticleDetail(widget.article.id, slug: widget.article.slug);
    if (mounted) {
      setState(() {
        _fullArticle = detail ?? widget.article;
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final article = _fullArticle ?? widget.article;
    final langCode = context.watch<LanguageProvider>().languageCode;
    final fontScale = context.watch<FontSizeProvider>().fontScale;
    final bookmarkProvider = context.watch<BookmarkProvider>();
    final isSaved = bookmarkProvider.isSaved(article.id);
    final isOffline = bookmarkProvider.isOffline(article.id);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final title = article.getLocalizedTitle(langCode);
    final content = article.getLocalizedContent(langCode);

    return Scaffold(
      appBar: AppBar(
        title: Text(
          article.categoryName.toUpperCase(),
          style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, letterSpacing: 1),
        ),
        actions: [
          // Font size adjust button
          IconButton(
            icon: const Icon(Icons.format_size_rounded),
            tooltip: context.tr('font_size'),
            onPressed: () => _showFontSizeSheet(context),
          ),

          // Offline download button
          IconButton(
            icon: Icon(
              isOffline ? Icons.offline_pin_rounded : Icons.download_for_offline_outlined,
              color: isOffline ? AppColors.accentGold : Colors.white,
            ),
            tooltip: context.tr('offline_saved'),
            onPressed: () async {
              if (isOffline) {
                await bookmarkProvider.removeOffline(article.id);
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(content: Text('Removed from offline cache'), duration: Duration(seconds: 1)),
                  );
                }
              } else {
                await bookmarkProvider.saveForOffline(article);
                if (context.mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text(context.tr('offline_saved')), duration: const Duration(seconds: 1)),
                  );
                }
              }
            },
          ),

          // Bookmark button
          IconButton(
            icon: Icon(
              isSaved ? Icons.bookmark : Icons.bookmark_border,
              color: isSaved ? AppColors.accentGold : Colors.white,
            ),
            tooltip: 'Bookmark',
            onPressed: () async {
              await bookmarkProvider.toggleBookmark(article);
              if (context.mounted) {
                final msg = bookmarkProvider.isSaved(article.id)
                    ? context.tr('bookmark_saved')
                    : context.tr('bookmark_removed');
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(msg), duration: const Duration(seconds: 1)),
                );
              }
            },
          ),

          // Native Share Sheet
          IconButton(
            icon: const Icon(Icons.share_rounded),
            tooltip: context.tr('share_article'),
            onPressed: () {
              Share.share(
                '$title\n\nRead more on Lanka News Hub: https://news.liontech.lk/news/${article.slug}',
                subject: title,
              );
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.only(bottom: 32),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Featured Image
            if (article.imageUrl != null && article.imageUrl!.isNotEmpty) ...[
              InkWell(
                onTap: () {
                  Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => FullScreenImageViewer(imageUrls: [article.imageUrl!]),
                    ),
                  );
                },
                child: SizedBox(
                  width: double.infinity,
                  height: 240,
                  child: CachedNetworkImage(
                    imageUrl: article.imageUrl!,
                    fit: BoxFit.cover,
                    placeholder: (_, __) => Container(color: Colors.grey.shade300),
                    errorWidget: (_, __, ___) => Container(color: Colors.grey.shade200),
                  ),
                ),
              ),
            ],

            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Badges: Breaking, Offline available
                  Row(
                    children: [
                      if (article.isBreaking) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          margin: const EdgeInsets.only(right: 8),
                          decoration: BoxDecoration(
                            color: AppColors.breakingRed,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Text(
                            'BREAKING NEWS',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                      if (isOffline) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.green.shade700,
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(Icons.offline_pin, size: 12, color: Colors.white),
                              SizedBox(width: 4),
                              Text(
                                'OFFLINE READY',
                                style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 12),

                  // Headline
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 22 * fontScale,
                      fontWeight: FontWeight.bold,
                      height: 1.3,
                      fontFamily: 'serif',
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Author & Date & Views
                  Row(
                    children: [
                      CircleAvatar(
                        radius: 14,
                        backgroundColor: AppColors.primary,
                        child: Text(
                          article.authorName.isNotEmpty ? article.authorName[0] : 'N',
                          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        article.authorName,
                        style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                      ),
                      const SizedBox(width: 8),
                      const Text('•', style: TextStyle(color: Colors.grey)),
                      const SizedBox(width: 8),
                      Text(
                        article.publishedAt.split(' ').first,
                        style: TextStyle(
                          fontSize: 12,
                          color: isDark ? AppColors.darkTextSecondary : AppColors.lightTextSecondary,
                        ),
                      ),
                      const Spacer(),
                      const Icon(Icons.remove_red_eye_outlined, size: 14, color: Colors.grey),
                      const SizedBox(width: 4),
                      Text(
                        '${article.views}',
                        style: const TextStyle(fontSize: 12, color: Colors.grey),
                      ),
                    ],
                  ),
                  const Divider(height: 28),

                  // Video Player Card (if article has video)
                  if (article.videoUrl != null && article.videoUrl!.isNotEmpty) ...[
                    Container(
                      margin: const EdgeInsets.only(bottom: 20),
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: isDark ? AppColors.darkSurface : const Color(0xFFFFF0F2),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.primary.withOpacity(0.3)),
                      ),
                      child: Row(
                        children: [
                          Container(
                            width: 48,
                            height: 48,
                            decoration: const BoxDecoration(
                              color: AppColors.primary,
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(Icons.play_arrow_rounded, color: Colors.white, size: 32),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Watch Video Report',
                                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  article.videoUrl!,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontSize: 12, color: Colors.grey),
                                ),
                              ],
                            ),
                          ),
                          ElevatedButton(
                            onPressed: () async {
                              final uri = Uri.parse(article.videoUrl!);
                              if (await canLaunchUrl(uri)) {
                                await launchUrl(uri, mode: LaunchMode.externalApplication);
                              }
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
                              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            ),
                            child: const Text('Play', style: TextStyle(fontSize: 12)),
                          ),
                        ],
                      ),
                    ),
                  ],

                  // Article Content Body with rich typography & HTML parsing
                  HtmlContentRenderer(
                    html: content.isNotEmpty ? content : article.getLocalizedSummary(langCode),
                    fontScale: fontScale,
                    isDark: isDark,
                  ),

                  // Photo Gallery Section (if article has gallery images)
                  if (article.galleryItems.isNotEmpty) ...[
                    const SizedBox(height: 24),
                    const Row(
                      children: [
                        Icon(Icons.photo_library_outlined, size: 20, color: AppColors.primary),
                        SizedBox(width: 8),
                        Text(
                          'Photo Gallery',
                          style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      height: 160,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemCount: article.galleryItems.length,
                        separatorBuilder: (_, __) => const SizedBox(width: 10),
                        itemBuilder: (context, index) {
                          final g = article.galleryItems[index];
                          return InkWell(
                            onTap: () {
                              final urls = article.galleryItems.map((e) => e.url).toList();
                              final captions = article.galleryItems.map((e) => e.caption).toList();
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => FullScreenImageViewer(
                                    imageUrls: urls,
                                    initialIndex: index,
                                    captions: captions,
                                  ),
                                ),
                              );
                            },
                            borderRadius: BorderRadius.circular(8),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: CachedNetworkImage(
                                imageUrl: g.url,
                                width: 200,
                                height: 160,
                                fit: BoxFit.cover,
                              ),
                            ),
                          );
                        },
                      ),
                    ),
                  ],

                  // Tags
                  if (article.tags.isNotEmpty) ...[
                    const SizedBox(height: 24),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: article.tags.map((tag) {
                        return Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                          decoration: BoxDecoration(
                            color: isDark ? AppColors.darkSurface : Colors.grey.shade200,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Text(
                            '#$tag',
                            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500),
                          ),
                        );
                      }).toList(),
                    ),
                  ],

                  // Related Articles
                  if (article.relatedArticles.isNotEmpty) ...[
                    const Divider(height: 36),
                    Text(
                      context.tr('related_articles'),
                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 12),
                    ...article.relatedArticles.map((rel) {
                      return ArticleCard(
                        article: rel,
                        onTap: () {
                          Navigator.pushReplacement(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ArticleDetailScreen(article: rel),
                            ),
                          );
                        },
                      );
                    }),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showFontSizeSheet(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return Consumer<FontSizeProvider>(
          builder: (context, fontProvider, _) {
            return Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        context.tr('font_size'),
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Preset chips
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                    children: [
                      _buildPresetChip(context, fontProvider, FontSizePreset.small, context.tr('font_size_small')),
                      _buildPresetChip(context, fontProvider, FontSizePreset.normal, context.tr('font_size_normal')),
                      _buildPresetChip(context, fontProvider, FontSizePreset.large, context.tr('font_size_large')),
                      _buildPresetChip(context, fontProvider, FontSizePreset.extraLarge, context.tr('font_size_extra_large')),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Slider for fine tuning
                  Slider(
                    value: fontProvider.fontScale,
                    min: 0.85,
                    max: 1.35,
                    divisions: 10,
                    activeColor: AppColors.primary,
                    label: '${(fontProvider.fontScale * 100).round()}%',
                    onChanged: (val) => fontProvider.setFontScale(val),
                  ),

                  // Sample Preview Box
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardTheme.color,
                      borderRadius: BorderRadius.circular(8),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: Text(
                      'Preview: Sri Lanka’s leading trilingual news application.',
                      style: TextStyle(
                        fontSize: 15 * fontProvider.fontScale,
                        fontFamily: 'serif',
                      ),
                    ),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildPresetChip(
    BuildContext context,
    FontSizeProvider provider,
    FontSizePreset preset,
    String label,
  ) {
    final isSelected = provider.currentPreset == preset;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppColors.primary,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : null,
        fontWeight: isSelected ? FontWeight.bold : null,
      ),
      onSelected: (_) => provider.setPreset(preset),
    );
  }
}

