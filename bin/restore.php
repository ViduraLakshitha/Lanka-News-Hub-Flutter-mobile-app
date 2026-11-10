<?php
// Automated Database & Media Restore Utility for Lanka News Hub
// Run only from CLI in staging, maintenance, or disaster recovery mode.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit("CLI execution only.\n");
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

$backupFile = $argv[1] ?? null;
$force = in_array('--force', $argv, true);

if (!$backupFile || !is_file($backupFile)) {
    echo "Usage: php bin/restore.php <path/to/backup.zip> [--force]\n";
    echo "Example: php bin/restore.php storage/backups/news-20261005-020000.zip\n";
    exit(1);
}

$root = dirname(__DIR__);
$zip = new ZipArchive();
$openResult = $zip->open($backupFile, ZipArchive::CHECKCONS);
if ($openResult !== true) {
    echo "ERROR: Backup archive is corrupted or unreadable (code: $openResult).\n";
    exit(1);
}

echo "======================================================================\n";
echo "LANKA NEWS HUB - DISASTER RECOVERY & BACKUP RESTORE\n";
echo "Archive: " . basename($backupFile) . " (" . round(filesize($backupFile) / (1024 * 1024), 2) . " MB)\n";
echo "Database Driver: " . DB_DRIVER . "\n";
echo "Target Root: $root\n";
echo "======================================================================\n";

if (!$force) {
    echo "WARNING: Restoring will overwrite the current database and public uploads.\n";
    echo "Type 'RESTORE' and press enter to proceed: ";
    $confirmation = trim(fgets(STDIN));
    if ($confirmation !== 'RESTORE') {
        echo "Restore aborted by user.\n";
        $zip->close();
        exit(0);
    }
}

$tempExtract = $root . '/storage/restore_temp_' . time();
if (!is_dir($tempExtract)) {
    mkdir($tempExtract, 0700, true);
}

try {
    echo "\n[1/4] Extracting backup archive contents...\n";
    if (!$zip->extractTo($tempExtract)) {
        throw new RuntimeException("Failed to extract backup archive to temporary directory.");
    }
    $zip->close();

    // 2. Identify database dump file
    echo "[2/4] Restoring database schema and records...\n";
    $db = \App\Core\Database::getInstance()->getConnection();

    if (DB_DRIVER === 'sqlite') {
        $sqliteFiles = glob($tempExtract . '/*.sqlite');
        if (empty($sqliteFiles)) {
            throw new RuntimeException("No .sqlite database dump found in backup archive.");
        }
        $sourceDb = $sqliteFiles[0];
        $targetDb = DB_PATH;
        // Close PDO connection by resetting instance
        unset($db);
        if (!copy($sourceDb, $targetDb)) {
            throw new RuntimeException("Failed to restore SQLite database to $targetDb.");
        }
        echo "  ✓ Restored SQLite database from " . basename($sourceDb) . "\n";
    } else {
        $sqlFiles = glob($tempExtract . '/*.sql');
        if (empty($sqlFiles)) {
            throw new RuntimeException("No .sql database dump found in backup archive.");
        }
        $sqlFile = $sqlFiles[0];
        echo "  Executing SQL script: " . basename($sqlFile) . "...\n";
        $sql = file_get_contents($sqlFile);
        $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $db->exec($sql);
        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
        echo "  ✓ Executed MySQL restore successfully.\n";
    }

    // 3. Restore uploaded media
    echo "[3/4] Restoring uploaded media files to public/uploads/...\n";
    $extractedUploads = $tempExtract . '/uploads';
    $targetUploads = $root . '/public/uploads';
    if (is_dir($extractedUploads)) {
        if (!is_dir($targetUploads)) {
            mkdir($targetUploads, 0755, true);
        }
        $count = 0;
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extractedUploads, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            $subPath = substr($item->getPathname(), strlen($extractedUploads) + 1);
            $destPath = $targetUploads . DIRECTORY_SEPARATOR . $subPath;
            if ($item->isDir()) {
                if (!is_dir($destPath)) mkdir($destPath, 0755, true);
            } else {
                $parent = dirname($destPath);
                if (!is_dir($parent)) mkdir($parent, 0755, true);
                if (copy($item->getPathname(), $destPath)) {
                    $count++;
                }
            }
        }
        echo "  ✓ Restored $count media files to public/uploads/.\n";
    } else {
        echo "  No uploaded media folder found in backup.\n";
    }

    // 4. Invalidate caches
    echo "[4/4] Purging application cache...\n";
    \App\Core\Cache::flush();
    echo "  ✓ Application object and query caches flushed.\n";

    \App\Core\Logger::info("Database and media restored from backup: " . basename($backupFile));
    echo "\n======================================================================\n";
    echo "SUCCESS: Platform restored cleanly and ready for traffic.\n";
    echo "======================================================================\n";
} catch (\Throwable $e) {
    echo "\nERROR during restore: " . $e->getMessage() . "\n";
    exit(1);
} finally {
    // Cleanup temporary extract directory
    if (is_dir($tempExtract)) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($tempExtract, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            @$todo($fileinfo->getRealPath());
        }
        @rmdir($tempExtract);
    }
}
