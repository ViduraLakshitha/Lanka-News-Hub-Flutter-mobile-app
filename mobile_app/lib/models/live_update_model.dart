import '../core/utils/media_url_formatter.dart';

class LiveUpdateModel {
  final int id;
  final String slug;
  final String title;
  final String summary;
  final String publishedAt;
  final bool isBreaking;
  final String categoryName;
  final String? imageUrl;

  LiveUpdateModel({
    required this.id,
    required this.slug,
    required this.title,
    required this.summary,
    required this.publishedAt,
    required this.isBreaking,
    required this.categoryName,
    this.imageUrl,
  });

  factory LiveUpdateModel.fromMap(Map<String, dynamic> map) {
    return LiveUpdateModel(
      id: map['id'] is int ? map['id'] : int.tryParse(map['id'].toString()) ?? 0,
      slug: map['slug']?.toString() ?? '',
      title: map['title']?.toString() ?? '',
      summary: map['summary']?.toString() ?? '',
      publishedAt: map['published_at']?.toString() ?? '',
      isBreaking: map['is_breaking'] == true || map['is_breaking'] == 1,
      categoryName: map['category_name']?.toString() ?? 'News',
      imageUrl: MediaUrlFormatter.format(map['image_url']?.toString()),
    );
  }
}

