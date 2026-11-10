<?php
namespace App\Controllers;

use App\Core\Database;
use Throwable;

class HealthController {
    public function check(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $status = 'healthy';
        $httpCode = 200;
        $checks = [];

        // 1. Database Connectivity Check
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query('SELECT 1');
            $checks['database'] = [
                'status' => ($stmt && $stmt->fetchColumn() == 1) ? 'up' : 'down',
                'driver' => DB_DRIVER,
            ];
            if ($checks['database']['status'] !== 'up') {
                $status = 'degraded';
                $httpCode = 503;
            }
        } catch (Throwable $e) {
            $status = 'unhealthy';
            $httpCode = 503;
            $checks['database'] = [
                'status' => 'down',
                'error' => APP_DEBUG ? $e->getMessage() : 'Database connection failed',
            ];
        }

        // 2. Storage & Writable Directories Check
        $root = dirname(__DIR__, 2);
        $writableDirs = [
            'storage_logs' => $root . '/storage/logs',
            'storage_cache' => $root . '/storage/cache',
            'storage_sessions' => $root . '/storage/sessions',
            'public_uploads' => $root . '/public/uploads',
        ];

        $storageStatus = 'ok';
        $storageDetails = [];
        foreach ($writableDirs as $name => $path) {
            if (!is_dir($path)) {
                @mkdir($path, 0755, true);
            }
            $isWritable = is_writable($path);
            $storageDetails[$name] = $isWritable ? 'writable' : 'readonly_or_missing';
            if (!$isWritable) {
                $storageStatus = 'error';
                $status = 'degraded';
                $httpCode = 503;
            }
        }
        $checks['storage'] = [
            'status' => $storageStatus,
            'details' => $storageDetails,
        ];

        // 3. Disk Space Monitoring
        $freeSpace = @disk_free_space($root);
        $totalSpace = @disk_total_space($root);
        if ($freeSpace !== false && $totalSpace !== false && $totalSpace > 0) {
            $freePercent = round(($freeSpace / $totalSpace) * 100, 1);
            $checks['disk'] = [
                'free_mb' => round($freeSpace / (1024 * 1024), 1),
                'free_percent' => $freePercent,
                'status' => ($freePercent < 5) ? 'critical' : (($freePercent < 15) ? 'warning' : 'ok'),
            ];
            if ($checks['disk']['status'] === 'critical') {
                $status = 'degraded';
            }
        }

        // 4. Runtime & Cache Subsystem
        $checks['runtime'] = [
            'php_version' => PHP_VERSION,
            'sapi' => PHP_SAPI,
            'opcache_enabled' => function_exists('opcache_get_status') && !empty(opcache_get_status(false)['opcache_enabled']),
            'cache_driver' => defined('CACHE_DRIVER') ? CACHE_DRIVER : 'none',
        ];

        http_response_code($httpCode);
        echo json_encode([
            'status' => $status,
            'timestamp' => time(),
            'datetime' => date('c'),
            'app_env' => defined('APP_ENV') ? APP_ENV : 'unknown',
            'checks' => $checks,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
