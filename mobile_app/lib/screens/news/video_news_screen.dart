import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../models/article_model.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../providers/language_provider.dart';
import '../../widgets/empty_state_view.dart';
import '../../widgets/shimmer_loaders.dart';
import 'article_detail_screen.dart';

class VideoNewsScreen extends StatefulWidget {
  const VideoNewsScreen({super.key});

  @override
  State<VideoNewsScreen> createState() => _VideoNewsScreenState();
}

class _VideoNewsScreenState extends State<VideoNewsScreen> {
  List<ArticleModel> _videos = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadVideos();
  }

  Future<void> _loadVideos() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: {'section': 'videos', 'limit': 30},
      );
      final List raw = res['data'] ?? [];
      setState(() {
        _videos = raw.map((e) => ArticleModel.fromMap(e as Map<String, dynamic>)).toList();
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final langCode = context.watch<LanguageProvider>().languageCode;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Video News Reports'),
      ),
      body: _isLoading
          ? ListView.builder(
              itemCount: 4,
              itemBuilder: (_, __) => const ArticleCardShimmer(),
            )
          : _videos.isEmpty
              ? const EmptyStateView(
                  icon: Icons.video_library_outlined,
                  title: 'No video stories available',
                )
              : RefreshIndicator(
                  onRefresh: _loadVideos,
                  child: ListView.separated(
                    padding: const EdgeInsets.all(16),
                    itemCount: _videos.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 16),
                    itemBuilder: (context, index) {
                      final item = _videos[index];
                      final title = item.getLocalizedTitle(langCode);

                      return InkWell(
                        onTap: () {
                          Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => ArticleDetailScreen(article: item),
                            ),
                          );
                        },
                        borderRadius: BorderRadius.circular(12),
                        child: Container(
                          decoration: BoxDecoration(
                            color: Theme.of(context).cardTheme.color,
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.06),
                                blurRadius: 8,
                                offset: const Offset(0, 3),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Video Thumbnail with Overlay
                              ClipRRect(
                                borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                                child: Stack(
                                  alignment: Alignment.center,
                                  children: [
                                    SizedBox(
                                      width: double.infinity,
                                      height: 200,
                                      child: item.imageUrl != null
                                          ? CachedNetworkImage(
                                              imageUrl: item.imageUrl!,
                                              fit: BoxFit.cover,
                                            )
                                          : Container(color: Colors.black87),
                                    ),
                                    Container(
                                      width: 56,
                                      height: 56,
                                      decoration: BoxDecoration(
                                        color: AppColors.primary.withOpacity(0.9),
                                        shape: BoxShape.circle,
                                        boxShadow: const [
                                          BoxShadow(color: Colors.black38, blurRadius: 10),
                                        ],
                                      ),
                                      child: const Icon(
                                        Icons.play_arrow_rounded,
                                        color: Colors.white,
                                        size: 36,
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                              Padding(
                                padding: const EdgeInsets.all(12),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      title,
                                      style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                        fontFamily: 'serif',
                                        height: 1.3,
                                      ),
                                    ),
                                    const SizedBox(height: 8),
                                    Row(
                                      children: [
                                        Text(
                                          item.categoryName,
                                          style: const TextStyle(
                                            color: AppColors.primary,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 11,
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        const Text('•', style: TextStyle(color: Colors.grey)),
                                        const SizedBox(width: 8),
                                        Text(
                                          item.publishedAt.split(' ').first,
                                          style: const TextStyle(color: Colors.grey, fontSize: 11),
                                        ),
                                        const Spacer(),
                                        if (item.videoUrl != null)
                                          TextButton.icon(
                                            onPressed: () async {
                                              final uri = Uri.parse(item.videoUrl!);
                                              if (await canLaunchUrl(uri)) {
                                                await launchUrl(uri, mode: LaunchMode.externalApplication);
                                              }
                                            },
                                            icon: const Icon(Icons.open_in_new, size: 14),
                                            label: const Text('Open External', style: TextStyle(fontSize: 11)),
                                          ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                ),
    );
  }
}

