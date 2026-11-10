<?php
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/app/bootstrap.php';
$email = $argv[1] ?? ''; $name = $argv[2] ?? 'Administrator';
$password = getenv('NEWS_ADMIN_PASSWORD') ?: '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) exit("Usage: set NEWS_ADMIN_PASSWORD (12+ characters), then php bin/admin.php email 'Name'\n");
$db=App\Core\Database::getInstance()->getConnection();
$s=$db->prepare('SELECT id FROM users WHERE email=?'); $s->execute([$email]);
if ($s->fetch()) exit("Account already exists; no changes made.\n");
$role=$db->query("SELECT id FROM roles WHERE slug='super_admin'")->fetchColumn();
$db->prepare('INSERT INTO users (name,email,password,role_id,status) VALUES (?,?,?,?,?)')->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$role,'active']);
echo "Administrator created.\n";
