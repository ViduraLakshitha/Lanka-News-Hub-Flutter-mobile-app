import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/classified_provider.dart';
import '../../providers/auth_provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/classified_card.dart';
import '../../widgets/shimmer_loaders.dart';
import '../../widgets/empty_state_view.dart';
import 'classified_detail_screen.dart';
import 'post_ad_screen.dart';
import '../account/login_screen.dart';

class ClassifiedsHomeScreen extends StatefulWidget {
  const ClassifiedsHomeScreen({super.key});

  @override
  State<ClassifiedsHomeScreen> createState() => _ClassifiedsHomeScreenState();
}

class _ClassifiedsHomeScreenState extends State<ClassifiedsHomeScreen> {
  final ScrollController _scrollController = ScrollController();
  final TextEditingController _searchController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollController.position.pixels >= _scrollController.position.maxScrollExtent - 200) {
      Provider.of<ClassifiedProvider>(context, listen: false).loadMore();
    }
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ClassifiedProvider>();

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('classifieds_title')),
        actions: [
          IconButton(
            icon: const Icon(Icons.tune_rounded),
            tooltip: context.tr('filter'),
            onPressed: () => _showFilterSheet(context, provider),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.primary,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_photo_alternate_outlined),
        label: Text(context.tr('post_ad')),
        onPressed: () {
          final auth = Provider.of<AuthProvider>(context, listen: false);
          if (!auth.isAuthenticated) {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => LoginScreen(
                  onLoginSuccess: () {
                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(builder: (_) => const PostAdScreen()),
                    );
                  },
                ),
              ),
            );
          } else {
            Navigator.push(
              context,
              MaterialPageRoute(builder: (_) => const PostAdScreen()),
            );
          }
        },
      ),
      body: Column(
        children: [
          // Search & Filter Bar
          Container(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: context.tr('search_classifieds'),
                prefixIcon: const Icon(Icons.search, color: AppColors.primary),
                suffixIcon: _searchController.text.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, size: 18),
                        onPressed: () {
                          _searchController.clear();
                          provider.setFilter(query: '');
                        },
                      )
                    : null,
                contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              ),
              onSubmitted: (val) => provider.setFilter(query: val.trim()),
            ),
          ),

          // Horizontal Category Pills
          if (provider.categories.isNotEmpty) ...[
            SizedBox(
              height: 44,
              child: ListView.separated(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                scrollDirection: Axis.horizontal,
                itemCount: provider.categories.length + 1,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (context, index) {
                  if (index == 0) {
                    final isSelected = provider.selectedCategory == null;
                    return ChoiceChip(
                      label: Text(context.tr('all_categories')),
                      selected: isSelected,
                      selectedColor: AppColors.primary,
                      labelStyle: TextStyle(
                        color: isSelected ? Colors.white : null,
                        fontWeight: isSelected ? FontWeight.bold : null,
                      ),
                      onSelected: (_) => provider.setFilter(category: null),
                    );
                  }
                  final cat = provider.categories[index - 1];
                  final isSelected = provider.selectedCategory == cat.slug;
                  return ChoiceChip(
                    label: Text(cat.name),
                    selected: isSelected,
                    selectedColor: AppColors.primary,
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : null,
                      fontWeight: isSelected ? FontWeight.bold : null,
                    ),
                    onSelected: (_) => provider.setFilter(category: cat.slug),
                  );
                },
              ),
            ),
            const SizedBox(height: 6),
          ],

          // Active filter chip if any
          if (provider.locationFilter.isNotEmpty ||
              provider.minPrice != null ||
              provider.maxPrice != null ||
              provider.sortBy != 'newest') ...[
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              child: Row(
                children: [
                  const Text('Active Filters:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                  const SizedBox(width: 8),
                  InkWell(
                    onTap: () => provider.resetFilters(),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Row(
                        children: [
                          Text('Clear All', style: TextStyle(fontSize: 11, color: AppColors.primary)),
                          SizedBox(width: 4),
                          Icon(Icons.close, size: 12, color: AppColors.primary),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          // Classifieds Feed List
          Expanded(
            child: provider.isLoading
                ? ListView.builder(
                    itemCount: 4,
                    itemBuilder: (_, __) => const ClassifiedCardShimmer(),
                  )
                : provider.classifieds.isEmpty
                    ? EmptyStateView(
                        icon: Icons.storefront_outlined,
                        title: 'No advertisements found',
                        subtitle: 'Try adjusting your filters or search keywords',
                        actionLabel: 'Reset Filters',
                        onAction: () => provider.resetFilters(),
                      )
                    : RefreshIndicator(
                        onRefresh: () => provider.fetchClassifieds(refresh: true),
                        child: ListView.builder(
                          controller: _scrollController,
                          itemCount: provider.classifieds.length + (provider.isLoadingMore ? 1 : 0),
                          itemBuilder: (context, index) {
                            if (index == provider.classifieds.length) {
                              return const Padding(
                                padding: EdgeInsets.all(16),
                                child: Center(child: CircularProgressIndicator()),
                              );
                            }
                            final ad = provider.classifieds[index];
                            return ClassifiedCard(
                              ad: ad,
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => ClassifiedDetailScreen(ad: ad),
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

  void _showFilterSheet(BuildContext context, ClassifiedProvider provider) {
    String? tempCategory = provider.selectedCategory;
    String tempLocation = provider.locationFilter;
    double? tempMin = provider.minPrice;
    double? tempMax = provider.maxPrice;
    String tempSort = provider.sortBy;

    final locationCtrl = TextEditingController(text: tempLocation);
    final minCtrl = TextEditingController(text: tempMin != null ? tempMin.toInt().toString() : '');
    final maxCtrl = TextEditingController(text: tempMax != null ? tempMax.toInt().toString() : '');

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setModalState) {
            return Padding(
              padding: EdgeInsets.only(
                left: 20,
                right: 20,
                top: 20,
                bottom: MediaQuery.of(context).viewInsets.bottom + 20,
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          context.tr('filter'),
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                        ),
                        TextButton(
                          onPressed: () {
                            provider.resetFilters();
                            Navigator.pop(ctx);
                          },
                          child: const Text('Reset'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),

                    // Sort by
                    Text(context.tr('sort_by'), style: const TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: [
                        _buildSortChip('newest', context.tr('sort_newest'), tempSort, (val) => setModalState(() => tempSort = val)),
                        _buildSortChip('price_asc', context.tr('sort_price_low'), tempSort, (val) => setModalState(() => tempSort = val)),
                        _buildSortChip('price_desc', context.tr('sort_price_high'), tempSort, (val) => setModalState(() => tempSort = val)),
                        _buildSortChip('premium', 'Featured First', tempSort, (val) => setModalState(() => tempSort = val)),
                      ],
                    ),
                    const SizedBox(height: 16),

                    // Location filter
                    Text(context.tr('location'), style: const TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    TextField(
                      controller: locationCtrl,
                      decoration: const InputDecoration(
                        hintText: 'e.g. Colombo, Kandy, Galle',
                        prefixIcon: Icon(Icons.location_on_outlined, size: 20),
                        contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Price Range
                    Text(context.tr('price_range'), style: const TextStyle(fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: TextField(
                            controller: minCtrl,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              hintText: context.tr('min_price'),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: TextField(
                            controller: maxCtrl,
                            keyboardType: TextInputType.number,
                            decoration: InputDecoration(
                              hintText: context.tr('max_price'),
                              contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Apply Button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () {
                          provider.setFilter(
                            category: tempCategory,
                            location: locationCtrl.text.trim(),
                            minPrice: double.tryParse(minCtrl.text.trim()),
                            maxPrice: double.tryParse(maxCtrl.text.trim()),
                            sortBy: tempSort,
                          );
                          Navigator.pop(ctx);
                        },
                        child: Text(context.tr('apply')),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildSortChip(String value, String label, String current, Function(String) onSelect) {
    final isSelected = current == value;
    return ChoiceChip(
      label: Text(label, style: const TextStyle(fontSize: 12)),
      selected: isSelected,
      selectedColor: AppColors.primary,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : null,
        fontWeight: isSelected ? FontWeight.bold : null,
      ),
      onSelected: (_) => onSelect(value),
    );
  }
}
