<?php
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/app/bootstrap.php';
$root=dirname(__DIR__); $folder=$root.'/storage/backups';
if (!is_dir($folder)) mkdir($folder,0700,true);
$stamp=date('Ymd-His').'-'.bin2hex(random_bytes(3)); $path=$folder.'/news-'.$stamp;
$db=App\Core\Database::getInstance()->getConnection();
if (DB_DRIVER==='sqlite') {
    $db->exec('VACUUM INTO '.$db->quote($path.'.sqlite'));
    $database=$path.'.sqlite';
} else {
    $database=$path.'.sql'; $out=fopen($database,'xb');
    fwrite($out,"SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n");
    $db->exec('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ'); $db->beginTransaction();
    try {
        foreach ($db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $identifier='`'.str_replace('`','``',$table).'`';
            $schema=$db->query('SHOW CREATE TABLE '.$identifier)->fetch(PDO::FETCH_NUM)[1];
            fwrite($out,$schema.";\n");
            $rows=$db->query('SELECT * FROM '.$identifier);
            while ($row=$rows->fetch(PDO::FETCH_ASSOC)) {
                $columns=implode(',',array_map(fn($key)=>'`'.str_replace('`','``',$key).'`',array_keys($row)));
                $values=implode(',',array_map(fn($value)=>$value===null ? 'NULL' : $db->quote((string)$value),array_values($row)));
                fwrite($out,"INSERT INTO $identifier ($columns) VALUES ($values);\n");
            }
        }
        $db->commit(); fwrite($out,"SET FOREIGN_KEY_CHECKS=1;\n"); fclose($out);
    } catch (Throwable $e) { if ($db->inTransaction()) $db->rollBack(); fclose($out); throw $e; }
}
$zip=new ZipArchive(); if ($zip->open($path.'.zip',ZipArchive::CREATE|ZipArchive::EXCL)!==true) throw new RuntimeException('Unable to create backup archive.');
$zip->addFile($database,basename($database));
$uploadRoot=$root.'/public/uploads';
if (is_dir($uploadRoot)) foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploadRoot,FilesystemIterator::SKIP_DOTS)) as $file) {
    if ($file->isFile() && !$file->isLink()) $zip->addFile($file->getPathname(),'uploads/'.str_replace('\\','/',substr($file->getPathname(),strlen($uploadRoot)+1)));
}
$zip->addFromString('RESTORE.txt',"Restore the database into an EMPTY database and place uploads/ under public/. Do not overwrite a live installation without a separately verified backup. See README.md.\n");
if (!$zip->close()) throw new RuntimeException('Backup archive could not be finalized.');
App\Core\Logger::info("Scheduled backup created: " . basename($path) . ".zip", ['backup' => basename($path) . '.zip', 'database' => basename($database)]);
echo "Backup created: ".$path.".zip\nDatabase export: ".$database."\n";
