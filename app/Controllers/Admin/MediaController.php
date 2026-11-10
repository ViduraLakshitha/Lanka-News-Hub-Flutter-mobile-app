<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Media;

class MediaController extends Controller {
    
    public function __construct() {
        AuthMiddleware::check();
    }

    public function index() {
        $mediaModel = new Media();
        $mediaFiles = $mediaModel->allOrdered();
        
        $this->view('admin/media/index', [
            'title' => 'Media Library - Admin',
            'media' => $mediaFiles
        ]);
    }

    public function upload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
            $file = $_FILES['file'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB

            if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxSize) {
                // Ensure upload directory exists
                $uploadDir = dirname(dirname(dirname(__DIR__))) . '/public/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '-' . time() . '.' . $extension;
                $targetPath = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $mediaModel = new Media();
                    $mediaModel->create([
                        'uploader_id' => $_SESSION['user_id'],
                        'file_name' => $file['name'],
                        'file_path' => 'uploads/' . $filename,
                        'file_type' => $file['type']
                    ]);
                    
                    $_SESSION['success'] = "File uploaded successfully.";
                } else {
                    $_SESSION['error'] = "Failed to move uploaded file.";
                }
            } else {
                $_SESSION['error'] = "Invalid file type or size exceeded (Max 5MB).";
            }
            
            $this->redirect('/admin/media');
        }
    }

    public function delete($id) {
        $mediaModel = new Media();
        $media = $mediaModel->find($id);
        
        if ($media) {
            $filePath = dirname(dirname(dirname(__DIR__))) . '/public/' . $media['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $mediaModel->delete($id);
        }
        
        $this->redirect('/admin/media');
    }
}
