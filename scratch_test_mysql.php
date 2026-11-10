<?php
$host = '127.0.0.1';
$port = 3306;
$db = 'liontec2_news_DB';
$user = 'liontec2_news_admin';
$pass = 'Dha#1D345jk#@2';

echo "Attempting MySQL connection to $host:$port db=$db user=$user...\n";
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "SUCCESS: Connected to MySQL database!\n";
} catch (PDOException $e) {
    echo "MySQL connection failed: " . $e->getMessage() . "\n";
}

// Let's also test if MySQL root with no password works (standard local XAMPP)
echo "\nAttempting MySQL connection to 127.0.0.1 as root (no password)...\n";
try {
    $pdoRoot = new PDO("mysql:host=127.0.0.1;port=3306", 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "SUCCESS: Connected to local MySQL as root!\n";
    $dbs = $pdoRoot->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Available databases on local MySQL:\n" . implode("\n", $dbs) . "\n";
} catch (PDOException $e) {
    echo "Root connection failed: " . $e->getMessage() . "\n";
}

