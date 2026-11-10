<?php
namespace App\Controllers;
use App\Core\Portal;
class AccountController extends Portal {
    public function login(): void {
        $error='';
        if ($this->post()) {
            $email=strtolower($this->text('email'));
            // Two persistent buckets: per IP and per account. A new session cannot reset throttling.
            $buckets=[hash('sha256','ip:'.($_SERVER['REMOTE_ADDR'] ?? '')),hash('sha256','email:'.$email)];
            $cutoff=date('Y-m-d H:i:s',time()-900);
            $this->run('DELETE FROM login_attempts WHERE attempted_at<?',[$cutoff]);
            $ipCount=$this->one('SELECT COUNT(*) AS n FROM login_attempts WHERE bucket=? AND attempted_at>=?',[$buckets[0],$cutoff])['n'];
            $accountCount=$this->one('SELECT COUNT(*) AS n FROM login_attempts WHERE bucket=? AND attempted_at>=?',[$buckets[1],$cutoff])['n'];
            if ($ipCount>=10 || $accountCount>=5) {
                \App\Core\Logger::security("Login rate limit triggered", ['email' => $email]);
                http_response_code(429); $error='Too many attempts. Try again in 15 minutes.';
            } else {
                $user=$this->one('SELECT * FROM users WHERE email=? AND status=?',[$email,'active']);
                $valid=password_verify((string)($_POST['password'] ?? ''), $user['password'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
                if ($user && $valid) {
                    session_regenerate_id(true); $_SESSION['user_id']=$user['id']; $_SESSION['csrf']=bin2hex(random_bytes(32));
                    $this->user=$user; $this->audit('Signed in','users',(int)$user['id']);
                    \App\Core\Logger::security("Successful sign-in", ['email' => $email, 'user_id' => $user['id']]);
                    $this->run('DELETE FROM login_attempts WHERE bucket=?',[$buckets[1]]);
                    $role=$this->one('SELECT slug FROM roles WHERE id=?',[$user['role_id']])['slug'];
                    $this->redirect($role==='member' ? '/account' : '/admin');
                }
                foreach ($buckets as $bucket) $this->save('login_attempts',['bucket'=>$bucket,'attempted_at'=>date('Y-m-d H:i:s')]);
                \App\Core\Logger::security("Failed sign-in attempt", ['email' => $email]);
                $error='Email or password is incorrect.';
            }
        }
        $this->page('auth',['title'=>'Welcome back','mode'=>'login','error'=>$error]);
    }
    public function register(): void {
        $error='';
        if ($this->post()) {
            $ipBucket = hash('sha256', 'register_ip:' . ($_SERVER['REMOTE_ADDR'] ?? ''));
            $cutoff = date('Y-m-d H:i:s', time() - 3600);
            $regCount = $this->one('SELECT COUNT(*) AS n FROM login_attempts WHERE bucket=? AND attempted_at>=?', [$ipBucket, $cutoff])['n'];
            if ($regCount >= 10) {
                \App\Core\Logger::security("Registration rate limit triggered", ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
                http_response_code(429);
                $error = 'Too many registration attempts from this network. Please try again later.';
            } else {
                $this->save('login_attempts', ['bucket' => $ipBucket, 'attempted_at' => date('Y-m-d H:i:s')]);
                $email=strtolower($this->text('email')); $name=$this->text('name',100); $password=(string)($_POST['password'] ?? '');
                if (!$name || !filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)<12 || strlen($password)>72) $error='Enter your name, a valid email and a password of 12–72 characters.';
                elseif ($this->one('SELECT id FROM users WHERE email=?',[$email])) $error='This email is already registered. Please sign in.';
                else {
                    $role=$this->one("SELECT id FROM roles WHERE slug='member'")['id'];
                    $id=$this->save('users',['name'=>$name,'email'=>$email,'password'=>password_hash($password,PASSWORD_DEFAULT),'role_id'=>$role,'status'=>'active']);
                    session_regenerate_id(true); $_SESSION['user_id']=$id; $_SESSION['csrf']=bin2hex(random_bytes(32));
                    $this->audit('Registered account', 'users', $id);
                    \App\Core\Logger::security("New member registered", ['email' => $email, 'user_id' => $id]);
                    flash('Your account is ready. Welcome to Lanka News Hub.'); $this->redirect('/account');
                }
            }
        }
        $this->page('auth',['title'=>'Join your community','mode'=>'register','error'=>$error]);
    }
    public function logout(): void {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            \App\Core\Logger::security("User signed out", ['user_id' => $userId]);
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
        $this->redirect('/');
    }
    public function dashboard(): void {
        $this->requireUser(); $error='';
        if ($this->post()) {
            $formType = (string)($_POST['form_type'] ?? '');

            if ($formType === 'password') {
                $currentPw = (string)($_POST['current_password'] ?? '');
                $password = (string)($_POST['password'] ?? '');
                $passwordConfirm = (string)($_POST['password_confirmation'] ?? '');

                if ($currentPw === '') {
                    $error = 'Please enter your current password.';
                } elseif (!password_verify($currentPw, $this->user['password'])) {
                    $error = 'Current password is incorrect. Please verify and try again.';
                } elseif ($password === '') {
                    $error = 'Please enter a new password.';
                } elseif (strlen($password) < 12 || strlen($password) > 72) {
                    $error = 'New password must contain between 12 and 72 characters.';
                } elseif ($password !== $passwordConfirm) {
                    $error = 'New password and confirm password do not match.';
                } else {
                    $this->save('users', ['password' => password_hash($password, PASSWORD_DEFAULT)], (int)$this->user['id']);
                    $this->audit('Updated password', 'users', (int)$this->user['id']);
                    \App\Core\Logger::security('Member changed password', ['user_id' => $this->user['id']]);
                    flash('Password updated successfully.');
                    $this->redirect('/account');
                }
            } elseif ($formType === 'profile') {
                $name = $this->text('name', 100);
                if (!$name) {
                    $error = 'Please enter your name.';
                } else {
                    $this->save('users', ['name' => $name], (int)$this->user['id']);
                    $this->audit('Updated profile', 'users', (int)$this->user['id']);
                    \App\Core\Logger::security('Member updated profile', ['user_id' => $this->user['id']]);
                    flash('Profile updated successfully.');
                    $this->redirect('/account');
                }
            } else {
                // Fallback for general POST requests without form_type
                $name = $this->text('name', 100);
                $password = (string)($_POST['password'] ?? '');
                $passwordConfirm = (string)($_POST['password_confirmation'] ?? '');

                if (!$name) {
                    $error = 'Please enter your name.';
                } elseif ($password) {
                    $currentPw = (string)($_POST['current_password'] ?? '');
                    if ($currentPw === '') {
                        $error = 'Please enter your current password.';
                    } elseif (!password_verify($currentPw, $this->user['password'])) {
                        $error = 'Current password is incorrect. Please verify and try again.';
                    } elseif (strlen($password) < 12 || strlen($password) > 72) {
                        $error = 'New password must contain between 12 and 72 characters.';
                    } elseif ($passwordConfirm !== '' && $password !== $passwordConfirm) {
                        $error = 'New password and confirm password do not match.';
                    }
                }

                if (!$error) {
                    $data = ['name' => $name];
                    if ($password) {
                        $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                    }
                    $this->save('users', $data, (int)$this->user['id']);
                    $this->audit($password ? 'Updated password and profile' : 'Updated profile', 'users', (int)$this->user['id']);
                    \App\Core\Logger::security($password ? 'Member changed password' : 'Member updated profile', ['user_id' => $this->user['id']]);
                    flash($password ? 'Password updated successfully.' : 'Profile updated successfully.');
                    $this->redirect('/account');
                }
            }
        }
        $listings=$this->rows('SELECT * FROM classifieds WHERE user_id=? ORDER BY id DESC',[$this->user['id']]);
        $favorites=$this->rows("SELECT c.* FROM classifieds c JOIN favorites f ON f.classified_id=c.id WHERE f.user_id=? AND c.status='approved' AND c.expires_at>? ORDER BY f.id DESC",[$this->user['id'],date('Y-m-d H:i:s')]);
        $this->page('account',['title'=>'My account','listings'=>$listings,'favorites'=>$favorites,'error'=>$error]);
    }
}
