<?php
require_once dirname(__DIR__) . '/config/config.php';
spl_autoload_register(function ($class) {
    if (str_starts_with($class, 'App\\')) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (is_file($file)) require_once $file;
    }
});
require_once __DIR__ . '/functions.php';
