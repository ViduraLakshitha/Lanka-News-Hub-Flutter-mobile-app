import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../models/category_model.dart';
import '../../models/article_model.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/news_provider.dart';
import '../../providers/language_provider.dart';
import '../../core/network/api_client.dart';
import '../../core/constants/api_constants.dart';
import '../../widgets/article_card.dart';
import '../../widgets/shimmer_loaders.dart';
import '../../widgets/empty_state_view.dart';
import 'article_detail_screen.dart';

class CategoryNewsScreen extends StatefulWidget {
  final CategoryModel? initialCategory;

  const CategoryNewsScreen({
    super.key,
    this.initialCategory,
  });

  @override
  State<CategoryNewsScreen> createState() => _CategoryNewsScreenState();
}

class _CategoryNewsScreenState extends State<CategoryNewsScreen> {
  CategoryModel? _selectedCategory;
  List<ArticleModel> _articles = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    final categories = Provider.of<NewsProvider>(context, listen: false).categories;
    _selectedCategory = widget.initialCategory ?? (categories.isNotEmpty ? categories.first : null);
    _loadCategoryNews();
  }

  Future<void> _loadCategoryNews() async {
    if (_selectedCategory == null) {
      setState(() => _isLoading = false);
      return;
    }

    setState(() => _isLoading = true);

    try {
      final categoryParam = CategoryModel.resolveQueryParam(_selectedCategory!.slug, _selectedCategory!.id);
      final res = await ApiClient.get(
        ApiConstants.news,
        queryParams: {'category': categoryParam, 'limit': 30},
        cacheDuration: const Duration(minutes: 5),
      );
      final List raw = res['data'] ?? [];
      setState(() {
        _articles = raw.map((e) => ArticleModel.fromMap(e as Map<String, dynamic>)).toList();
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final newsProvider = context.watch<NewsProvider>();
    final categories = newsProvider.categories;
    final langCode = context.watch<LanguageProvider>().languageCode;

    // Auto-select first category once categories load if none selected yet
    if (_selectedCategory == null && categories.isNotEmpty) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted && _selectedCategory == null) {
          setState(() {
            _selectedCategory = categories.first;
          });
          _loadCategoryNews();
        }
      });
    }

    return Scaffold(
      appBar: AppBar(
        title: Text(_selectedCategory?.getLocalizedName(langCode) ?? 'Categories'),
      ),
      body: Column(
        children: [
          // Horizontal Category Tabs
          if (categories.isNotEmpty) ...[
            Container(
              height: 48,
              padding: const EdgeInsets.symmetric(vertical: 6),
              child: ListView.separated(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                scrollDirection: Axis.horizontal,
                itemCount: categories.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  final cat = categories[index];
                  final isSelected = _selectedCategory?.id == cat.id;

                  return ChoiceChip(
                    label: Text(cat.getLocalizedName(langCode)),
                    selected: isSelected,
                    selectedColor: AppColors.primary,
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : null,
                      fontWeight: isSelected ? FontWeight.bold : null,
                    ),
                    onSelected: (val) {
                      if (val) {
                        setState(() {
                          _selectedCategory = cat;
                        });
                        _loadCategoryNews();
                      }
                    },
                  );
                },
              ),
            ),
            const Divider(height: 1),
          ],

          // Articles List
          Expanded(
            child: _isLoading
                ? ListView.builder(
                    itemCount: 5,
                    itemBuilder: (_, __) => const ArticleCardShimmer(),
                  )
                : _articles.isEmpty
                    ? const EmptyStateView(
                        icon: Icons.newspaper,
                        title: 'No articles in this category yet',
                      )
                    : RefreshIndicator(
                        onRefresh: _loadCategoryNews,
                        child: ListView.builder(
                          itemCount: _articles.length,
                          itemBuilder: (context, index) {
                            final article = _articles[index];
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
          ),
        ],
      ),
    );
  }
}

