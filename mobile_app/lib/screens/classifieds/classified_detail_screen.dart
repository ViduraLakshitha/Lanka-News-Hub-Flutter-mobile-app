import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import 'package:share_plus/share_plus.dart';
import '../../models/classified_model.dart';
import '../../core/constants/app_colors.dart';
import '../../providers/classified_provider.dart';
import '../../core/localization/app_localizations.dart';
import '../../widgets/full_screen_image_viewer.dart';

class ClassifiedDetailScreen extends StatefulWidget {
  final ClassifiedModel ad;

  const ClassifiedDetailScreen({
    super.key,
    required this.ad,
  });

  @override
  State<ClassifiedDetailScreen> createState() => _ClassifiedDetailScreenState();
}

class _ClassifiedDetailScreenState extends State<ClassifiedDetailScreen> {
  int _activeImageIndex = 0;

  @override
  Widget build(BuildContext context) {
    final provider = context.watch<ClassifiedProvider>();
    final ad = widget.ad;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final isFav = ad.isFavorite;

    return Scaffold(
      appBar: AppBar(
        title: Text(ad.categoryName),
        actions: [
          // Favorite Button
          IconButton(
            icon: Icon(
              isFav ? Icons.favorite : Icons.favorite_border,
              color: isFav ? AppColors.breakingRed : Colors.white,
            ),
            onPressed: () => provider.toggleFavorite(ad),
          ),

          // Share Button
          IconButton(
            icon: const Icon(Icons.share_rounded),
            onPressed: () {
              Share.share(
                'Check out this advertisement on Lanka News Hub: ${ad.title} - ${ad.formattedPrice}\nLocation: ${ad.location}',
                subject: ad.title,
              );
            },
          ),
        ],
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Theme.of(context).cardTheme.color,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.08),
              blurRadius: 10,
              offset: const Offset(0, -3),
            ),
          ],
        ),
        child: SafeArea(
          child: Row(
            children: [
              // Call Button
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => provider.callAdvertiser(ad.contactPhone, ad.id),
                  icon: const Icon(Icons.phone_in_talk, size: 18),
                  label: Text(context.tr('call_now')),
                  style: OutlinedButton.styleFrom(
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    side: const BorderSide(color: AppColors.primary, width: 1.5),
                  ),
                ),
              ),
              const SizedBox(width: 12),

              // WhatsApp Button
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: () => provider.whatsappAdvertiser(ad.contactPhone, ad.title, ad.id),
                  icon: const Icon(Icons.chat, size: 18),
                  label: Text(context.tr('whatsapp')),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF25D366),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.only(bottom: 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image Carousel with Full Screen Viewer tap
            if (ad.images.isNotEmpty) ...[
              SizedBox(
                height: 260,
                child: Stack(
                  children: [
                    PageView.builder(
                      itemCount: ad.images.length,
                      onPageChanged: (idx) => setState(() => _activeImageIndex = idx),
                      itemBuilder: (context, index) {
                        return InkWell(
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => FullScreenImageViewer(
                                  imageUrls: ad.images,
                                  initialIndex: index,
                                ),
                              ),
                            );
                          },
                          child: CachedNetworkImage(
                            imageUrl: ad.images[index],
                            fit: BoxFit.cover,
                            width: double.infinity,
                            placeholder: (_, __) => Container(color: Colors.grey.shade300),
                            errorWidget: (_, __, ___) => Container(
                              color: Colors.grey.shade200,
                              child: const Icon(Icons.image_not_supported, size: 40, color: Colors.grey),
                            ),
                          ),
                        );
                      },
                    ),

                    // Photo Counter Pill
                    Positioned(
                      bottom: 12,
                      right: 12,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.7),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.photo_camera, size: 12, color: Colors.white),
                            const SizedBox(width: 4),
                            Text(
                              '${_activeImageIndex + 1} / ${ad.images.length}',
                              style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],

            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Premium badge
                  if (ad.isPremium) ...[
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      margin: const EdgeInsets.only(bottom: 8),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(colors: [Color(0xFFFFD700), Color(0xFFFFA500)]),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.star, size: 12, color: Colors.black87),
                          SizedBox(width: 4),
                          Text(
                            'FEATURED PREMIUM AD',
                            style: TextStyle(
                              color: Colors.black87,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],

                  // Price
                  Text(
                    ad.formattedPrice,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: AppColors.primary,
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Title
                  Text(
                    ad.title,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Metadata Cards (Location, Category, Date)
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: isDark ? AppColors.darkCard : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Column(
                      children: [
                        _buildMetaRow(Icons.location_on_outlined, context.tr('location'), ad.location),
                        const Divider(height: 16),
                        _buildMetaRow(Icons.category_outlined, context.tr('ad_category'), ad.categoryName),
                        const Divider(height: 16),
                        _buildMetaRow(Icons.calendar_today_outlined, 'Posted on', ad.createdAt.split(' ').first),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Description
                  Text(
                    context.tr('ad_description'),
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    ad.description,
                    style: TextStyle(
                      fontSize: 14,
                      height: 1.5,
                      color: isDark ? AppColors.darkTextPrimary : AppColors.lightTextPrimary,
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Seller Details Section
                  Text(
                    context.tr('seller_details'),
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Theme.of(context).cardTheme.color,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isDark ? AppColors.darkBorder : AppColors.lightBorder,
                      ),
                    ),
                    child: Column(
                      children: [
                        ListTile(
                          contentPadding: EdgeInsets.zero,
                          leading: CircleAvatar(
                            backgroundColor: AppColors.primary,
                            child: Text(
                              ad.contactName.isNotEmpty ? ad.contactName[0] : 'S',
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
                            ),
                          ),
                          title: Text(ad.contactName, style: const TextStyle(fontWeight: FontWeight.bold)),
                          subtitle: Text(ad.contactPhone),
                          trailing: IconButton(
                            icon: const Icon(Icons.email_outlined, color: AppColors.primary),
                            onPressed: () => provider.emailAdvertiser(ad.contactEmail, ad.title, ad.id),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  // Safety Tips Alert Box
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.amber.withOpacity(0.12),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: Colors.amber.shade700.withOpacity(0.4)),
                    ),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(Icons.security_rounded, color: Colors.amber.shade800, size: 20),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            context.tr('safety_tips'),
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark ? Colors.amber.shade200 : Colors.amber.shade900,
                              height: 1.3,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMetaRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 16, color: Colors.grey),
        const SizedBox(width: 8),
        Text('$label: ', style: const TextStyle(fontSize: 13, color: Colors.grey)),
        Expanded(
          child: Text(
            value,
            textAlign: TextAlign.end,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
          ),
        ),
      ],
    );
  }
}

