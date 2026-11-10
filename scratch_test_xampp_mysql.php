<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', 'Sjf#29@84nJsU', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "SUCCESS: Connected to local MySQL as root!\n";
    $dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Databases found:\n" . implode("\n", $dbs) . "\n";
    
    // Check if liontec2_news_DB exists or similar
    foreach ($dbs as $db) {
        if (stripos($db, 'news') !== false || stripos($db, 'lion') !== false) {
            echo "Found matching database: $db\n";
            $tables = $pdo->query("SHOW TABLES FROM `$db`")->fetchAll(PDO::FETCH_COLUMN);
            echo "Tables in $db:\n" . implode(", ", $tables) . "\n";
            $newsCount = $pdo->query("SELECT COUNT(*) FROM `$db`.news_articles")->fetchColumn();
            echo "Article count in $db: $newsCount\n";
        }
    }
} catch (PDOException $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}

