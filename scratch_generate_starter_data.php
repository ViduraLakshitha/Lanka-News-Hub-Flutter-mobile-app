<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306;dbname=liontec2_news_DB;charset=utf8mb4", 'liontec2_news_admin', 'Dha#1D345jk#@2', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$articles = $pdo->query("SELECT a.id, a.slug, a.title_en, a.title_si, a.title_ta, 
                                a.summary_en, a.summary_si, a.summary_ta,
                                a.is_breaking, a.is_featured, a.views, a.published_at,
                                c.id AS category_id, c.name_en AS category_name, c.slug AS category_slug, c.color AS category_color,
                                u.name AS author_name, m.file_path AS image_path
                         FROM articles a
                         JOIN categories c ON c.id = a.category_id
                         JOIN users u ON u.id = a.author_id
                         LEFT JOIN media m ON m.id = a.featured_image_id
                         WHERE a.status = 'published'
                         ORDER BY a.published_at DESC
                         LIMIT 15")->fetchAll();

$categories = $pdo->query("SELECT id, name_en, name_si, name_ta, slug, color, icon, order_index FROM categories WHERE status = 'active' ORDER BY order_index ASC")->fetchAll();

$dartArticles = [];
foreach ($articles as $a) {
    $img = $a['image_path'] ? 'http://172.20.10.5:8080/' . ltrim($a['image_path'], '/') : null;
    $dartArticles[] = [
        'id' => (int)$a['id'],
        'slug' => $a['slug'],
        'title' => $a['title_en'],
        'summary' => $a['summary_en'],
        'titles' => [
            'en' => $a['title_en'] ?: '',
            'si' => $a['title_si'] ?: '',
            'ta' => $a['title_ta'] ?: ''
        ],
        'summaries' => [
            'en' => $a['summary_en'] ?: '',
            'si' => $a['summary_si'] ?: '',
            'ta' => $a['summary_ta'] ?: ''
        ],
        'contents' => [
            'en' => $a['summary_en'] ?: '',
            'si' => $a['summary_si'] ?: '',
            'ta' => $a['summary_ta'] ?: ''
        ],
        'author_name' => $a['author_name'] ?: 'Editorial Desk',
        'category_id' => (int)$a['category_id'],
        'category_name' => $a['category_name'],
        'category_slug' => $a['category_slug'],
        'category_color' => $a['category_color'] ?: '#C41230',
        'image_url' => $img,
        'video_url' => '',
        'is_breaking' => (bool)$a['is_breaking'],
        'is_featured' => (bool)$a['is_featured'],
        'views' => (int)$a['views'],
        'tags' => ['News', 'Sri Lanka'],
        'published_at' => $a['published_at']
    ];
}

$dartCategories = [];
foreach ($categories as $c) {
    $dartCategories[] = [
        'id' => (int)$c['id'],
        'slug' => $c['slug'],
        'name' => $c['name_en'],
        'name_en' => $c['name_en'],
        'name_si' => $c['name_si'],
        'name_ta' => $c['name_ta'],
        'color' => $c['color'] ?: '#C41230',
        'icon' => $c['icon'] ?: 'newspaper',
        'order' => (int)$c['order_index']
    ];
}

$jsonArticles = json_encode($dartArticles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$jsonCategories = json_encode($dartCategories, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$dartCode = <<<DART
// Preloaded starter news and categories from MySQL database
// Ensures the app is never empty even on first launch or offline
class StarterData {
  static const List<Map<String, dynamic>> categories = $jsonCategories;

  static const List<Map<String, dynamic>> articles = $jsonArticles;
}
DART;

file_put_contents('F:/news_page/mobile_app/lib/core/constants/starter_data.dart', $dartCode);
echo "starter_data.dart generated successfully with " . count($dartArticles) . " articles and " . count($dartCategories) . " categories.\n";

