import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/classified_provider.dart';
import '../../models/classified_model.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/classified_card.dart';
import '../../widgets/empty_state_view.dart';
import 'classified_detail_screen.dart';

class MyAdsScreen extends StatefulWidget {
  const MyAdsScreen({super.key});

  @override
  State<MyAdsScreen> createState() => _MyAdsScreenState();
}

class _MyAdsScreenState extends State<MyAdsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    Provider.of<ClassifiedProvider>(context, listen: false).fetchMyAds();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ClassifiedProvider>();
    final myAds = provider.myAds;

    final activeAds = myAds.where((a) => a.status == 'approved' && !a.isExpired).toList();
    final pendingAds = myAds.where((a) => a.status == 'pending').toList();
    final expiredAds = myAds.where((a) => a.isExpired || a.status == 'withdrawn').toList();

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('my_ads')),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: [
            Tab(text: '${context.tr('status_active')} (${activeAds.length})'),
            Tab(text: '${context.tr('status_pending')} (${pendingAds.length})'),
            Tab(text: '${context.tr('status_expired')} (${expiredAds.length})'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildAdList(context, provider, activeAds, 'No active advertisements'),
          _buildAdList(context, provider, pendingAds, 'No advertisements currently in review'),
          _buildAdList(context, provider, expiredAds, 'No expired or withdrawn advertisements'),
        ],
      ),
    );
  }

  Widget _buildAdList(
    BuildContext context,
    ClassifiedProvider provider,
    List<ClassifiedModel> list,
    String emptyMessage,
  ) {
    if (list.isEmpty) {
      return EmptyStateView(
        icon: Icons.inventory_2_outlined,
        title: emptyMessage,
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.symmetric(vertical: 8),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final ad = list[index];

        return Column(
          children: [
            ClassifiedCard(
              ad: ad,
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => ClassifiedDetailScreen(ad: ad)),
                );
              },
            ),

            // Dashboard Management Controls for User
            Container(
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 12),
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: Theme.of(context).cardTheme.color,
                borderRadius: const BorderRadius.vertical(bottom: Radius.circular(10)),
                border: Border.all(color: Colors.grey.withOpacity(0.2)),
              ),
              child: Row(
                children: [
                  // Status badge
                  _buildStatusChip(ad.status, ad.isExpired),
                  const Spacer(),

                  // Renew button
                  if (ad.isExpired || ad.status == 'approved') ...[
                    TextButton.icon(
                      onPressed: () async {
                        final ok = await provider.renewAdvertisement(ad.id);
                        if (context.mounted && ok) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Advertisement renewed for review')),
                          );
                        }
                      },
                      icon: const Icon(Icons.refresh, size: 16),
                      label: Text(context.tr('renew_ad'), style: const TextStyle(fontSize: 12)),
                    ),
                  ],

                  // Delete / Withdraw button
                  IconButton(
                    icon: const Icon(Icons.delete_outline, size: 20, color: Colors.red),
                    tooltip: context.tr('delete_ad'),
                    onPressed: () {
                      showDialog(
                        context: context,
                        builder: (ctx) => AlertDialog(
                          title: const Text('Withdraw Advertisement?'),
                          content: const Text('Are you sure you want to withdraw this advertisement from the marketplace?'),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.pop(ctx),
                              child: Text(context.tr('cancel')),
                            ),
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(backgroundColor: Colors.red),
                              onPressed: () async {
                                Navigator.pop(ctx);
                                await provider.withdrawAdvertisement(ad.id);
                              },
                              child: Text(context.tr('delete')),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
          ],
        );
      },
    );
  }

  Widget _buildStatusChip(String status, bool isExpired) {
    if (isExpired) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(color: Colors.grey.shade400, borderRadius: BorderRadius.circular(4)),
        child: const Text('EXPIRED', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
      );
    }

    final color = switch (status) {
      'approved' => Colors.green,
      'pending' => Colors.orange,
      _ => Colors.grey,
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(4)),
      child: Text(
        status.toUpperCase(),
        style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold),
      ),
    );
  }
}
