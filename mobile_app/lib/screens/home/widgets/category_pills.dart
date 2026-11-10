import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../../models/category_model.dart';
import '../../../providers/language_provider.dart';
import '../../../core/constants/app_colors.dart';

class CategoryPills extends StatelessWidget {
  final List<CategoryModel> categories;
  final String? selectedSlug;
  final Function(String?) onSelected;

  const CategoryPills({
    super.key,
    required this.categories,
    required this.selectedSlug,
    required this.onSelected,
  });

  @override
  Widget build(BuildContext context) {
    final langCode = context.watch<LanguageProvider>().languageCode;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return SizedBox(
      height: 44,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        scrollDirection: Axis.horizontal,
        itemCount: categories.length + 1,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, index) {
          if (index == 0) {
            final isSelected = selectedSlug == null;
            return ChoiceChip(
              label: const Text('All News'),
              selected: isSelected,
              onSelected: (_) => onSelected(null),
              selectedColor: AppColors.primary,
              labelStyle: TextStyle(
                color: isSelected ? Colors.white : (isDark ? Colors.white70 : Colors.black87),
                fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                fontSize: 13,
              ),
              showCheckmark: false,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              side: BorderSide(
                color: isSelected
                    ? AppColors.primary
                    : (isDark ? AppColors.darkBorder : AppColors.lightBorder),
              ),
            );
          }

          final cat = categories[index - 1];
          final isSelected = selectedSlug == cat.slug;
          final localizedName = cat.getLocalizedName(langCode);

          return ChoiceChip(
            label: Text(localizedName),
            selected: isSelected,
            onSelected: (_) => onSelected(cat.slug),
            selectedColor: AppColors.primary,
            labelStyle: TextStyle(
              color: isSelected ? Colors.white : (isDark ? Colors.white70 : Colors.black87),
              fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
              fontSize: 13,
            ),
            showCheckmark: false,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            side: BorderSide(
              color: isSelected
                  ? AppColors.primary
                  : (isDark ? AppColors.darkBorder : AppColors.lightBorder),
            ),
          );
        },
      ),
    );
  }
}

