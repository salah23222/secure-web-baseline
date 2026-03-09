<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH',  BASE_PATH . '/app');

// PSR-4 autoloader for tests
spl_autoload_register(function (string $class): void {
    $mappings = [
        'App\\'   => APP_PATH . '/',
        'Tests\\' => BASE_PATH . '/tests/',
    ];

    foreach ($mappings as $prefix => $baseDir) {
        if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
            continue;
        }
        $file = $baseDir . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
