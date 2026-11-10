<?php
/**
 * Automated Scheduled Task Runner (CLI Only)
 * 
 * Usage:
 *   php bin/cron.php            # Run routine maintenance
 *   php bin/cron.php --backup   # Run routine maintenance + backup
 *   php bin/cron.php --dry-run  # Simulate tasks without database modifications
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("CLI only\n");
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Logger;
use App\Core\Cache;

$options = getopt('', ['backup', 'dry-run', 'verbose']);
$dryRun = isset($options['dry-run']);
$runBackup = isset($options['backup']);
$now = date('Y-m-d H:i:s');
$today = date('Y-m-d');

echo "=== Lanka News Hub Scheduled Task Runner ===\n";
echo "Timestamp: {$now} (" . APP_TIMEZONE . ")\n";
if ($dryRun) {
    echo "Mode: DRY-RUN (No changes will be committed)\n";
}

$db = Database::getInstance()->getConnection();
$tasksCompleted = 0;

// 1. Transition Due Scheduled Stories to Published Status
try {
    $stmt = $db->prepare("SELECT id, title_en, title_si, title_ta FROM articles WHERE status = 'scheduled' AND published_at <= ?");
    $stmt->execute([$now]);
    $scheduledStories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($scheduledStories);

    if ($count > 0) {
        if (!$dryRun) {
            $updateStmt = $db->prepare("UPDATE articles SET status = 'published', updated_at = ? WHERE status = 'scheduled' AND published_at <= ?");
            $updateStmt->execute([$now, $now]);
            Cache::delete('portal_categories');
        }
        $msg = "Transitioned {$count} scheduled story(ies) to published status.";
        echo "[STORIES] {$msg}\n";
        Logger::info("Cron: " . $msg, ['count' => $count, 'stories' => array_column($scheduledStories, 'id')]);
    } else {
        echo "[STORIES] No scheduled stories due for publication.\n";
    }
    $tasksCompleted++;
} catch (Throwable $e) {
    echo "[ERROR] Story scheduling check failed: " . $e->getMessage() . "\n";
    Logger::error("Cron story scheduling failure: " . $e->getMessage());
}

// 2. Expire Overdue Classified Listings
try {
    $stmt = $db->prepare("SELECT id FROM classifieds WHERE status = 'approved' AND expires_at IS NOT NULL AND expires_at <= ?");
    $stmt->execute([$now]);
    $expiredClassifieds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($expiredClassifieds);

    if ($count > 0) {
        if (!$dryRun) {
            $updateStmt = $db->prepare("UPDATE classifieds SET status = 'expired', updated_at = ? WHERE status = 'approved' AND expires_at IS NOT NULL AND expires_at <= ?");
            $updateStmt->execute([$now, $now]);
        }
        $msg = "Expired {$count} overdue classified listing(s).";
        echo "[CLASSIFIEDS] {$msg}\n";
        Logger::info("Cron: " . $msg, ['count' => $count]);
    } else {
        echo "[CLASSIFIEDS] No classified listings due for expiry.\n";
    }
    $tasksCompleted++;
} catch (Throwable $e) {
    echo "[ERROR] Classifieds expiration check failed: " . $e->getMessage() . "\n";
    Logger::error("Cron classifieds expiration failure: " . $e->getMessage());
}

// 3. Deactivate Expired Website Advertisements
try {
    $stmt = $db->prepare("SELECT id FROM advertisements WHERE is_active = 1 AND end_date IS NOT NULL AND end_date < ?");
    $stmt->execute([$today]);
    $expiredAds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($expiredAds);

    if ($count > 0) {
        if (!$dryRun) {
            $updateStmt = $db->prepare("UPDATE advertisements SET is_active = 0 WHERE is_active = 1 AND end_date IS NOT NULL AND end_date < ?");
            $updateStmt->execute([$today]);
        }
        $msg = "Deactivated {$count} expired advertisement campaign(s).";
        echo "[ADVERTISEMENTS] {$msg}\n";
        Logger::info("Cron: " . $msg, ['count' => $count]);
    } else {
        echo "[ADVERTISEMENTS] All active advertisements are within their scheduled dates.\n";
    }
    $tasksCompleted++;
} catch (Throwable $e) {
    echo "[ERROR] Advertisement maintenance failed: " . $e->getMessage() . "\n";
    Logger::error("Cron advertisement maintenance failure: " . $e->getMessage());
}

// 4. Prune Stale Login Throttling Buckets (> 24 hours old)
try {
    $cutoff = date('Y-m-d H:i:s', time() - 86400);
    if (!$dryRun) {
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE attempted_at < ?");
        $stmt->execute([$cutoff]);
        $pruned = $stmt->rowCount();
        echo "[SECURITY] Pruned {$pruned} stale login throttling attempt(s).\n";
        if ($pruned > 0) {
            Logger::info("Cron: Pruned {$pruned} stale login attempts.", ['cutoff' => $cutoff]);
        }
    } else {
        echo "[SECURITY] Dry run: Skipping login attempt pruning.\n";
    }
    $tasksCompleted++;
} catch (Throwable $e) {
    echo "[ERROR] Login throttling cleanup failed: " . $e->getMessage() . "\n";
    Logger::error("Cron login throttling cleanup failure: " . $e->getMessage());
}

// 5. Prune Expired File Cache Entries
try {
    $cacheDir = dirname(__DIR__) . '/storage/cache';
    $prunedCacheFiles = 0;
    if (is_dir($cacheDir)) {
        foreach (glob($cacheDir . '/*.cache') as $file) {
            $raw = @file_get_contents($file);
            if ($raw !== false) {
                $payload = @unserialize($raw);
                if (is_array($payload) && isset($payload['expires']) && time() > $payload['expires']) {
                    if (!$dryRun) {
                        @unlink($file);
                    }
                    $prunedCacheFiles++;
                }
            }
        }
    }
    echo "[CACHE] Pruned {$prunedCacheFiles} expired cache file(s).\n";
    if ($prunedCacheFiles > 0) {
        Logger::info("Cron: Cleaned {$prunedCacheFiles} expired cache files.");
    }
    $tasksCompleted++;
} catch (Throwable $e) {
    echo "[ERROR] Cache cleanup failed: " . $e->getMessage() . "\n";
    Logger::error("Cron cache cleanup failure: " . $e->getMessage());
}

// 6. Optional Backup Trigger
if ($runBackup && !$dryRun) {
    echo "[BACKUP] Executing scheduled system backup...\n";
    try {
        $backupScript = __DIR__ . '/backup.php';
        if (file_exists($backupScript)) {
            $output = [];
            $code = 0;
            exec("php " . escapeshellarg($backupScript), $output, $code);
            if ($code === 0) {
                echo "[BACKUP] Backup completed successfully:\n" . implode("\n", $output) . "\n";
                Logger::info("Cron: Automated backup executed successfully.");
            } else {
                throw new RuntimeException("Backup failed with code {$code}: " . implode("\n", $output));
            }
        }
    } catch (Throwable $e) {
        echo "[ERROR] Backup failed: " . $e->getMessage() . "\n";
        Logger::error("Cron automated backup failure: " . $e->getMessage());
    }
}

Logger::info("Cron: Scheduled maintenance completed.", ['tasks_completed' => $tasksCompleted]);
echo "=== Scheduled Maintenance Complete ({$tasksCompleted} tasks evaluated) ===\n";
