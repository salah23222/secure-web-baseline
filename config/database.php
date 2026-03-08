<?php

declare(strict_types=1);

/**
 * Database configuration.
 *
 * Reads credentials from environment variables when available.
 * Falls back to development defaults for local use.
 *
 * For production:
 *   1. Copy .env.example to .env
 *   2. Fill in real values
 *   3. Ensure .env is in .gitignore (it is by default)
 */

// Load .env file if it exists and hasn't been loaded yet
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile) && !isset($_ENV['DB_HOST'])) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value);
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

return [
    'host'     => $_ENV['DB_HOST']     ?? '127.0.0.1',
    'port'     => (int) ($_ENV['DB_PORT']     ?? 3306),
    'dbname'   => $_ENV['DB_NAME']     ?? 'secure_web_baseline',
    'username' => $_ENV['DB_USERNAME'] ?? 'root',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset'  => 'utf8mb4',
];
