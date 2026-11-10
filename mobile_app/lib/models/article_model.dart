class ArticleModel {
  final int id;
  final String slug;
  final String title;
  final String summary;
  final String? content;
  final Map<String, String> titles;
  final Map<String, String> summaries;
  final Map<String, String> contents;
  final String authorName;
  final int categoryId;
  final String categoryName;
  final String categorySlug;
  final String categoryColor;
  final String? imageUrl;
  final String? videoUrl;
  final String? videoMediaUrl;
  final bool isBreaking;
  final bool isFeatured;
  final int views;
  final List<String> tags;
  final List<GalleryItem> galleryItems;
  final List<ArticleModel> relatedArticles;
  final String publishedAt;
  bool isSaved;
  bool isOffline;

  ArticleModel({
    required this.id,
    required this.slug,
    required this.title,
    required this.summary,
    this.content,
    required this.titles,
    required this.summaries,
    required this.contents,
    required this.authorName,
    required this.categoryId,
    required this.categoryName,
    required this.categorySlug,
    required this.categoryColor,
    this.imageUrl,
    this.videoUrl,
    this.videoMediaUrl,
    required this.isBreaking,
    required this.isFeatured,
    required this.views,
    required this.tags,
    this.galleryItems = const [],
    this.relatedArticles = const [],
    required this.publishedAt,
    this.isSaved = false,
    this.isOffline = false,
  });

  String getLocalizedTitle(String langCode) {
    return titles[langCode]?.isNotEmpty == true
        ? titles[langCode]!
        : (titles['en']?.isNotEmpty == true ? titles['en']! : title);
  }

  String getLocalizedSummary(String langCode) {
    return summaries[langCode]?.isNotEmpty == true
        ? summaries[langCode]!
        : (summaries['en']?.isNotEmpty == true ? summaries['en']! : summary);
  }

  String getLocalizedContent(String langCode) {
    if (contents.containsKey(langCode) && contents[langCode]!.isNotEmpty) {
      return contents[langCode]!;
    }
    if (contents.containsKey('en') && contents['en']!.isNotEmpty) {
      return contents['en']!;
    }
    return content ?? summary;
  }

  factory ArticleModel.fromMap(Map<String, dynamic> map) {
    final titlesMap = <String, String>{};
    if (map['titles'] is Map) {
      (map['titles'] as Map).forEach((k, v) => titlesMap[k.toString()] = v?.toString() ?? '');
    }

    final summariesMap = <String, String>{};
    if (map['summaries'] is Map) {
      (map['summaries'] as Map).forEach((k, v) => summariesMap[k.toString()] = v?.toString() ?? '');
    }

    final contentsMap = <String, String>{};
    if (map['contents'] is Map) {
      (map['contents'] as Map).forEach((k, v) => contentsMap[k.toString()] = v?.toString() ?? '');
    }

    final galleryList = <GalleryItem>[];
    if (map['gallery_items'] is List) {
      for (final g in map['gallery_items'] as List) {
        if (g is Map<String, dynamic>) {
          galleryList.add(GalleryItem.fromMap(g));
        }
      }
    }

    final relatedList = <ArticleModel>[];
    if (map['related_articles'] is List) {
      for (final r in map['related_articles'] as List) {
        if (r is Map<String, dynamic>) {
          relatedList.add(ArticleModel.fromMap(r));
        }
      }
    }

    final tagList = <String>[];
    if (map['tags'] is List) {
      for (final t in map['tags'] as List) {
        tagList.add(t.toString());
      }
    }

    return ArticleModel(
      id: map['id'] is int ? map['id'] : int.tryParse(map['id'].toString()) ?? 0,
      slug: map['slug']?.toString() ?? '',
      title: map['title']?.toString() ?? '',
      summary: map['summary']?.toString() ?? '',
      content: map['content']?.toString(),
      titles: titlesMap,
      summaries: summariesMap,
      contents: contentsMap,
      authorName: map['author_name']?.toString() ?? 'Editorial Desk',
      categoryId: map['category_id'] is int ? map['category_id'] : int.tryParse(map['category_id']?.toString() ?? '0') ?? 0,
      categoryName: map['category_name']?.toString() ?? '',
      categorySlug: map['category_slug']?.toString() ?? '',
      categoryColor: map['category_color']?.toString() ?? '#C41230',
      imageUrl: map['image_url']?.toString(),
      videoUrl: map['video_url']?.toString(),
      videoMediaUrl: map['video_media_url']?.toString(),
      isBreaking: map['is_breaking'] == true || map['is_breaking'] == 1,
      isFeatured: map['is_featured'] == true || map['is_featured'] == 1,
      views: map['views'] is int ? map['views'] : int.tryParse(map['views']?.toString() ?? '0') ?? 0,
      tags: tagList,
      galleryItems: galleryList,
      relatedArticles: relatedList,
      publishedAt: map['published_at']?.toString() ?? '',
      isSaved: map['is_saved'] == true,
      isOffline: map['is_offline'] == true,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'slug': slug,
      'title': title,
      'summary': summary,
      'content': content,
      'titles': titles,
      'summaries': summaries,
      'contents': contents,
      'author_name': authorName,
      'category_id': categoryId,
      'category_name': categoryName,
      'category_slug': categorySlug,
      'category_color': categoryColor,
      'image_url': imageUrl,
      'video_url': videoUrl,
      'video_media_url': videoMediaUrl,
      'is_breaking': isBreaking,
      'is_featured': isFeatured,
      'views': views,
      'tags': tags,
      'gallery_items': galleryItems.map((g) => g.toMap()).toList(),
      'published_at': publishedAt,
      'is_saved': isSaved,
      'is_offline': isOffline,
    };
  }
}

class GalleryItem {
  final int id;
  final String url;
  final String altText;
  final String caption;

  GalleryItem({
    required this.id,
    required this.url,
    required this.altText,
    required this.caption,
  });

  factory GalleryItem.fromMap(Map<String, dynamic> map) {
    return GalleryItem(
      id: map['id'] is int ? map['id'] : int.tryParse(map['id'].toString()) ?? 0,
      url: map['url']?.toString() ?? '',
      altText: map['alt_text']?.toString() ?? '',
      caption: map['caption']?.toString() ?? '',
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'url': url,
      'alt_text': altText,
      'caption': caption,
    };
  }
}

