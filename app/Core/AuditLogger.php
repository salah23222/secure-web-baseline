<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Audit logger for security-relevant events.
 *
 * Writes structured JSON log entries to storage/audit.log.
 * Each entry is one JSON object per line (NDJSON format) for easy parsing.
 *
 * Events logged:
 *  - login_success, login_failed, logout
 *  - register_success, register_failed
 *  - account_locked, rate_limit_hit
 *  - csrf_failure, session_hijack_attempt
 */
class AuditLogger
{
    private const LOG_FILE    = BASE_PATH . '/storage/audit.log';
    private const LOG_DIR     = BASE_PATH . '/storage';
    private const MAX_LOG_SIZE = 10 * 1024 * 1024; // 10 MB before rotation

    // ── Public event methods ─────────────────────────────────────────

    public static function loginSuccess(string $email, int $userId): void
    {
        self::log('login_success', ['email' => $email, 'user_id' => $userId]);
    }

    public static function loginFailed(string $email, string $reason = ''): void
    {
        self::log('login_failed', ['email' => $email, 'reason' => $reason]);
    }

    public static function logout(int $userId, string $email): void
    {
        self::log('logout', ['user_id' => $userId, 'email' => $email]);
    }

    public static function registerSuccess(string $email, int $userId): void
    {
        self::log('register_success', ['email' => $email, 'user_id' => $userId]);
    }

    public static function registerFailed(string $email, string $reason = ''): void
    {
        self::log('register_failed', ['email' => $email, 'reason' => $reason]);
    }

    public static function accountLocked(string $email): void
    {
        self::log('account_locked', ['email' => $email]);
    }

    public static function rateLimitHit(string $action): void
    {
        self::log('rate_limit_hit', ['action' => $action]);
    }

    public static function csrfFailure(): void
    {
        self::log('csrf_failure', []);
    }

    public static function sessionHijackAttempt(): void
    {
        self::log('session_hijack_attempt', []);
    }

    // ── Core write ───────────────────────────────────────────────────

    private static function log(string $event, array $context): void
    {
        self::ensureStorageDir();
        self::rotateIfNeeded();

        $entry = [
            'timestamp' => date('c'),
            'event'     => $event,
            'ip'        => self::getIp(),
            'ua'        => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200),
            'context'   => $context,
        ];

        $line = json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n";
        file_put_contents(self::LOG_FILE, $line, FILE_APPEND | LOCK_EX);
    }

    private static function getIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private static function ensureStorageDir(): void
    {
        if (!is_dir(self::LOG_DIR)) {
            mkdir(self::LOG_DIR, 0700, true);
        }
    }

    private static function rotateIfNeeded(): void
    {
        if (file_exists(self::LOG_FILE) && filesize(self::LOG_FILE) > self::MAX_LOG_SIZE) {
            $rotated = self::LOG_FILE . '.' . date('Ymd_His');
            rename(self::LOG_FILE, $rotated);
        }
    }
}
