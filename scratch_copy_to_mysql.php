<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$sqlite = new PDO('sqlite:storage/preview.sqlite', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$mysql = new PDO("mysql:host=127.0.0.1;port=3306;dbname=liontec2_news_DB;charset=utf8mb4", 'liontec2_news_admin', 'Dha#1D345jk#@2', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$mysql->exec("SET FOREIGN_KEY_CHECKS = 0;");

$tables = [
    'roles',
    'users',
    'categories',
    'media',
    'articles',
    'advertisements',
    'settings',
    'activity_logs',
    'article_revisions',
    'classified_categories',
    'classifieds',
    'favorites',
    'payments',
    'menus',
    'api_tokens',
    'saved_articles',
    'device_tokens',
    'notification_preferences'
];

foreach ($tables as $table) {
    try {
        $rows = $sqlite->query("SELECT * FROM `$table`")->fetchAll();
        if (empty($rows)) {
            echo "Table $table has 0 rows in sqlite.\n";
            continue;
        }
        
        $mysql->exec("TRUNCATE TABLE `$table`");
        
        $first = $rows[0];
        $cols = array_keys($first);
        $colNames = implode('`, `', $cols);
        $placeholders = implode(', ', array_fill(0, count($cols), '?'));
        
        $stmt = $mysql->prepare("INSERT INTO `$table` (`$colNames`) VALUES ($placeholders)");
        
        $inserted = 0;
        foreach ($rows as $row) {
            $stmt->execute(array_values($row));
            $inserted++;
        }
        echo "Table `$table`: Copied $inserted rows to MySQL.\n";
    } catch (Throwable $e) {
        echo "Error copying `$table`: " . $e->getMessage() . "\n";
    }
}

$mysql->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "\nVerification of MySQL:\n";
$articleCount = $mysql->query("SELECT COUNT(*) FROM articles")->fetchColumn();
echo "MySQL articles count: $articleCount\n";
$catCount = $mysql->query("SELECT COUNT(*) FROM categories")->fetchColumn();
echo "MySQL categories count: $catCount\n";
$classifiedCount = $mysql->query("SELECT COUNT(*) FROM classifieds")->fetchColumn();
echo "MySQL classifieds count: $classifiedCount\n";

