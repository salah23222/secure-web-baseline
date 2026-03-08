<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Secure Web Baseline — Bootstrap
|--------------------------------------------------------------------------
| Initializes autoloading, session, security headers, CSRF, and helpers.
*/

use App\Core\SecurityHeaders;
use App\Core\Session;
use App\Core\CSRF;
use App\Core\AuditLogger;

/*
|--------------------------------------------------------------------------
| Path Constants
|--------------------------------------------------------------------------
*/
define('BASE_PATH', dirname(__DIR__, 2));
define('APP_PATH', dirname(__DIR__));

/*
|--------------------------------------------------------------------------
| Error Reporting
|--------------------------------------------------------------------------
*/
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Remove PHP version fingerprint as early as possible
header_remove('X-Powered-By');
ini_set('expose_php', '0');

/*
|--------------------------------------------------------------------------
| Autoload (PSR-4 style)
|--------------------------------------------------------------------------
*/
spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = APP_PATH . '/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

/*
|--------------------------------------------------------------------------
| Ensure storage directory exists and is protected
|--------------------------------------------------------------------------
*/
$storageDir = BASE_PATH . '/storage';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0700, true);
}
$htaccessPath = $storageDir . '/.htaccess';
if (!file_exists($htaccessPath)) {
    file_put_contents($htaccessPath, "Require all denied\n");
}

/*
|--------------------------------------------------------------------------
| Start Secure Session
|--------------------------------------------------------------------------
*/
Session::start();

/*
|--------------------------------------------------------------------------
| Security Headers (generates per-request CSP nonce)
|--------------------------------------------------------------------------
*/
SecurityHeaders::send();

/*
|--------------------------------------------------------------------------
| CSRF — Auto-verify on POST, init token
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRF::verify()) {
        AuditLogger::csrfFailure();
        http_response_code(403);
        if (file_exists(APP_PATH . '/Views/errors/403.php')) {
            require APP_PATH . '/Views/errors/403.php';
        } else {
            echo '403 Forbidden — CSRF verification failed.';
        }
        exit;
    }
}

CSRF::generate();

/*
|--------------------------------------------------------------------------
| Global Helpers
|--------------------------------------------------------------------------
*/

/** Render a view with extracted data. */
function view(string $name, array $data = []): void
{
    if (str_contains($name, '..') || str_contains($name, "\0")) {
        throw new RuntimeException('Invalid view name.');
    }
    extract($data, EXTR_SKIP);
    $__file = APP_PATH . '/Views/' . $name . '.php';
    if (!file_exists($__file)) {
        throw new RuntimeException("View [{$name}] not found.");
    }
    require $__file;
}

/** HTML-escape for safe output. */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Current CSP nonce for inline script/style tags. */
function nonce(): string
{
    return SecurityHeaders::getNonce();
}

/** Hidden CSRF input field. */
function csrf_field(): string
{
    return CSRF::field();
}

/** Safe redirect to an internal path. Prevents open redirect. */
function redirect(string $path): never
{
    $path = '/' . ltrim($path, '/');
    header('Location: ' . $path);
    exit;
}

/** Get a flash message (one-time read). */
function flash_error(): ?string
{
    return Session::getFlash('error');
}

function flash_success(): ?string
{
    return Session::getFlash('success');
}

/** Retrieve old form input after validation failure. */
function old(string $field, string $default = ''): string
{
    return e(Session::getFlash('old_' . $field) ?? $default);
}

/** Check if a user session is active. */
function is_logged_in(): bool
{
    return Session::has('user_id');
}
