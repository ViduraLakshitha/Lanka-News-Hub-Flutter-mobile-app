<?php
namespace App\Core;

class Controller {
    protected function view($viewPath, $data = []) {
        extract($data);
        
        $fullPath = dirname(__DIR__) . '/Views/' . $viewPath . '.php';
        
        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            die("View $viewPath not found.");
        }
    }

    protected function redirect($url) {
        // Handle absolute or relative paths
        if (strpos($url, 'http') === 0) {
            header("Location: " . $url);
        } else {
            // Trim leading slash
            $url = ltrim($url, '/');
            header("Location: " . APP_URL . "/" . $url);
        }
        exit();
    }
}
