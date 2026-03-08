<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Secure session handler with fingerprinting, idle timeout, and regeneration.
 */
class Session
{
    private const SESSION_NAME   = 'SWB_SESSION';
    private const IDLE_TIMEOUT   = 1800;   // 30 minutes
    private const REGEN_INTERVAL = 900;    // 15 minutes

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
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();
        self::$started = true;

        self::preventHijacking();
        self::checkIdleTimeout();
        self::periodicRegenerate();
    }

    /**
     * Destroy current session completely and issue a fresh session ID.
     * Used on logout to prevent session fixation after re-login.
     */
    public static function destroy(): void
    {
        // Clear all session data
        $_SESSION = [];

        // Expire the session cookie immediately
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Strict',
            ]);
        }

        session_destroy();
        self::$started = false;

        // Start a brand-new session with a fresh ID so flash messages work
        // and no old session ID can be reused by an attacker.
        self::start();
        session_regenerate_id(true);
    }

    // ── Hijack detection ────────────────────────────────────────────

    private static function preventHijacking(): void
    {
        $fingerprint = self::generateFingerprint();

        if (!isset($_SESSION['_fingerprint'])) {
            $_SESSION['_fingerprint'] = $fingerprint;
            return;
        }

        if (!hash_equals($_SESSION['_fingerprint'], $fingerprint)) {
            self::forceExpire();
            header('Location: /login');
            exit;
        }
    }

    private static function generateFingerprint(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (str_contains($ip, ':')) {
            // IPv6 — use first 3 groups (/48 subnet)
            $groups = explode(':', $ip);
            $subnet = implode(':', array_slice($groups, 0, 3));
        } else {
            // IPv4 — use first 3 octets (/24 subnet)
            $parts  = explode('.', $ip);
            $subnet = implode('.', array_slice($parts, 0, 3));
        }

        return hash('sha256', $ua . $subnet);
    }

    // ── Idle timeout ─────────────────────────────────────────────────

    private static function checkIdleTimeout(): void
    {
        if (isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > self::IDLE_TIMEOUT) {
                self::forceExpire();
                header('Location: /login');
                exit;
            }
        }
        $_SESSION['_last_activity'] = time();
    }

    // ── Periodic regeneration ────────────────────────────────────────

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

    /**
     * Hard-expire the session without starting a new one.
     * Used internally for hijack/timeout detection.
     */
    private static function forceExpire(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 3600,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => 'Strict',
            ]);
        }

        session_destroy();
        self::$started = false;
    }

    // ── Simple key-value API ─────────────────────────────────────────

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

    // ── Flash messages ────────────────────────────────────────────────

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

    // ── Helpers ───────────────────────────────────────────────────────

    private static function isHttps(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}
