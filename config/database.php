<?php

declare(strict_types=1);

/**
 * Database configuration.
 *
 * For production, move credentials to environment variables or a .env file
 * and load them here instead of hard-coding values.
 */
return [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'dbname'   => 'secure_web_baseline',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
