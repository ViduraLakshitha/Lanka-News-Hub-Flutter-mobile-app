<?php
require_once __DIR__ . '/app/bootstrap.php';
echo "DB_DRIVER constant: " . (defined('DB_DRIVER') ? DB_DRIVER : 'not defined') . "\n";
echo "getenv('DB_DRIVER'): " . var_export(getenv('DB_DRIVER'), true) . "\n";
echo "_ENV['DB_DRIVER']: " . var_export($_ENV['DB_DRIVER'] ?? null, true) . "\n";
echo "_SERVER['DB_DRIVER']: " . var_export($_SERVER['DB_DRIVER'] ?? null, true) . "\n";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'not defined') . "\n";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'not defined') . "\n";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'not defined') . "\n";

