<?php
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$file = __DIR__ . '/public' . $path;

if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // let built-in server serve static file
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/public/index.php';

