<?php
$host = 'news.liontech.lk';
$port = 3306;
$db = 'liontec2_news_DB';
$user = 'liontec2_news_admin';
$pass = 'Dha#1D345jk#@2';

echo "Testing remote MySQL connection to $host...\n";
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ]);
    echo "SUCCESS: Connected to remote MySQL!\n";
    $count = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    echo "Articles in remote DB: $count\n";
} catch (PDOException $e) {
    echo "Remote MySQL failed: " . $e->getMessage() . "\n";
}

