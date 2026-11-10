<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = realpath(__DIR__ . $path);
if ($file && str_starts_with($file, __DIR__ . DIRECTORY_SEPARATOR) && is_file($file) && preg_match('/\.(css|js|svg|png|jpg|jpeg|gif|webp|ico|mp4|webm|pdf|woff2?)$/i', $file)) return false;
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
