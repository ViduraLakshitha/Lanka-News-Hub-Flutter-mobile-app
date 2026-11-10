<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller {
    
    public function showLogin() {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/admin');
        }
        $this->view('admin/login');
    }

    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $userModel = new User();
            $user = $userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $_SESSION['error'] = 'Your account is inactive.';
                    $this->redirect('/admin/login');
                }

                // Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role_id'];
                
                $this->redirect('/admin');
            } else {
                $_SESSION['error'] = 'Invalid email or password.';
                $this->redirect('/admin/login');
            }
        }
    }

    public function logout() {
        session_destroy();
        $this->redirect('/admin/login');
    }
}
