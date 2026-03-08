<?php

declare(strict_types=1);

namespace App\Core;

/**
 * File-based rate limiter and account lockout.
 * No external dependencies — uses PHP session + server-side file storage.
 *
 * Two independent mechanisms:
 *  1. IP-based rate limiting   — max N attempts per window per IP.
 *  2. Account lockout          — max N failed logins per email before lockout.
 */
class RateLimiter
{
    // ── IP Rate Limit ────────────────────────────────────────────────
    private const IP_MAX_ATTEMPTS  = 10;    // max attempts
    private const IP_WINDOW        = 300;   // per 5-minute window

    // ── Account Lockout ──────────────────────────────────────────────
    private const LOCK_MAX_ATTEMPTS = 5;    // failed logins before lockout
    private const LOCK_DURATION     = 900;  // 15 minutes lockout

    // ── Storage ──────────────────────────────────────────────────────
    private const STORAGE_DIR = BASE_PATH . '/storage/rate_limits';

    // ── IP Rate Limiting ─────────────────────────────────────────────

    /**
     * Check if the current IP has exceeded the rate limit for a given action.
     */
    public static function tooManyAttempts(string $action): bool
    {
        $key  = self::ipKey($action);
        $data = self::read($key);

        if ($data === null) {
            return false;
        }

        // Reset if window has passed
        if (time() - $data['window_start'] > self::IP_WINDOW) {
            self::delete($key);
            return false;
        }

        return $data['attempts'] >= self::IP_MAX_ATTEMPTS;
    }

    /**
     * Increment the attempt counter for the current IP + action.
     */
    public static function hit(string $action): void
    {
        $key  = self::ipKey($action);
        $data = self::read($key);

        if ($data === null || time() - $data['window_start'] > self::IP_WINDOW) {
            $data = ['attempts' => 0, 'window_start' => time()];
        }

        $data['attempts']++;
        self::write($key, $data);
    }

    /**
     * Clear rate limit for the current IP + action (on successful login).
     */
    public static function clear(string $action): void
    {
        self::delete(self::ipKey($action));
    }

    /**
     * Seconds remaining in the current rate-limit window.
     */
    public static function retryAfter(string $action): int
    {
        $data = self::read(self::ipKey($action));
        if ($data === null) {
            return 0;
        }
        $remaining = self::IP_WINDOW - (time() - $data['window_start']);
        return max(0, $remaining);
    }

    // ── Account Lockout ──────────────────────────────────────────────

    /**
     * Record a failed login attempt for a given email.
     */
    public static function recordFailedLogin(string $email): void
    {
        $key  = self::emailKey($email);
        $data = self::read($key) ?? ['attempts' => 0, 'locked_at' => null];

        // Reset if a previous lockout has expired
        if ($data['locked_at'] !== null && time() - $data['locked_at'] > self::LOCK_DURATION) {
            $data = ['attempts' => 0, 'locked_at' => null];
        }

        $data['attempts']++;

        if ($data['attempts'] >= self::LOCK_MAX_ATTEMPTS && $data['locked_at'] === null) {
            $data['locked_at'] = time();
        }

        self::write($key, $data);
    }

    /**
     * Check if an account is currently locked out.
     */
    public static function isAccountLocked(string $email): bool
    {
        $data = self::read(self::emailKey($email));
        if ($data === null || $data['locked_at'] === null) {
            return false;
        }

        if (time() - $data['locked_at'] > self::LOCK_DURATION) {
            self::delete(self::emailKey($email));
            return false;
        }

        return true;
    }

    /**
     * Seconds remaining on the account lockout.
     */
    public static function lockoutEndsIn(string $email): int
    {
        $data = self::read(self::emailKey($email));
        if ($data === null || $data['locked_at'] === null) {
            return 0;
        }
        $remaining = self::LOCK_DURATION - (time() - $data['locked_at']);
        return max(0, $remaining);
    }

    /**
     * Clear failed login counter on successful authentication.
     */
    public static function clearFailedLogins(string $email): void
    {
        self::delete(self::emailKey($email));
    }

    // ── Storage helpers ──────────────────────────────────────────────

    private static function ipKey(string $action): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        return 'ip_' . $action . '_' . hash('sha256', $ip);
    }

    private static function emailKey(string $email): string
    {
        return 'email_' . hash('sha256', strtolower(trim($email)));
    }

    private static function filePath(string $key): string
    {
        self::ensureStorageDir();
        return self::STORAGE_DIR . '/' . $key . '.json';
    }

    private static function read(string $key): ?array
    {
        $path = self::filePath($key);
        if (!file_exists($path)) {
            return null;
        }
        $json = file_get_contents($path);
        if ($json === false) {
            return null;
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    private static function write(string $key, array $data): void
    {
        file_put_contents(self::filePath($key), json_encode($data), LOCK_EX);
    }

    private static function delete(string $key): void
    {
        $path = self::filePath($key);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    private static function ensureStorageDir(): void
    {
        if (!is_dir(self::STORAGE_DIR)) {
            mkdir(self::STORAGE_DIR, 0700, true);
        }
    }
}
