class NotificationItem {
  final String id;
  final String type; // 'breaking_news', 'advertisement_update', 'promotion'
  final String title;
  final String body;
  final int? articleId;
  final String? articleSlug;
  final int? adId;
  final String timestamp;
  bool isRead;

  NotificationItem({
    required this.id,
    required this.type,
    required this.title,
    required this.body,
    this.articleId,
    this.articleSlug,
    this.adId,
    required this.timestamp,
    this.isRead = false,
  });

  factory NotificationItem.fromMap(Map<String, dynamic> map) {
    return NotificationItem(
      id: map['id']?.toString() ?? '',
      type: map['type']?.toString() ?? 'breaking_news',
      title: map['title']?.toString() ?? '',
      body: map['body']?.toString() ?? '',
      articleId: map['article_id'] is int ? map['article_id'] : int.tryParse(map['article_id']?.toString() ?? ''),
      articleSlug: map['article_slug']?.toString(),
      adId: map['ad_id'] is int ? map['ad_id'] : int.tryParse(map['ad_id']?.toString() ?? ''),
      timestamp: map['timestamp']?.toString() ?? '',
      isRead: map['is_read'] == true,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'type': type,
      'title': title,
      'body': body,
      'article_id': articleId,
      'article_slug': articleSlug,
      'ad_id': adId,
      'timestamp': timestamp,
      'is_read': isRead,
    };
  }
}

