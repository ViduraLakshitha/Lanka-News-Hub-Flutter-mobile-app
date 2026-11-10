<?php
namespace App\Middleware;

class AuthMiddleware {
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . APP_URL . "/admin/login");
            exit();
        }
    }
}
