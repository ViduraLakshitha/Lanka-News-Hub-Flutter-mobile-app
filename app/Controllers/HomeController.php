<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Article;
use App\Models\Category;

class HomeController extends Controller {
    public function index() {
        $categoryModel = new Category();

        // Get only published articles
        $sql = "SELECT a.*, c.name_en as category_name_en, c.name_si as category_name, c.slug as category_slug, u.name as author_name, m.file_path as image_path
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN media m ON a.featured_image_id = m.id
                WHERE a.status = 'published'
                ORDER BY a.published_at DESC";
                
        $stmt = \App\Core\Database::getInstance()->getConnection()->prepare($sql);
        $stmt->execute();
        $allNews = $stmt->fetchAll();

        // Split into breaking, featured, latest
        $breaking = array_filter($allNews, fn($a) => $a['is_breaking'] == 1);
        $featured = array_filter($allNews, fn($a) => $a['is_featured'] == 1);
        
        $this->view('public/home', [
            'title' => 'Lanka News Hub - Sri Lanka\'s Premier Digital News Network',
            'latest' => array_slice($allNews, 0, 15),
            'breaking' => array_slice($breaking, 0, 5),
            'featured' => array_slice($featured, 0, 5),
            'categories' => $categoryModel->allOrdered()
        ]);
    }

    public function show($slug) {
        $categoryModel = new Category();

        $sql = "SELECT a.*, c.name_en as category_name_en, c.name_si as category_name, c.slug as category_slug, u.name as author_name, m.file_path as image_path
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN media m ON a.featured_image_id = m.id
                WHERE a.slug = :slug AND a.status = 'published'";
                
        $stmt = \App\Core\Database::getInstance()->getConnection()->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $article = $stmt->fetch();

        if (!$article) {
            http_response_code(404);
            die("Article not found.");
        }

        // Increment views
        $updateSql = "UPDATE articles SET views = views + 1 WHERE id = :id";
        $updateStmt = \App\Core\Database::getInstance()->getConnection()->prepare($updateSql);
        $updateStmt->execute(['id' => $article['id']]);

        // Get related
        $relatedSql = "SELECT a.*, c.name_en as category_name_en, c.name_si as category_name, m.file_path as image_path FROM articles a 
                       LEFT JOIN categories c ON a.category_id = c.id
                       LEFT JOIN media m ON a.featured_image_id = m.id 
                       WHERE a.category_id = :cat_id AND a.id != :id AND a.status = 'published' 
                       ORDER BY a.published_at DESC LIMIT 3";
        $relatedStmt = \App\Core\Database::getInstance()->getConnection()->prepare($relatedSql);
        $relatedStmt->execute(['cat_id' => $article['category_id'], 'id' => $article['id']]);
        $related = $relatedStmt->fetchAll();

        $articleTitle = !empty($article['title_en']) ? $article['title_en'] : $article['title_si'];

        $this->view('public/article', [
            'title' => $articleTitle . ' - Lanka News Hub',
            'article' => $article,
            'related' => $related,
            'categories' => $categoryModel->allOrdered()
        ]);
    }

    public function category($slug) {
        $categoryModel = new Category();
        $db = \App\Core\Database::getInstance()->getConnection();

        // Get category info
        $stmtCat = $db->prepare("SELECT * FROM categories WHERE slug = :slug AND status = 'active' LIMIT 1");
        $stmtCat->execute(['slug' => $slug]);
        $currentCat = $stmtCat->fetch();

        if (!$currentCat) {
            http_response_code(404);
            die("Category not found.");
        }

        // Get articles for this category
        $sql = "SELECT a.*, c.name_en as category_name_en, c.name_si as category_name, c.slug as category_slug, u.name as author_name, m.file_path as image_path
                FROM articles a
                LEFT JOIN categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                LEFT JOIN media m ON a.featured_image_id = m.id
                WHERE a.category_id = :cat_id AND a.status = 'published'
                ORDER BY a.published_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute(['cat_id' => $currentCat['id']]);
        $articles = $stmt->fetchAll();

        $catName = !empty($currentCat['name_en']) ? $currentCat['name_en'] : $currentCat['name_si'];

        $this->view('public/category', [
            'title' => $catName . ' - Lanka News Hub',
            'current_category' => $currentCat,
            'articles' => $articles,
            'categories' => $categoryModel->allOrdered()
        ]);
    }

    public function search() {
        $categoryModel = new Category();
        $query = trim($_GET['q'] ?? '');
        $articles = [];

        if (!empty($query)) {
            $db = \App\Core\Database::getInstance()->getConnection();
            $sql = "SELECT a.*, c.name_en as category_name_en, c.name_si as category_name, c.slug as category_slug, u.name as author_name, m.file_path as image_path
                    FROM articles a
                    LEFT JOIN categories c ON a.category_id = c.id
                    LEFT JOIN users u ON a.author_id = u.id
                    LEFT JOIN media m ON a.featured_image_id = m.id
                    WHERE a.status = 'published' AND (a.title_si LIKE :q OR a.content_si LIKE :q OR a.title_en LIKE :q OR a.content_en LIKE :q)
                    ORDER BY a.published_at DESC LIMIT 30";
            $stmt = $db->prepare($sql);
            $stmt->execute(['q' => "%{$query}%"]);
            $articles = $stmt->fetchAll();
        }

        $this->view('public/search', [
            'title' => 'Search: ' . htmlspecialchars($query) . ' - Lanka News Hub',
            'query' => $query,
            'articles' => $articles,
            'categories' => $categoryModel->allOrdered()
        ]);
    }
}
