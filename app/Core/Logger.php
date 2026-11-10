<?php
namespace App\Core;

class Logger {
    private static ?string $logPath = null;

    private static function getPath(): string {
        if (self::$logPath === null) {
            $dir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($dir)) {
                @mkdir($dir, 0700, true);
            }
            self::$logPath = $dir . '/app.log';
        }
        return self::$logPath;
    }

    public static function log(string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
        $userId = $_SESSION['user_id'] ?? 'guest';
        
        // Sanitize sensitive fields from context
        $filtered = self::sanitizeContext($context);
        $contextJson = !empty($filtered) ? ' ' . json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        
        $entry = sprintf("[%s] [%s] [ip:%s] [user:%s] %s%s\n", $timestamp, strtoupper($level), $ip, $userId, $message, $contextJson);
        
        @file_put_contents(self::getPath(), $entry, FILE_APPEND | LOCK_EX);

        // Also write errors to dedicated error.log
        if (in_array(strtolower($level), ['error', 'critical', 'emergency'], true)) {
            $errorPath = dirname(self::getPath()) . '/error.log';
            @file_put_contents($errorPath, $entry, FILE_APPEND | LOCK_EX);
        }
    }

    public static function info(string $message, array $context = []): void {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void {
        self::log('ERROR', $message, $context);
    }

    public static function security(string $message, array $context = []): void {
        self::log('SECURITY', $message, $context);
    }

    private static function sanitizeContext(array $context): array {
        $sensitive = ['password', 'current_password', '_token', 'csrf', 'secret', 'token', 'authorization'];
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $context[$key] = self::sanitizeContext($value);
            } elseif (in_array(strtolower((string)$key), $sensitive, true)) {
                $context[$key] = '***REDACTED***';
            }
        }
        return $context;
    }
}
