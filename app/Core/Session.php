<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Secure session handler with fingerprinting, idle timeout, and regeneration.
 */
class Session
{
    private const SESSION_NAME = 'SWB_SESSION';
    private const IDLE_TIMEOUT = 1800;       // 30 minutes
    private const REGEN_INTERVAL = 900;      // 15 minutes

    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');

        if (self::isHttps()) {
            ini_set('session.cookie_secure', '1');
        }

        session_name(self::SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => self::isHttps(),
            'httponly'  => true,
            'samesite'  => 'Strict',
        ]);

        session_start();
        self::$started = true;

        self::preventHijacking();
        self::checkIdleTimeout();
        self::periodicRegenerate();
    }

    /**
     * Basic session hijack detection via browser + subnet fingerprint.
     */
    private static function preventHijacking(): void
    {
        $fingerprint = self::generateFingerprint();

        if (!isset($_SESSION['_fingerprint'])) {
            $_SESSION['_fingerprint'] = $fingerprint;
            return;
        }

        if (!hash_equals($_SESSION['_fingerprint'], $fingerprint)) {
            // AuditLogger may not be loaded yet at session start time,
            // so we use a deferred flag and log it after bootstrap completes.
            $_SESSION['_hijack_flag'] = true;
            self::destroy();
            header('Location: /login');
            exit;
        }
    }

    private static function generateFingerprint(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Use a subnet prefix so minor IP changes (mobile) don't break the session.
        // IPv4: use /24 (first 3 octets). IPv6: use /48 (first 3 groups).
        if (str_contains($ip, ':')) {
            $groups = explode(':', $ip);
            $subnet = implode(':', array_slice($groups, 0, 3));
        } else {
            $parts = explode('.', $ip);
            $subnet = implode('.', array_slice($parts, 0, 3));
        }

        return hash('sha256', $ua . $subnet);
    }

    private static function checkIdleTimeout(): void
    {
        if (isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > self::IDLE_TIMEOUT) {
                self::destroy();
                header('Location: /login');
                exit;
            }
        }
        $_SESSION['_last_activity'] = time();
    }

    private static function periodicRegenerate(): void
    {
        if (!isset($_SESSION['_created_at'])) {
            $_SESSION['_created_at'] = time();
            return;
        }

        if (time() - $_SESSION['_created_at'] > self::REGEN_INTERVAL) {
            session_regenerate_id(true);
            $_SESSION['_created_at'] = time();
        }
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly'  => $params['httponly'],
                'samesite'  => 'Strict',
            ]);
        }

        session_destroy();
        self::$started = false;
    }

    // ── Simple key-value API ────────────────────────────────────────

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    // ── Flash messages (one-time read) ──────────────────────────────

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    // ── Helpers ─────────────────────────────────────────────────────

    private static function isHttps(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}
