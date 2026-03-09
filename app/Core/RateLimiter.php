<?php

declare(strict_types=1);

namespace App\Core;

/**
 * File-based rate limiter and account lockout.
 *
 * Two independent mechanisms:
 *  1. IP-based rate limiting   — max N attempts per window per IP.
 *  2. Account lockout          — max N failed logins per email before lockout.
 *
 * Also provides purgeExpired() for scheduled cleanup (cron).
 */
class RateLimiter
{
    // ── IP Rate Limit ────────────────────────────────────────────────
    private const IP_MAX_ATTEMPTS = 10;
    private const IP_WINDOW       = 300;  // 5 minutes

    // ── Account Lockout ──────────────────────────────────────────────
    private const LOCK_MAX_ATTEMPTS = 5;
    private const LOCK_DURATION     = 900; // 15 minutes

    // ── Storage ──────────────────────────────────────────────────────
    private const STORAGE_DIR = BASE_PATH . '/storage/rate_limits';

    // ── IP Rate Limiting ─────────────────────────────────────────────

    public static function tooManyAttempts(string $action): bool
    {
        $data = self::read(self::ipKey($action));

        if ($data === null) {
            return false;
        }

        if (time() - $data['window_start'] > self::IP_WINDOW) {
            self::delete(self::ipKey($action));
            return false;
        }

        return $data['attempts'] >= self::IP_MAX_ATTEMPTS;
    }

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

    public static function clear(string $action): void
    {
        self::delete(self::ipKey($action));
    }

    public static function retryAfter(string $action): int
    {
        $data = self::read(self::ipKey($action));
        if ($data === null) {
            return 0;
        }

        return max(0, self::IP_WINDOW - (time() - $data['window_start']));
    }

    // ── Account Lockout ──────────────────────────────────────────────

    public static function recordFailedLogin(string $email): void
    {
        $key  = self::emailKey($email);
        $data = self::read($key) ?? ['attempts' => 0, 'locked_at' => null];

        if ($data['locked_at'] !== null && time() - $data['locked_at'] > self::LOCK_DURATION) {
            $data = ['attempts' => 0, 'locked_at' => null];
        }

        $data['attempts']++;

        if ($data['attempts'] >= self::LOCK_MAX_ATTEMPTS && $data['locked_at'] === null) {
            $data['locked_at'] = time();
        }

        self::write($key, $data);
    }

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

    public static function lockoutEndsIn(string $email): int
    {
        $data = self::read(self::emailKey($email));
        if ($data === null || $data['locked_at'] === null) {
            return 0;
        }

        return max(0, self::LOCK_DURATION - (time() - $data['locked_at']));
    }

    public static function clearFailedLogins(string $email): void
    {
        self::delete(self::emailKey($email));
    }

    // ── Scheduled Cleanup ────────────────────────────────────────────

    /**
     * Delete all expired rate-limit and lockout files.
     *
     * Call from a cron job, e.g.:
     *   * * * * * php /path/to/project/scripts/purge_rate_limits.php
     *
     * Or call manually from a CLI script.
     */
    public static function purgeExpired(): int
    {
        $dir = self::STORAGE_DIR;
        if (!is_dir($dir)) {
            return 0;
        }

        $deleted = 0;
        $now     = time();

        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $json = file_get_contents($file);
            if ($json === false) {
                continue;
            }

            $data = json_decode($json, true);
            if (!is_array($data)) {
                unlink($file);
                $deleted++;
                continue;
            }

            $expired = false;

            // IP window record
            if (isset($data['window_start'])) {
                $expired = ($now - $data['window_start']) > self::IP_WINDOW;
            }

            // Lockout record
            if (isset($data['locked_at'])) {
                if ($data['locked_at'] === null) {
                    // Not locked — expire if all attempts cleared
                    $expired = $data['attempts'] === 0;
                } else {
                    $expired = ($now - $data['locked_at']) > self::LOCK_DURATION;
                }
            }

            if ($expired) {
                unlink($file);
                $deleted++;
            }
        }

        return $deleted;
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
