<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$rootPass = 'Sjf#29@84nJsU';
$dbName = 'liontec2_news_DB';
$dbUser = 'liontec2_news_admin';
$dbPass = 'Dha#1D345jk#@2';

echo "1. Connecting to MySQL as root...\n";
$pdoRoot = new PDO("mysql:host=127.0.0.1;port=3306", 'root', $rootPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

echo "2. Creating database `$dbName` if not exists...\n";
$pdoRoot->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

echo "3. Creating user `$dbUser` and granting privileges...\n";
$pdoRoot->exec("CREATE USER IF NOT EXISTS '$dbUser'@'localhost' IDENTIFIED BY '$dbPass'");
$pdoRoot->exec("ALTER USER '$dbUser'@'localhost' IDENTIFIED BY '$dbPass'");
$pdoRoot->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUser'@'localhost'");

$pdoRoot->exec("CREATE USER IF NOT EXISTS '$dbUser'@'127.0.0.1' IDENTIFIED BY '$dbPass'");
$pdoRoot->exec("ALTER USER '$dbUser'@'127.0.0.1' IDENTIFIED BY '$dbPass'");
$pdoRoot->exec("GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUser'@'127.0.0.1'");

$pdoRoot->exec("FLUSH PRIVILEGES");
echo "User and Database ready!\n";

echo "4. Testing connection as `$dbUser`...\n";
$pdoUser = new PDO("mysql:host=127.0.0.1;port=3306;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
echo "Successfully connected to `$dbName` as `$dbUser`!\n";

