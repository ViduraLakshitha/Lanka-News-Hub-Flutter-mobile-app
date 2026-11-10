<?php
namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = DB_HOST;
        $db = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASSWORD;
        $charset = 'utf8mb4';

        $port = DB_PORT;
        $dsn = DB_DRIVER === 'sqlite' ? 'sqlite:' . DB_PATH : "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false, // Important for secure prepared statements
        ];

        if (DB_DRIVER === 'mysql' && defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci";
        }

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
            if (DB_DRIVER === 'sqlite') {
                $this->pdo->exec('PRAGMA foreign_keys = ON');
                $this->pdo->exec('PRAGMA busy_timeout = 5000');
            } else {
                $this->pdo->exec("SET time_zone = '+05:30'");
            }
        } catch (PDOException $e) {
            if (DB_DRIVER === 'mysql' && file_exists(DB_PATH)) {
                try {
                    $this->pdo = new PDO('sqlite:' . DB_PATH, null, null, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                    $this->pdo->exec('PRAGMA foreign_keys = ON');
                    $this->pdo->exec('PRAGMA busy_timeout = 5000');
                    return;
                } catch (PDOException $sqle) {
                    // Fallthrough to standard error reporting
                }
            }
            Logger::error("Database connection failure: " . $e->getMessage(), ['driver' => DB_DRIVER]);
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
