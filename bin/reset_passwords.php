<?php
if (PHP_SAPI !== 'cli') exit;
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=' . dirname(__DIR__) . '/storage/preview.sqlite');
require_once dirname(__DIR__) . '/app/bootstrap.php';
$db = App\Core\Database::getInstance()->getConnection();
$hash = password_hash('MemberPassword123!', PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE users SET password = ?");
$stmt->execute([$hash]);
echo "Reset " . $stmt->rowCount() . " users with password 'MemberPassword123!'.\n";
