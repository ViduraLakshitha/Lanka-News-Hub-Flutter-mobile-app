<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Middleware\AuthMiddleware;

class DashboardController extends Controller {
    
    public function __construct() {
        AuthMiddleware::check();
    }

    public function index() {
        $db = Database::getInstance()->getConnection();

        // 1. Total Articles
        $stmtArticles = $db->query("SELECT COUNT(*) as count FROM articles");
        $totalArticles = $stmtArticles->fetch()['count'] ?? 0;

        // 2. Published Articles
        $stmtPub = $db->query("SELECT COUNT(*) as count FROM articles WHERE status = 'published'");
        $publishedArticles = $stmtPub->fetch()['count'] ?? 0;

        // 3. Total Views
        $stmtViews = $db->query("SELECT SUM(views) as total_views FROM articles");
        $totalViews = $stmtViews->fetch()['total_views'] ?? 0;

        // 4. Total Categories
        $stmtCats = $db->query("SELECT COUNT(*) as count FROM categories");
        $totalCategories = $stmtCats->fetch()['count'] ?? 0;

        // 5. Total Media
        $stmtMedia = $db->query("SELECT COUNT(*) as count FROM media");
        $totalMedia = $stmtMedia->fetch()['count'] ?? 0;

        // 6. Recent Articles
        $stmtRecent = $db->query("SELECT a.*, c.name_si as category_name, u.name as author_name 
                                  FROM articles a 
                                  LEFT JOIN categories c ON a.category_id = c.id 
                                  LEFT JOIN users u ON a.author_id = u.id 
                                  ORDER BY a.created_at DESC LIMIT 5");
        $recentArticles = $stmtRecent->fetchAll();

        $data = [
            'title' => 'Editorial Overview - Lanka News CMS',
            'total_articles' => (int)$totalArticles,
            'published_articles' => (int)$publishedArticles,
            'total_views' => (int)$totalViews,
            'total_categories' => (int)$totalCategories,
            'total_media' => (int)$totalMedia,
            'recent_articles' => $recentArticles
        ];
        
        $this->view('admin/dashboard', $data);
    }
}
