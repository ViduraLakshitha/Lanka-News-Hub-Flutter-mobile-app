<?php
$zip = new ZipArchive();
$zipPath = 'F:/news_page/cpanel_mobile_api_update.zip';

if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
    $zip->addFile('F:/news_page/app/Controllers/ApiController.php', 'app/Controllers/ApiController.php');
    $zip->addFile('F:/news_page/app/routes.php', 'app/routes.php');
    $zip->addFile('F:/news_page/public/index.php', 'public/index.php');
    $zip->addFile('F:/news_page/database/cpanel_clean_categories.sql', 'database/cpanel_clean_categories.sql');
    $zip->addFile('F:/news_page/database/cpanel_mobile_update.sql', 'database/cpanel_mobile_update.sql');
    $zip->addFile('F:/news_page/README_CPANEL_DEPLOY.txt', 'README_CPANEL_DEPLOY.txt');
    $zip->close();
    echo "Zip package created successfully at: $zipPath (Size: " . filesize($zipPath) . " bytes)\n";
} else {
    echo "Failed to create zip file.\n";
}

