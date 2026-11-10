<?php
$pdo = new PDO("mysql:host=127.0.0.1;port=3306", 'root', 'Sjf#29@84nJsU', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
echo "Scanning all MySQL databases for 'article' or 'news' tables...\n";
foreach ($dbs as $db) {
    if (in_array($db, ['information_schema', 'performance_schema', 'mysql', 'phpmyadmin'])) continue;
    try {
        $tables = $pdo->query("SHOW TABLES FROM `$db`")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $t) {
            if (stripos($t, 'article') !== false || stripos($t, 'news') !== false) {
                $cnt = $pdo->query("SELECT COUNT(*) FROM `$db`.`$t`")->fetchColumn();
                echo "Database [$db] -> Table [$t] (Rows: $cnt)\n";
            }
        }
    } catch (Throwable $e) {
        // ignore
    }
}
echo "Scan complete.\n";

