<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Category;

class CategoryController extends Controller {
    
    public function __construct() {
        AuthMiddleware::check();
    }

    public function index() {
        $categoryModel = new Category();
        $categories = $categoryModel->allOrdered();
        
        $this->view('admin/categories/index', [
            'title' => 'Categories - Admin',
            'categories' => $categories
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? ($_POST['name_en'] ?? ($_POST['name_si'] ?? '')));
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'] ?? '')));
            
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }

            $data = [
                'name_si' => $name,
                'name_en' => $name,
                'slug' => $slug,
                'color' => $_POST['color'] ?? '#dc2626',
                'order_index' => (int)($_POST['order_index'] ?? 0),
                'status' => $_POST['status'] ?? 'active'
            ];
            
            $categoryModel = new Category();
            $categoryModel->create($data);
            
            $this->redirect('/admin/categories');
        }
        
        $this->view('admin/categories/create', ['title' => 'Add Category - Admin']);
    }

    public function delete($id) {
        $categoryModel = new Category();
        $categoryModel->delete($id);
        $this->redirect('/admin/categories');
    }
}
