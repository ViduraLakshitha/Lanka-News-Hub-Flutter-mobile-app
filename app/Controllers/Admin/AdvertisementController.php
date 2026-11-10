<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Advertisement;

class AdvertisementController extends Controller {
    
    public function __construct() {
        AuthMiddleware::check();
    }

    public function index() {
        $adModel = new Advertisement();
        $ads = $adModel->allOrdered();
        
        $this->view('admin/advertisements/index', [
            'title' => 'Advertisement Management - Admin',
            'ads' => $ads
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imagePath = !empty($_POST['image_path']) ? trim($_POST['image_path']) : null;

            // Handle Direct Device File Upload
            if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['banner_file'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
                $maxSize = 8 * 1024 * 1024; // 8MB

                if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                    $uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/uploads/ads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'ad-' . uniqid() . '-' . time() . '.' . $extension;
                    $targetPath = $uploadDir . $filename;

                    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                        $imagePath = 'uploads/ads/' . $filename;
                    }
                }
            }

            $data = [
                'title' => trim($_POST['title'] ?? ''),
                'position' => trim($_POST['position'] ?? 'header_728x90'),
                'ad_code' => !empty($_POST['ad_code']) ? trim($_POST['ad_code']) : null,
                'image_path' => $imagePath,
                'url' => !empty($_POST['url']) ? trim($_POST['url']) : null,
                'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
                'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            $adModel = new Advertisement();
            $adModel->create($data);
            
            $this->redirect('/admin/advertisements');
        }
        
        $this->view('admin/advertisements/create', [
            'title' => 'Create Advertisement - Admin'
        ]);
    }

    public function delete($id) {
        $adModel = new Advertisement();
        $ad = $adModel->find($id);

        if ($ad && !empty($ad['image_path']) && strpos($ad['image_path'], 'http') !== 0) {
            $filePath = dirname(dirname(dirname(__DIR__))) . '/public/' . $ad['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $adModel->delete($id);
        $this->redirect('/admin/advertisements');
    }
}
