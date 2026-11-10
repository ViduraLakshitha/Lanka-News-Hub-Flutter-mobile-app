<?php
namespace App\Controllers;

use App\Core\Portal;
use App\Core\Upload;
use App\Core\Database;

class ApiController extends Portal {
    protected ?array $apiUser = null;

    public function __construct() {
        parent::__construct();
        $this->handleCors();
        $this->authenticateApiUser();
    }

    private function handleCors(): void {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, X-App-Language');
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }

    private function authenticateApiUser(): void {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
            $token = $matches[1];
            $row = $this->one("SELECT t.user_id, t.expires_at, u.*, r.slug AS role 
                              FROM api_tokens t 
                              JOIN users u ON u.id = t.user_id 
                              JOIN roles r ON r.id = u.role_id 
                              WHERE t.token = ? AND u.status = 'active'", [$token]);
            if ($row) {
                if (empty($row['expires_at']) || strtotime($row['expires_at']) > time()) {
                    $this->apiUser = $row;
                    $this->user = $row;
                }
            }
        }
    }

    private function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function error(string $message, int $status = 400, array $errors = []): void {
        $this->json([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    private function success(mixed $data = null, string $message = 'Success', array $extra = []): void {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ];
        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }
        $this->json($response);
    }

    private function requireAuth(): array {
        if (!$this->apiUser) {
            $this->error('Unauthorized. Please log in.', 401);
        }
        return $this->apiUser;
    }

    private function getJsonInput(): array {
        $input = json_decode(file_get_contents('php://input'), true);
        return is_array($input) ? $input : [];
    }

    private function getBaseUrl(): string {
        $configured = setting('app_url', defined('APP_URL') ? APP_URL : '');
        if ($configured && !str_contains($configured, 'localhost') && !str_contains($configured, '127.0.0.1')) {
            return rtrim($configured, '/');
        }
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1:8080';
        return $scheme . '://' . $host;
    }

    private function formatMediaUrl(?string $path): ?string {
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = ltrim($path, '/');
        if (!str_starts_with($path, 'uploads/') && !str_starts_with($path, 'assets/')) {
            $path = 'uploads/' . $path;
        }
        return $this->getBaseUrl() . '/' . $path;
    }

    // ==========================================
    // 1. BOOTSTRAP ENDPOINT
    // ==========================================
    public function bootstrap(): void {
        $lang = $this->getRequestLanguage();

        // News categories
        $categories = $this->rows("SELECT id, name_en, name_si, name_ta, slug, color, icon, order_index 
                                  FROM categories WHERE status = 'active' ORDER BY order_index ASC, id ASC");
        $formattedCats = array_map(function($c) use ($lang) {
            return [
                'id' => (int)$c['id'],
                'slug' => $c['slug'],
                'name' => $c['name_' . $lang] ?: $c['name_en'],
                'name_en' => $c['name_en'],
                'name_si' => $c['name_si'],
                'name_ta' => $c['name_ta'],
                'color' => $c['color'] ?: '#C41230',
                'icon' => $c['icon'] ?: 'newspaper',
                'order' => (int)$c['order_index'],
            ];
        }, $categories);

        // Classified categories
        $classifiedCats = $this->rows("SELECT id, name_en, name_si, name_ta, slug FROM classified_categories ORDER BY id ASC");
        $formattedClassifiedCats = array_map(function($c) use ($lang) {
            return [
                'id' => (int)$c['id'],
                'slug' => $c['slug'],
                'name' => $c['name_' . $lang] ?: $c['name_en'],
                'name_en' => $c['name_en'],
                'name_si' => $c['name_si'],
                'name_ta' => $c['name_ta'],
            ];
        }, $classifiedCats);

        // Latest breaking news alert if any
        $breaking = $this->rows("SELECT a.id, a.slug, a.title_si, a.title_en, a.title_ta, a.published_at 
                                FROM articles a 
                                WHERE a.status = 'published' AND a.is_breaking = 1 
                                ORDER BY a.published_at DESC LIMIT 5");
        $breakingAlerts = array_map(function($b) use ($lang) {
            return [
                'id' => (int)$b['id'],
                'slug' => $b['slug'],
                'title' => $b['title_' . $lang] ?: ($b['title_en'] ?: $b['title_si']),
                'published_at' => $b['published_at'],
            ];
        }, $breaking);

        // Active banner ads for mobile
        $ads = $this->rows("SELECT id, title, position, image_path, url, ad_text, cta_label 
                           FROM advertisements 
                           WHERE is_active = 1 AND (start_date IS NULL OR start_date <= ?) AND (end_date IS NULL OR end_date >= ?)
                           ORDER BY priority DESC LIMIT 5", [date('Y-m-d'), date('Y-m-d')]);
        $formattedAds = array_map(function($ad) {
            return [
                'id' => (int)$ad['id'],
                'title' => $ad['title'],
                'position' => $ad['position'],
                'image_url' => $this->formatMediaUrl($ad['image_path']),
                'url' => $ad['url'],
                'ad_text' => $ad['ad_text'],
                'cta_label' => $ad['cta_label'] ?: 'Learn More',
            ];
        }, $ads);

        $this->success([
            'app_name' => setting('site_name', 'Lanka News Hub'),
            'languages' => [
                ['code' => 'en', 'name' => 'English', 'native' => 'English'],
                ['code' => 'si', 'name' => 'Sinhala', 'native' => 'සිංහල'],
                ['code' => 'ta', 'name' => 'Tamil', 'native' => 'தமிழ்'],
            ],
            'categories' => $formattedCats,
            'classified_categories' => $formattedClassifiedCats,
            'breaking_alerts' => $breakingAlerts,
            'banner_ads' => $formattedAds,
            'contact_email' => setting('contact_email', 'contact@news.liontech.lk'),
            'app_version' => '1.0.0',
            'user' => $this->apiUser ? $this->formatUser($this->apiUser) : null,
        ]);
    }

    // ==========================================
    // 2. NEWS ARTICLES LIST & SEARCH
    // ==========================================
    public function newsList(): void {
        $lang = $this->getRequestLanguage();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(50, (int)($_GET['limit'] ?? 15)));
        $offset = ($page - 1) * $limit;

        $category = trim($_GET['category'] ?? '');
        $section = trim($_GET['section'] ?? '');
        $q = trim($_GET['q'] ?? '');

        $where = "a.status IN ('published','scheduled') AND a.published_at <= ? AND c.status = 'active'";
        $params = [date('Y-m-d H:i:s')];

        // Section filters
        if ($section === 'breaking') {
            $where .= " AND a.is_breaking = 1";
        } elseif ($section === 'featured') {
            $where .= " AND a.is_featured = 1";
        } elseif ($section === 'videos') {
            $where .= " AND (COALESCE(a.video_url, '') <> '' OR a.video_media_id IS NOT NULL)";
        } elseif ($section === 'gallery') {
            $where .= " AND COALESCE(a.gallery_ids, '[]') NOT IN ('', '[]')";
        }

        // Category filter
        if ($category !== '') {
            if (is_numeric($category)) {
                $where .= " AND a.category_id = ?";
                $params[] = (int)$category;
            } else {
                $where .= " AND c.slug = ?";
                $params[] = $category;
            }
        }

        // Search query
        if ($q !== '') {
            $where .= " AND (a.title_en LIKE ? OR a.title_si LIKE ? OR a.title_ta LIKE ? OR a.summary_en LIKE ? OR a.summary_si LIKE ? OR a.tags LIKE ?)";
            $searchTerm = '%' . $q . '%';
            $params = array_merge($params, array_fill(0, 6, $searchTerm));
        }

        // Count total
        $countRow = $this->one("SELECT COUNT(*) AS total FROM articles a JOIN categories c ON c.id = a.category_id WHERE $where", $params);
        $total = (int)($countRow['total'] ?? 0);

        // Fetch articles
        $sql = "SELECT a.id, a.author_id, a.category_id, a.featured_image_id, a.video_media_id,
                       a.slug, a.title_si, a.title_en, a.title_ta, 
                       a.summary_si, a.summary_en, a.summary_ta,
                       a.content_si, a.content_en, a.content_ta,
                       a.status, a.is_breaking, a.is_featured, a.views, 
                       a.published_at, a.created_at, a.gallery_ids, a.video_url, a.tags,
                       c.name_en AS category_name_en, c.name_si AS category_name_si, c.name_ta AS category_name_ta,
                       c.slug AS category_slug, c.color AS category_color,
                       u.name AS author_name, m.file_path AS image_path
                FROM articles a 
                JOIN categories c ON c.id = a.category_id 
                JOIN users u ON u.id = a.author_id 
                LEFT JOIN media m ON m.id = a.featured_image_id 
                WHERE $where 
                ORDER BY a.published_at DESC, a.id DESC 
                LIMIT $limit OFFSET $offset";

        $rows = $this->rows($sql, $params);
        $articles = array_map(fn($row) => $this->formatArticle($row, $lang), $rows);

        $this->success($articles, 'Articles retrieved', [
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'has_more' => ($offset + count($articles)) < $total,
            ]
        ]);
    }

    // ==========================================
    // 3. SINGLE ARTICLE DETAIL
    // ==========================================
    public function newsDetail(string $idOrSlug): void {
        $lang = $this->getRequestLanguage();

        $where = is_numeric($idOrSlug) ? "a.id = ?" : "a.slug = ?";
        $sql = "SELECT a.*, 
                       c.name_en AS category_name_en, c.name_si AS category_name_si, c.name_ta AS category_name_ta,
                       c.slug AS category_slug, c.color AS category_color,
                       u.name AS author_name, m.file_path AS image_path,
                       vm.file_path AS video_media_path
                FROM articles a 
                JOIN categories c ON c.id = a.category_id 
                JOIN users u ON u.id = a.author_id 
                LEFT JOIN media m ON m.id = a.featured_image_id 
                LEFT JOIN media vm ON vm.id = a.video_media_id 
                WHERE $where";

        $row = $this->one($sql, [$idOrSlug]);
        if (!$row) {
            $this->error('Article not found', 404);
        }

        // Increment view count
        $this->run("UPDATE articles SET views = views + 1 WHERE id = ?", [$row['id']]);
        $row['views'] = ((int)$row['views']) + 1;

        $article = $this->formatArticle($row, $lang, true);

        // Fetch gallery images if available
        $galleryImages = [];
        $galleryIds = json_decode($row['gallery_ids'] ?? '[]', true);
        if (is_array($galleryIds) && !empty($galleryIds)) {
            $in = implode(',', array_fill(0, count($galleryIds), '?'));
            $mediaRows = $this->rows("SELECT id, file_path, alt_text, caption FROM media WHERE id IN ($in)", $galleryIds);
            foreach ($mediaRows as $m) {
                $galleryImages[] = [
                    'id' => (int)$m['id'],
                    'url' => $this->formatMediaUrl($m['file_path']),
                    'alt_text' => $m['alt_text'] ?? '',
                    'caption' => $m['caption'] ?? '',
                ];
            }
        }
        $article['gallery_items'] = $galleryImages;

        // Fetch related articles from same category
        $related = $this->rows("SELECT a.id, a.slug, a.title_si, a.title_en, a.title_ta, 
                                      a.summary_si, a.summary_en, a.summary_ta,
                                      a.published_at, m.file_path AS image_path,
                                      c.name_en AS category_name_en, c.name_si AS category_name_si, c.name_ta AS category_name_ta, c.slug AS category_slug
                               FROM articles a 
                               JOIN categories c ON c.id = a.category_id 
                               LEFT JOIN media m ON m.id = a.featured_image_id 
                               WHERE a.category_id = ? AND a.id <> ? AND a.status = 'published' AND a.published_at <= ?
                               ORDER BY a.published_at DESC LIMIT 5", [$row['category_id'], $row['id'], date('Y-m-d H:i:s')]);

        $article['related_articles'] = array_map(fn($r) => $this->formatArticle($r, $lang), $related);

        // Check if user has saved this article
        $article['is_saved'] = false;
        if ($this->apiUser) {
            $saved = $this->one("SELECT id FROM saved_articles WHERE user_id = ? AND article_id = ?", [$this->apiUser['id'], $row['id']]);
            $article['is_saved'] = (bool)$saved;
        }

        $this->success($article, 'Article retrieved');
    }

    // ==========================================
    // 4. LIVE UPDATES
    // ==========================================
    public function liveUpdates(): void {
        $lang = $this->getRequestLanguage();
        // Return latest updates stream
        $sql = "SELECT a.id, a.slug, a.title_si, a.title_en, a.title_ta,
                       a.summary_si, a.summary_en, a.summary_ta,
                       a.published_at, a.is_breaking, c.name_en AS category_name_en,
                       c.name_si AS category_name_si, c.name_ta AS category_name_ta,
                       c.slug AS category_slug, m.file_path AS image_path
                FROM articles a 
                JOIN categories c ON c.id = a.category_id 
                LEFT JOIN media m ON m.id = a.featured_image_id 
                WHERE a.status = 'published' AND a.published_at <= ? 
                ORDER BY a.published_at DESC LIMIT 20";

        $rows = $this->rows($sql, [date('Y-m-d H:i:s')]);
        $updates = array_map(function($r) use ($lang) {
            return [
                'id' => (int)$r['id'],
                'slug' => $r['slug'],
                'title' => $r['title_' . $lang] ?: ($r['title_en'] ?: $r['title_si']),
                'summary' => $r['summary_' . $lang] ?: ($r['summary_en'] ?: $r['summary_si']),
                'published_at' => $r['published_at'],
                'is_breaking' => (bool)$r['is_breaking'],
                'category_name' => $r['category_name_' . $lang] ?: $r['category_name_en'],
                'image_url' => $this->formatMediaUrl($r['image_path']),
            ];
        }, $rows);

        $this->success($updates, 'Live updates retrieved');
    }

    // ==========================================
    // 5. CLASSIFIED ADVERTISEMENTS LIST & FILTER
    // ==========================================
    public function classifiedsList(): void {
        $lang = $this->getRequestLanguage();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = max(1, min(50, (int)($_GET['limit'] ?? 15)));
        $offset = ($page - 1) * $limit;

        $category = trim($_GET['category'] ?? '');
        $q = trim($_GET['q'] ?? '');
        $location = trim($_GET['location'] ?? '');
        $minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
        $sort = trim($_GET['sort'] ?? 'newest');

        $where = "c.status = 'approved' AND c.expires_at > ?";
        $params = [date('Y-m-d H:i:s')];

        if ($category !== '') {
            if (is_numeric($category)) {
                $where .= " AND c.category_id = ?";
                $params[] = (int)$category;
            } else {
                $where .= " AND cc.slug = ?";
                $params[] = $category;
            }
        }

        if ($q !== '') {
            $where .= " AND (c.title LIKE ? OR c.description LIKE ?)";
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        if ($location !== '') {
            $where .= " AND c.location LIKE ?";
            $params[] = '%' . $location . '%';
        }

        if ($minPrice !== null && $minPrice >= 0) {
            $where .= " AND c.price >= ?";
            $params[] = $minPrice;
        }

        if ($maxPrice !== null && $maxPrice >= 0) {
            $where .= " AND c.price <= ?";
            $params[] = $maxPrice;
        }

        $order = match($sort) {
            'price_asc' => "c.price ASC, c.id DESC",
            'price_desc' => "c.price DESC, c.id DESC",
            'premium' => "c.is_premium DESC, c.created_at DESC",
            default => "c.is_premium DESC, c.created_at DESC, c.id DESC",
        };

        $countRow = $this->one("SELECT COUNT(*) AS total FROM classifieds c JOIN classified_categories cc ON cc.id = c.category_id WHERE $where", $params);
        $total = (int)($countRow['total'] ?? 0);

        $sql = "SELECT c.*, cc.name_en AS category_name_en, cc.name_si AS category_name_si, cc.name_ta AS category_name_ta, cc.slug AS category_slug
                FROM classifieds c 
                JOIN classified_categories cc ON cc.id = c.category_id 
                WHERE $where 
                ORDER BY $order 
                LIMIT $limit OFFSET $offset";

        $rows = $this->rows($sql, $params);
        $userFavorites = [];
        if ($this->apiUser) {
            $favRows = $this->rows("SELECT classified_id FROM favorites WHERE user_id = ?", [$this->apiUser['id']]);
            $userFavorites = array_column($favRows, 'classified_id');
        }

        $items = array_map(fn($row) => $this->formatClassified($row, $lang, $userFavorites), $rows);

        $this->success($items, 'Classifieds retrieved', [
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'has_more' => ($offset + count($items)) < $total,
            ]
        ]);
    }

    // ==========================================
    // 6. SINGLE CLASSIFIED DETAIL
    // ==========================================
    public function classifiedDetail(string $id): void {
        $lang = $this->getRequestLanguage();
        $sql = "SELECT c.*, cc.name_en AS category_name_en, cc.name_si AS category_name_si, cc.name_ta AS category_name_ta, cc.slug AS category_slug,
                       u.name AS user_name, u.email AS user_email
                FROM classifieds c 
                JOIN classified_categories cc ON cc.id = c.category_id 
                LEFT JOIN users u ON u.id = c.user_id 
                WHERE c.id = ?";

        $row = $this->one($sql, [$id]);
        if (!$row) {
            $this->error('Advertisement not found', 404);
        }

        // If ad is not approved, only owner or admin can view
        if ($row['status'] !== 'approved' || strtotime($row['expires_at']) <= time()) {
            if (!$this->apiUser || ($this->apiUser['id'] != $row['user_id'] && $this->apiUser['role'] !== 'super_admin' && $this->apiUser['role'] !== 'editor')) {
                $this->error('This advertisement is inactive or under review.', 404);
            }
        }

        $isFavorite = false;
        if ($this->apiUser) {
            $fav = $this->one("SELECT id FROM favorites WHERE user_id = ? AND classified_id = ?", [$this->apiUser['id'], $id]);
            $isFavorite = (bool)$fav;
        }

        $ad = $this->formatClassified($row, $lang, $isFavorite ? [(int)$id] : []);
        $this->success($ad, 'Advertisement details retrieved');
    }

    // ==========================================
    // 7. SUBMIT CLASSIFIED AD
    // ==========================================
    public function submitClassified(): void {
        $user = $this->requireAuth();
        $input = !empty($_POST) ? $_POST : $this->getJsonInput();

        $title = trim($input['title'] ?? '');
        $description = trim($input['description'] ?? '');
        $categoryId = (int)($input['category_id'] ?? 0);
        $language = trim($input['language'] ?? 'en');
        if (!in_array($language, ['en', 'si', 'ta'], true)) $language = 'en';
        $location = trim($input['location'] ?? '');
        $price = isset($input['price']) && is_numeric($input['price']) ? (float)$input['price'] : null;
        $contactName = trim($input['contact_name'] ?? $user['name']);
        $contactEmail = trim($input['contact_email'] ?? $user['email']);
        $contactPhone = trim($input['contact_phone'] ?? '');
        $premiumRequested = !empty($input['premium_requested']) ? 1 : 0;

        $errors = [];
        if (!$title) $errors['title'] = 'Title is required';
        if (!$description) $errors['description'] = 'Description is required';
        if (!$location) $errors['location'] = 'Location is required';
        if (!$contactName) $errors['contact_name'] = 'Contact name is required';
        if (!$contactPhone) $errors['contact_phone'] = 'Contact phone number is required';
        if (!$contactEmail || !filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) $errors['contact_email'] = 'Valid contact email is required';

        $catCheck = $this->one("SELECT id FROM classified_categories WHERE id = ?", [$categoryId]);
        if (!$catCheck) $errors['category_id'] = 'Valid category must be selected';

        if (!empty($errors)) {
            $this->error('Please correct the validation errors', 422, $errors);
        }

        // Process images
        $imagePaths = [];
        // Case 1: Uploaded via $_FILES
        if (!empty($_FILES['images'])) {
            $files = $_FILES['images'];
            $fileCount = is_array($files['name']) ? count($files['name']) : 1;
            for ($i = 0; $i < $fileCount; $i++) {
                if ($i >= 8) break;
                $fileItem = is_array($files['name']) ? [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ] : $files;
                if ($fileItem['error'] === UPLOAD_ERR_NO_FILE) continue;
                try {
                    $saved = Upload::store($fileItem, true);
                    $imagePaths[] = $saved['file_path'];
                } catch (\Throwable $e) {
                    // continue
                }
            }
        }

        // Case 2: Passed as image URLs or existing array in input
        if (empty($imagePaths) && !empty($input['images']) && is_array($input['images'])) {
            foreach ($input['images'] as $img) {
                if (is_string($img) && strlen($img) > 0) {
                    $imagePaths[] = $img;
                }
            }
        }

        $data = [
            'user_id' => $user['id'],
            'category_id' => $categoryId,
            'language' => $language,
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'price' => $price,
            'contact_name' => $contactName,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
            'images' => json_encode($imagePaths),
            'status' => 'pending',
            'moderation_note' => null,
            'premium_requested' => $premiumRequested,
            'is_premium' => 0,
            'expires_at' => date('Y-m-d H:i:s', time() + 30 * 86400),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $adId = $this->save('classifieds', $data);
        $this->audit('Submitted ad via Mobile API', 'classifieds', $adId);

        $this->success([
            'id' => $adId,
            'status' => 'pending',
            'message' => 'Advertisement submitted successfully and is awaiting review.'
        ], 'Advertisement submitted successfully');
    }

    // ==========================================
    // 8. EDIT & RENEW CLASSIFIED AD
    // ==========================================
    public function updateClassified(string $id): void {
        $user = $this->requireAuth();
        $ad = $this->one("SELECT * FROM classifieds WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$ad) {
            $this->error('Advertisement not found or unauthorized', 404);
        }

        $input = !empty($_POST) ? $_POST : $this->getJsonInput();

        $update = [
            'updated_at' => date('Y-m-d H:i:s'),
            'status' => 'pending', // Re-submits for moderation on edit
        ];

        if (isset($input['title'])) $update['title'] = trim($input['title']);
        if (isset($input['description'])) $update['description'] = trim($input['description']);
        if (isset($input['location'])) $update['location'] = trim($input['location']);
        if (isset($input['price'])) $update['price'] = is_numeric($input['price']) ? (float)$input['price'] : null;
        if (isset($input['category_id'])) $update['category_id'] = (int)$input['category_id'];
        if (isset($input['contact_name'])) $update['contact_name'] = trim($input['contact_name']);
        if (isset($input['contact_email'])) $update['contact_email'] = trim($input['contact_email']);
        if (isset($input['contact_phone'])) $update['contact_phone'] = trim($input['contact_phone']);
        if (isset($input['premium_requested'])) $update['premium_requested'] = !empty($input['premium_requested']) ? 1 : 0;

        // Process images if provided
        if (!empty($_FILES['images'])) {
            $files = $_FILES['images'];
            $fileCount = is_array($files['name']) ? count($files['name']) : 1;
            $newPaths = [];
            for ($i = 0; $i < $fileCount; $i++) {
                if ($i >= 8) break;
                $fileItem = is_array($files['name']) ? [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ] : $files;
                if ($fileItem['error'] === UPLOAD_ERR_NO_FILE) continue;
                try {
                    $saved = Upload::store($fileItem, true);
                    $newPaths[] = $saved['file_path'];
                } catch (\Throwable $e) {}
            }
            if (!empty($newPaths)) {
                $update['images'] = json_encode($newPaths);
            }
        } elseif (isset($input['images']) && is_array($input['images'])) {
            $update['images'] = json_encode($input['images']);
        }

        $this->save('classifieds', $update, (int)$id);
        $this->audit('Updated ad via Mobile API', 'classifieds', (int)$id);

        $this->success(['id' => (int)$id], 'Advertisement updated successfully');
    }

    public function renewClassified(string $id): void {
        $user = $this->requireAuth();
        $ad = $this->one("SELECT * FROM classifieds WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$ad) {
            $this->error('Advertisement not found', 404);
        }

        $this->save('classifieds', [
            'status' => 'pending',
            'is_premium' => 0,
            'moderation_note' => null,
            'expires_at' => date('Y-m-d H:i:s', time() + 30 * 86400),
            'updated_at' => date('Y-m-d H:i:s')
        ], (int)$id);

        $this->audit('Renewed ad via Mobile API', 'classifieds', (int)$id);
        $this->success(['id' => (int)$id], 'Advertisement renewed and submitted for review');
    }

    public function deleteClassified(string $id): void {
        $user = $this->requireAuth();
        $ad = $this->one("SELECT * FROM classifieds WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$ad) {
            $this->error('Advertisement not found', 404);
        }

        $this->save('classifieds', [
            'status' => 'withdrawn',
            'updated_at' => date('Y-m-d H:i:s')
        ], (int)$id);

        $this->success(['id' => (int)$id], 'Advertisement withdrawn');
    }

    // ==========================================
    // 9. FAVORITES / SAVED CLASSIFIEDS
    // ==========================================
    public function toggleFavorite(string $id): void {
        $user = $this->requireAuth();
        $fav = $this->one("SELECT id FROM favorites WHERE user_id = ? AND classified_id = ?", [$user['id'], $id]);

        if ($fav) {
            $this->run("DELETE FROM favorites WHERE id = ?", [$fav['id']]);
            $isFavorite = false;
            $msg = 'Removed from favorites';
        } else {
            $this->save('favorites', [
                'user_id' => $user['id'],
                'classified_id' => (int)$id
            ]);
            $isFavorite = true;
            $msg = 'Added to favorites';
        }

        $this->success(['is_favorite' => $isFavorite], $msg);
    }

    public function userFavorites(): void {
        $user = $this->requireAuth();
        $lang = $this->getRequestLanguage();

        $sql = "SELECT c.*, cc.name_en AS category_name_en, cc.name_si AS category_name_si, cc.name_ta AS category_name_ta, cc.slug AS category_slug
                FROM favorites f 
                JOIN classifieds c ON c.id = f.classified_id 
                JOIN classified_categories cc ON cc.id = c.category_id 
                WHERE f.user_id = ? 
                ORDER BY f.id DESC";

        $rows = $this->rows($sql, [$user['id']]);
        $items = array_map(fn($row) => $this->formatClassified($row, $lang, [(int)$row['id']]), $rows);

        $this->success($items, 'Favorites retrieved');
    }

    // ==========================================
    // 10. USER ADS DASHBOARD
    // ==========================================
    public function userAds(): void {
        $user = $this->requireAuth();
        $lang = $this->getRequestLanguage();

        $sql = "SELECT c.*, cc.name_en AS category_name_en, cc.name_si AS category_name_si, cc.name_ta AS category_name_ta, cc.slug AS category_slug
                FROM classifieds c 
                JOIN classified_categories cc ON cc.id = c.category_id 
                WHERE c.user_id = ? 
                ORDER BY c.created_at DESC";

        $rows = $this->rows($sql, [$user['id']]);
        $items = array_map(fn($row) => $this->formatClassified($row, $lang, []), $rows);

        $activeCount = 0;
        $pendingCount = 0;
        $expiredCount = 0;

        foreach ($items as $item) {
            if ($item['status'] === 'approved' && !$item['is_expired']) {
                $activeCount++;
            } elseif ($item['status'] === 'pending') {
                $pendingCount++;
            } else {
                $expiredCount++;
            }
        }

        $this->success([
            'ads' => $items,
            'summary' => [
                'total' => count($items),
                'active' => $activeCount,
                'pending' => $pendingCount,
                'expired' => $expiredCount,
            ]
        ], 'User advertisements retrieved');
    }

    // ==========================================
    // 11. AUTHENTICATION & PROFILE
    // ==========================================
    public function register(): void {
        $input = $this->getJsonInput() ?: $_POST;

        $name = trim($input['name'] ?? '');
        $email = strtolower(trim($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');
        $phone = trim($input['phone'] ?? '');

        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            $this->error('Please provide a valid name, email address, and a password with at least 6 characters.', 422);
        }

        $existing = $this->one("SELECT id FROM users WHERE email = ?", [$email]);
        if ($existing) {
            $this->error('An account with this email address already exists.', 409);
        }

        $memberRole = $this->one("SELECT id FROM roles WHERE slug = 'member'");
        $roleId = $memberRole ? (int)$memberRole['id'] : 4;

        $userId = $this->save('users', [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
            'allowed_languages' => 'en,si,ta',
        ]);

        $token = bin2hex(random_bytes(32));
        $this->save('api_tokens', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 365 * 86400),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Default notification preferences
        $this->save('notification_preferences', [
            'user_id' => $userId,
            'breaking_news' => 1,
            'categories' => json_encode(['local', 'politics', 'business', 'sports', 'technology']),
            'ad_updates' => 1,
            'promotions' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $user = $this->one("SELECT u.*, r.slug AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?", [$userId]);

        $this->success([
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 'Registration successful', ['status_code' => 201]);
    }

    public function login(): void {
        $input = $this->getJsonInput() ?: $_POST;

        $email = strtolower(trim($input['email'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if (!$email || !$password) {
            $this->error('Email and password are required.', 422);
        }

        $user = $this->one("SELECT u.*, r.slug AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = ? AND u.status = 'active'", [$email]);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->error('Invalid email or password.', 401);
        }

        $token = bin2hex(random_bytes(32));
        $this->save('api_tokens', [
            'user_id' => $user['id'],
            'token' => $token,
            'expires_at' => date('Y-m-d H:i:s', time() + 365 * 86400),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->audit('Logged in via Mobile API', 'users', (int)$user['id']);

        $this->success([
            'token' => $token,
            'user' => $this->formatUser($user),
        ], 'Login successful');
    }

    public function me(): void {
        $user = $this->requireAuth();
        $this->success($this->formatUser($user), 'User profile retrieved');
    }

    public function updateProfile(): void {
        $user = $this->requireAuth();
        $input = $this->getJsonInput() ?: $_POST;

        $update = [];
        if (!empty($input['name'])) $update['name'] = trim($input['name']);
        if (!empty($input['password']) && strlen($input['password']) >= 6) {
            $update['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if (!empty($update)) {
            $this->save('users', $update, (int)$user['id']);
        }

        $fresh = $this->one("SELECT u.*, r.slug AS role FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?", [$user['id']]);
        $this->success($this->formatUser($fresh), 'Profile updated successfully');
    }

    // ==========================================
    // 12. SAVED ARTICLES (BOOKMARKS)
    // ==========================================
    public function toggleSavedArticle(string $articleId): void {
        $user = $this->requireAuth();
        $saved = $this->one("SELECT id FROM saved_articles WHERE user_id = ? AND article_id = ?", [$user['id'], $articleId]);

        if ($saved) {
            $this->run("DELETE FROM saved_articles WHERE id = ?", [$saved['id']]);
            $isSaved = false;
            $msg = 'Article removed from bookmarks';
        } else {
            $this->save('saved_articles', [
                'user_id' => $user['id'],
                'article_id' => (int)$articleId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $isSaved = true;
            $msg = 'Article bookmarked';
        }

        $this->success(['is_saved' => $isSaved], $msg);
    }

    public function userSavedArticles(): void {
        $user = $this->requireAuth();
        $lang = $this->getRequestLanguage();

        $sql = "SELECT a.id, a.slug, a.title_si, a.title_en, a.title_ta, 
                       a.summary_si, a.summary_en, a.summary_ta,
                       a.content_si, a.content_en, a.content_ta,
                       a.status, a.is_breaking, a.is_featured, a.views, 
                       a.published_at, a.created_at, a.gallery_ids, a.video_url, a.tags,
                       c.name_en AS category_name_en, c.name_si AS category_name_si, c.name_ta AS category_name_ta,
                       c.slug AS category_slug, c.color AS category_color,
                       u.name AS author_name, m.file_path AS image_path
                FROM saved_articles sa 
                JOIN articles a ON a.id = sa.article_id 
                JOIN categories c ON c.id = a.category_id 
                JOIN users u ON u.id = a.author_id 
                LEFT JOIN media m ON m.id = a.featured_image_id 
                WHERE sa.user_id = ? 
                ORDER BY sa.created_at DESC";

        $rows = $this->rows($sql, [$user['id']]);
        $articles = array_map(function($row) use ($lang) {
            $formatted = $this->formatArticle($row, $lang, true);
            $formatted['is_saved'] = true;
            return $formatted;
        }, $rows);

        $this->success($articles, 'Saved articles retrieved');
    }

    // ==========================================
    // 13. PUSH NOTIFICATIONS & PREFERENCES
    // ==========================================
    public function getNotificationPreferences(): void {
        $user = $this->requireAuth();
        $prefs = $this->one("SELECT * FROM notification_preferences WHERE user_id = ?", [$user['id']]);

        if (!$prefs) {
            $prefs = [
                'breaking_news' => 1,
                'categories' => json_encode(['local', 'politics', 'business', 'sports', 'technology']),
                'ad_updates' => 1,
                'promotions' => 1,
            ];
        } else {
            $prefs['categories'] = json_decode($prefs['categories'] ?? '[]', true);
        }

        $this->success([
            'breaking_news' => (bool)$prefs['breaking_news'],
            'categories' => is_array($prefs['categories']) ? $prefs['categories'] : [],
            'ad_updates' => (bool)$prefs['ad_updates'],
            'promotions' => (bool)$prefs['promotions'],
        ], 'Preferences retrieved');
    }

    public function updateNotificationPreferences(): void {
        $user = $this->requireAuth();
        $input = $this->getJsonInput() ?: $_POST;

        $breaking = isset($input['breaking_news']) ? ($input['breaking_news'] ? 1 : 0) : 1;
        $categories = isset($input['categories']) && is_array($input['categories']) ? json_encode($input['categories']) : json_encode(['local', 'politics']);
        $adUpdates = isset($input['ad_updates']) ? ($input['ad_updates'] ? 1 : 0) : 1;
        $promotions = isset($input['promotions']) ? ($input['promotions'] ? 1 : 0) : 1;

        $existing = $this->one("SELECT id FROM notification_preferences WHERE user_id = ?", [$user['id']]);
        if ($existing) {
            $this->run("UPDATE notification_preferences SET breaking_news=?, categories=?, ad_updates=?, promotions=?, updated_at=? WHERE id=?", 
                [$breaking, $categories, $adUpdates, $promotions, date('Y-m-d H:i:s'), $existing['id']]);
        } else {
            $this->save('notification_preferences', [
                'user_id' => $user['id'],
                'breaking_news' => $breaking,
                'categories' => $categories,
                'ad_updates' => $adUpdates,
                'promotions' => $promotions,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->success(null, 'Notification preferences updated');
    }

    public function registerDeviceToken(): void {
        $input = $this->getJsonInput() ?: $_POST;
        $token = trim($input['device_token'] ?? '');
        $platform = trim($input['platform'] ?? 'android');
        $topics = isset($input['topics']) && is_array($input['topics']) ? json_encode($input['topics']) : '[]';

        if (!$token) {
            $this->error('Device token is required', 422);
        }

        $userId = $this->apiUser ? $this->apiUser['id'] : null;

        $existing = $this->one("SELECT id FROM device_tokens WHERE token = ?", [$token]);
        if ($existing) {
            $this->run("UPDATE device_tokens SET user_id=?, platform=?, topics=?, updated_at=? WHERE id=?",
                [$userId, $platform, $topics, date('Y-m-d H:i:s'), $existing['id']]);
        } else {
            $this->save('device_tokens', [
                'user_id' => $userId,
                'token' => $token,
                'platform' => $platform,
                'topics' => $topics,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $this->success(null, 'Device token registered');
    }

    public function notificationsList(): void {
        $lang = $this->getRequestLanguage();
        // Return latest notifications feed (breaking news alerts + system notices)
        $breaking = $this->rows("SELECT id, slug, title_si, title_en, title_ta, published_at 
                                FROM articles 
                                WHERE is_breaking = 1 AND status = 'published' 
                                ORDER BY published_at DESC LIMIT 10");

        $items = [];
        foreach ($breaking as $b) {
            $items[] = [
                'id' => 'brk_' . $b['id'],
                'type' => 'breaking_news',
                'title' => 'Breaking News Alert',
                'body' => $b['title_' . $lang] ?: ($b['title_en'] ?: $b['title_si']),
                'article_id' => (int)$b['id'],
                'article_slug' => $b['slug'],
                'timestamp' => $b['published_at'],
                'is_read' => false,
            ];
        }

        // If user logged in, add ad status notices
        if ($this->apiUser) {
            $userAds = $this->rows("SELECT id, title, status, moderation_note, updated_at 
                                   FROM classifieds 
                                   WHERE user_id = ? AND status IN ('approved', 'pending') 
                                   ORDER BY updated_at DESC LIMIT 5", [$this->apiUser['id']]);
            foreach ($userAds as $ad) {
                $statusText = $ad['status'] === 'approved' ? 'has been approved and is now live!' : 'is currently pending review.';
                $items[] = [
                    'id' => 'ad_' . $ad['id'],
                    'type' => 'advertisement_update',
                    'title' => 'Advertisement Status Update',
                    'body' => "Your listing '{$ad['title']}' {$statusText}",
                    'ad_id' => (int)$ad['id'],
                    'timestamp' => $ad['updated_at'],
                    'is_read' => false,
                ];
            }
        }

        // Promotional notification sample
        $items[] = [
            'id' => 'promo_1',
            'type' => 'promotion',
            'title' => 'Welcome to Lanka News Hub Mobile',
            'body' => 'Get real-time news alerts in Sinhala, English, and Tamil. Place classified ads effortlessly!',
            'timestamp' => date('Y-m-d H:i:s', time() - 86400),
            'is_read' => true,
        ];

        $this->success($items, 'Notifications retrieved');
    }

    // ==========================================
    // HELPER FORMATTERS
    // ==========================================
    private function getRequestLanguage(): string {
        $lang = strtolower(trim($_SERVER['HTTP_X_APP_LANGUAGE'] ?? $_GET['language'] ?? 'en'));
        return in_array($lang, ['en', 'si', 'ta'], true) ? $lang : 'en';
    }

    private function formatArticle(array $row, string $lang, bool $includeFull = false): array {
        $title = $row['title_' . $lang] ?: ($row['title_en'] ?: $row['title_si']);
        $summary = $row['summary_' . $lang] ?: ($row['summary_en'] ?: $row['summary_si']);
        $content = $row['content_' . $lang] ?: ($row['content_en'] ?: $row['content_si']);

        $categoryName = $row['category_name_' . $lang] ?? ($row['category_name_en'] ?? '');

        $data = [
            'id' => (int)$row['id'],
            'slug' => $row['slug'],
            'title' => $title,
            'summary' => $summary,
            'titles' => [
                'en' => $row['title_en'] ?? '',
                'si' => $row['title_si'] ?? '',
                'ta' => $row['title_ta'] ?? '',
            ],
            'summaries' => [
                'en' => $row['summary_en'] ?? '',
                'si' => $row['summary_si'] ?? '',
                'ta' => $row['summary_ta'] ?? '',
            ],
            'author_name' => $row['author_name'] ?? 'Editorial Desk',
            'category_id' => (int)$row['category_id'],
            'category_name' => $categoryName,
            'category_slug' => $row['category_slug'] ?? '',
            'category_color' => $row['category_color'] ?? '#C41230',
            'image_url' => $this->formatMediaUrl($row['image_path'] ?? null),
            'video_url' => $row['video_url'] ?? null,
            'is_breaking' => (bool)($row['is_breaking'] ?? false),
            'is_featured' => (bool)($row['is_featured'] ?? false),
            'views' => (int)($row['views'] ?? 0),
            'tags' => array_values(array_filter(array_map('trim', explode(',', $row['tags'] ?? '')))),
            'published_at' => $row['published_at'] ?? $row['created_at'],
        ];

        if ($includeFull) {
            $data['content'] = $content;
            $data['contents'] = [
                'en' => $row['content_en'] ?? '',
                'si' => $row['content_si'] ?? '',
                'ta' => $row['content_ta'] ?? '',
            ];
            $data['video_media_url'] = $this->formatMediaUrl($row['video_media_path'] ?? null);
        }

        return $data;
    }

    private function formatClassified(array $row, string $lang, array $userFavorites = []): array {
        $rawImages = json_decode($row['images'] ?? '[]', true) ?: [];
        $images = [];
        foreach ($rawImages as $img) {
            if ($img) $images[] = $this->formatMediaUrl($img);
        }

        $categoryName = $row['category_name_' . $lang] ?? ($row['category_name_en'] ?? '');
        $isExpired = !empty($row['expires_at']) && strtotime($row['expires_at']) < time();

        return [
            'id' => (int)$row['id'],
            'user_id' => (int)$row['user_id'],
            'category_id' => (int)$row['category_id'],
            'category_name' => $categoryName,
            'category_slug' => $row['category_slug'] ?? '',
            'language' => $row['language'],
            'title' => $row['title'],
            'description' => $row['description'],
            'location' => $row['location'],
            'price' => $row['price'] !== null ? (float)$row['price'] : null,
            'formatted_price' => $row['price'] !== null ? 'LKR ' . number_format((float)$row['price'], 2) : 'Negotiable',
            'contact_name' => $row['contact_name'],
            'contact_email' => $row['contact_email'],
            'contact_phone' => $row['contact_phone'],
            'images' => $images,
            'thumbnail_url' => $images[0] ?? null,
            'status' => $row['status'],
            'moderation_note' => $row['moderation_note'] ?? null,
            'is_premium' => (bool)$row['is_premium'],
            'premium_requested' => (bool)$row['premium_requested'],
            'is_expired' => $isExpired,
            'is_favorite' => in_array((int)$row['id'], $userFavorites, true),
            'expires_at' => $row['expires_at'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ];
    }

    private function formatUser(array $user): array {
        return [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'member',
            'status' => $user['status'],
            'allowed_languages' => explode(',', $user['allowed_languages'] ?? 'en,si,ta'),
            'created_at' => $user['created_at'],
        ];
    }
}

