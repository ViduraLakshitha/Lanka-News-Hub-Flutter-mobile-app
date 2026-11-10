class ClassifiedModel {
  final int id;
  final int userId;
  final int categoryId;
  final String categoryName;
  final String categorySlug;
  final String language;
  final String title;
  final String description;
  final String location;
  final double? price;
  final String formattedPrice;
  final String contactName;
  final String contactEmail;
  final String contactPhone;
  final List<String> images;
  final String? thumbnailUrl;
  final String status;
  final String? moderationNote;
  final bool isPremium;
  final bool premiumRequested;
  final bool isExpired;
  bool isFavorite;
  final String? expiresAt;
  final String createdAt;
  final String updatedAt;

  ClassifiedModel({
    required this.id,
    required this.userId,
    required this.categoryId,
    required this.categoryName,
    required this.categorySlug,
    required this.language,
    required this.title,
    required this.description,
    required this.location,
    this.price,
    required this.formattedPrice,
    required this.contactName,
    required this.contactEmail,
    required this.contactPhone,
    required this.images,
    this.thumbnailUrl,
    required this.status,
    this.moderationNote,
    required this.isPremium,
    required this.premiumRequested,
    required this.isExpired,
    this.isFavorite = false,
    this.expiresAt,
    required this.createdAt,
    required this.updatedAt,
  });

  factory ClassifiedModel.fromMap(Map<String, dynamic> map) {
    final imageList = <String>[];
    if (map['images'] is List) {
      for (final img in map['images'] as List) {
        if (img != null && img.toString().isNotEmpty) {
          imageList.add(img.toString());
        }
      }
    }

    return ClassifiedModel(
      id: map['id'] is int ? map['id'] : int.tryParse(map['id'].toString()) ?? 0,
      userId: map['user_id'] is int ? map['user_id'] : int.tryParse(map['user_id']?.toString() ?? '0') ?? 0,
      categoryId: map['category_id'] is int ? map['category_id'] : int.tryParse(map['category_id']?.toString() ?? '0') ?? 0,
      categoryName: map['category_name']?.toString() ?? '',
      categorySlug: map['category_slug']?.toString() ?? '',
      language: map['language']?.toString() ?? 'en',
      title: map['title']?.toString() ?? '',
      description: map['description']?.toString() ?? '',
      location: map['location']?.toString() ?? '',
      price: map['price'] != null ? double.tryParse(map['price'].toString()) : null,
      formattedPrice: map['formatted_price']?.toString() ?? 'Negotiable',
      contactName: map['contact_name']?.toString() ?? '',
      contactEmail: map['contact_email']?.toString() ?? '',
      contactPhone: map['contact_phone']?.toString() ?? '',
      images: imageList,
      thumbnailUrl: map['thumbnail_url']?.toString(),
      status: map['status']?.toString() ?? 'pending',
      moderationNote: map['moderation_note']?.toString(),
      isPremium: map['is_premium'] == true || map['is_premium'] == 1,
      premiumRequested: map['premium_requested'] == true || map['premium_requested'] == 1,
      isExpired: map['is_expired'] == true || map['is_expired'] == 1,
      isFavorite: map['is_favorite'] == true,
      expiresAt: map['expires_at']?.toString(),
      createdAt: map['created_at']?.toString() ?? '',
      updatedAt: map['updated_at']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'user_id': userId,
      'category_id': categoryId,
      'category_name': categoryName,
      'category_slug': categorySlug,
      'language': language,
      'title': title,
      'description': description,
      'location': location,
      'price': price,
      'formatted_price': formattedPrice,
      'contact_name': contactName,
      'contact_email': contactEmail,
      'contact_phone': contactPhone,
      'images': images,
      'thumbnail_url': thumbnailUrl,
      'status': status,
      'moderation_note': moderationNote,
      'is_premium': isPremium,
      'premium_requested': premiumRequested,
      'is_expired': isExpired,
      'is_favorite': isFavorite,
      'expires_at': expiresAt,
      'created_at': createdAt,
      'updated_at': updatedAt,
    };
  }
}

