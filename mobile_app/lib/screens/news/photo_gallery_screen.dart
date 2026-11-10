import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import '../../models/article_model.dart';
import '../../core/constants/app_colors.dart';
import '../../core/network/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../providers/language_provider.dart';
import '../../widgets/full_screen_image_viewer.dart';
import '../../widgets/empty_state_view.dart';
import '../../widgets/shimmer_loaders.dart';
import 'article_detail_screen.dart';

class PhotoGalleryScreen extends StatefulWidget {
  const PhotoGalleryScreen({super.key});

  @override
  State<PhotoGalleryScreen> createState() => _PhotoGalleryScreenState();
}

class _PhotoGalleryScreenState extends State<PhotoGalleryScreen> {
  List<ArticleModel> _galleries = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadGalleries();
  }

  Future<void> _loadGalleries() async {
    try {
      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: {'section': 'gallery', 'limit': 30},
      );
      final List raw = res['data'] ?? [];
      setState(() {
        _galleries = raw.map((e) => ArticleModel.fromMap(e as Map<String, dynamic>)).toList();
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
        title: const Text('Photo Galleries'),
      ),
      body: _isLoading
          ? ListView.builder(
              itemCount: 4,
              itemBuilder: (_, __) => const ArticleCardShimmer(),
            )
          : _galleries.isEmpty
              ? const EmptyStateView(
                  icon: Icons.photo_library_outlined,
                  title: 'No photo galleries found',
                )
              : RefreshIndicator(
                  onRefresh: _loadGalleries,
                  child: GridView.builder(
                    padding: const EdgeInsets.all(16),
                    gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: 2,
                      crossAxisSpacing: 12,
                      mainAxisSpacing: 12,
                      childAspectRatio: 0.82,
                    ),
                    itemCount: _galleries.length,
                    itemBuilder: (context, index) {
                      final item = _galleries[index];
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
                                color: Colors.black.withOpacity(0.05),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
                              ),
                            ],
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Photo Thumbnail
                              Expanded(
                                child: ClipRRect(
                                  borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
                                  child: Stack(
                                    fit: StackFit.expand,
                                    children: [
                                      item.imageUrl != null
                                          ? CachedNetworkImage(
                                              imageUrl: item.imageUrl!,
                                              fit: BoxFit.cover,
                                            )
                                          : Container(color: Colors.grey.shade400),
                                      Positioned(
                                        top: 8,
                                        right: 8,
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
                                          decoration: BoxDecoration(
                                            color: Colors.black.withOpacity(0.7),
                                            borderRadius: BorderRadius.circular(4),
                                          ),
                                          child: const Icon(Icons.photo_library, size: 12, color: Colors.white),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),

                              Padding(
                                padding: const EdgeInsets.all(10),
                                child: Text(
                                  title,
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    height: 1.25,
                                  ),
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

