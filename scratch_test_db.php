<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/app/bootstrap.php';
$pdo = \App\Core\Database::getInstance()->getConnection();
echo "Active driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
echo "Articles count: " . $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn() . "\n";
echo "Categories count: " . $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() . "\n";
echo "Classifieds count: " . $pdo->query("SELECT COUNT(*) FROM classifieds")->fetchColumn() . "\n";
$latest = $pdo->query("SELECT id, title_en, title_si, title_ta, status FROM articles ORDER BY id DESC LIMIT 3")->fetchAll();
print_r($latest);
