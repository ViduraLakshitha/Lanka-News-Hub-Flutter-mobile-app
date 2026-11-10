<?php
// Production-aware .env and environment loader
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (strpos($trimmed, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'"); // Strip whitespace and quotes
            if (getenv($name) === false && !array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

if (!function_exists('env')) {
    function env(string $key, $default = null) {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val === false || $val === null || $val === '') {
            return $default;
        }
        return match (strtolower((string)$val)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $val,
        };
    }
}

$envKeys = [
    'APP_NAME', 'APP_ENV', 'APP_DEBUG', 'APP_URL', 'APP_TIMEZONE',
    'DB_DRIVER', 'DB_PATH', 'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD',
    'SESSION_LIFETIME', 'SESSION_SECURE_COOKIE',
    'CACHE_DRIVER', 'REDIS_HOST', 'REDIS_PORT', 'REDIS_PASSWORD',
    'LOG_CHANNEL', 'LOG_PATH',
    'MAIL_MAILER', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_ENCRYPTION', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME'
];

foreach ($envKeys as $key) {
    if (getenv($key) !== false) {
        $_ENV[$key] = getenv($key);
        $_SERVER[$key] = getenv($key);
    }
}

// Timezone configuration (Asia/Colombo UTC+05:30)
define('APP_TIMEZONE', $_ENV['APP_TIMEZONE'] ?? 'Asia/Colombo');
date_default_timezone_set(APP_TIMEZONE);

// Database configuration
define('DB_DRIVER', $_ENV['DB_DRIVER'] ?? 'sqlite');
$sqlitePath = $_ENV['DB_PATH'] ?? 'storage/preview.sqlite';
if (!preg_match('/^[a-zA-Z]:[\\\\\/]|^[\/\\\\]/', $sqlitePath)) {
    $sqlitePath = dirname(__DIR__) . '/' . ltrim($sqlitePath, '/\\');
}
define('DB_PATH', $sqlitePath);

define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', (int)($_ENV['DB_PORT'] ?? 3306));
define('DB_NAME', $_ENV['DB_NAME'] ?? 'news_hub');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');

// Application & Environment configuration
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Lanka News Hub');

$detectedHost = $_SERVER['HTTP_HOST'] ?? null;
if ($detectedHost) {
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');
    $detectedUrl = ($isHttps ? 'https://' : 'http://') . $detectedHost;
} else {
    $detectedUrl = rtrim($_ENV['APP_URL'] ?? 'https://news.liontech.lk', '/');
}
define('APP_URL', $detectedUrl);
define('APP_ENV', strtolower($_ENV['APP_ENV'] ?? 'development'));
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));


// Cache configuration
define('CACHE_DRIVER', strtolower($_ENV['CACHE_DRIVER'] ?? 'file'));

// Session configuration
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 86400)); // Default 24 hours

// Error handling and logging directives
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    $logDir = dirname(__DIR__) . '/storage/logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0700, true);
    }
    ini_set('error_log', $logDir . '/error.log');
    error_reporting(E_ALL);
}
