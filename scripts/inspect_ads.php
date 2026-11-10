<?php
putenv('DB_DRIVER=sqlite');
putenv('DB_PATH=' . __DIR__ . '/../storage/preview.sqlite');
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_PATH'] = __DIR__ . '/../storage/preview.sqlite';

require __DIR__ . '/../app/bootstrap.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();

$total = $db->query('SELECT COUNT(*) FROM advertisements')->fetchColumn();
$active = $db->query('SELECT COUNT(*) FROM advertisements WHERE is_active=1')->fetchColumn();
echo "Total ads in SQLite: $total, Active: $active\n";

$rows = $db->query('SELECT id, position, title, image_path, ad_format, priority, is_active FROM advertisements ORDER BY position, id')->fetchAll();
foreach ($rows as $r) {
    $img = $r['image_path'] ?? '(null)';
    echo sprintf("#%03d [%-20s] format:%-7s prio:%-2d active:%d | %s | %s\n", 
        $r['id'], $r['position'], $r['ad_format'], (int)$r['priority'], (int)$r['is_active'], $img, $r['title']
    );
}
