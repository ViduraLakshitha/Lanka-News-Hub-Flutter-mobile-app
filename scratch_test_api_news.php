<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/v1/news?limit=2';
$_SERVER['HTTP_HOST'] = '127.0.0.1:8080';

require_once __DIR__ . '/app/bootstrap.php';
$controller = new \App\Controllers\ApiController();
$controller->newsList();
