import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/classified_provider.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/classified_card.dart';
import '../../widgets/empty_state_view.dart';
import 'classified_detail_screen.dart';

class FavoriteAdsScreen extends StatelessWidget {
  const FavoriteAdsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ClassifiedProvider>();
    final favorites = provider.favorites;

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('saved_ads')),
      ),
      body: favorites.isEmpty
          ? EmptyStateView(
              icon: Icons.favorite_border,
              title: 'No saved advertisements',
              subtitle: 'Tap the heart icon on any advertisement to save it here for quick access.',
            )
          : ListView.builder(
              padding: const EdgeInsets.symmetric(vertical: 8),
              itemCount: favorites.length,
              itemBuilder: (context, index) {
                final ad = favorites[index];
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
    );
  }
}

