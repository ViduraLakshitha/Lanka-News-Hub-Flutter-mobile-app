<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Article;
use App\Models\Category;
use App\Models\Media;

class ArticleController extends Controller {
    
    public function __construct() {
        AuthMiddleware::check();
    }

    public function index() {
        $articleModel = new Article();
        $articles = $articleModel->allWithDetails();
        
        $this->view('admin/articles/index', [
            'title' => 'Articles - Newsroom Admin',
            'articles' => $articles
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title'] ?? ($_POST['title_en'] ?? ($_POST['title_si'] ?? '')));
            $summary = trim($_POST['summary'] ?? ($_POST['summary_en'] ?? ($_POST['summary_si'] ?? '')));
            $content = trim($_POST['content'] ?? ($_POST['content_en'] ?? ($_POST['content_si'] ?? '')));

            $data = [
                'author_id' => $_SESSION['user_id'],
                'category_id' => (int)($_POST['category_id'] ?? 0),
                'title_si' => $title,
                'title_en' => $title,
                'slug' => strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'] ?? ''))),
                'summary_si' => $summary,
                'summary_en' => $summary,
                'content_si' => $content,
                'content_en' => $content,
                'status' => $_POST['status'] ?? 'published',
                'is_breaking' => isset($_POST['is_breaking']) ? 1 : 0,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'featured_image_id' => !empty($_POST['featured_image_id']) ? $_POST['featured_image_id'] : null,
                'published_at' => ($_POST['status'] ?? 'published') === 'published' ? date('Y-m-d H:i:s') : null
            ];
            
            $articleModel = new Article();
            $articleModel->create($data);
            
            $this->redirect('/admin/articles');
        }
        
        $categoryModel = new Category();
        $categories = $categoryModel->allOrdered();
        
        $mediaModel = new Media();
        $media = $mediaModel->allOrdered();
        
        $this->view('admin/articles/create', [
            'title' => 'Create Article - Newsroom Admin',
            'categories' => $categories,
            'media' => $media
        ]);
    }

    public function delete($id) {
        $articleModel = new Article();
        $articleModel->delete($id);
        $this->redirect('/admin/articles');
    }
}
