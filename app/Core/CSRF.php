<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF protection with token generation, verification, and origin checking.
 */
class CSRF
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    public static function generate(): string
    {
        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(self::TOKEN_LENGTH)));
        }
        return Session::get(self::TOKEN_KEY);
    }

    public static function token(): string
    {
        return self::generate();
    }

    public static function field(): string
    {
        $token = self::generate();
        return '<input type="hidden" name="_csrf_token" value="'
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function metaTag(): string
    {
        $token = self::generate();
        return '<meta name="csrf-token" content="'
            . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Verify the submitted CSRF token. Also validates Origin/Referer headers.
     */
    public static function verify(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return true;
        }

        if (!self::validateOrigin()) {
            return false;
        }

        $submitted = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $stored = Session::get(self::TOKEN_KEY, '');

        if ($submitted === '' || $stored === '') {
            return false;
        }

        return hash_equals($stored, $submitted);
    }

    /** Regenerate the token (call after login or sensitive action). */
    public static function regenerate(): void
    {
        Session::set(self::TOKEN_KEY, bin2hex(random_bytes(self::TOKEN_LENGTH)));
    }

    /**
     * Check that Origin or Referer header matches the current host.
     * Allows requests with no Origin/Referer (direct form submissions).
     */
    private static function validateOrigin(): bool
    {
        $origin  = $_SERVER['HTTP_ORIGIN'] ?? null;
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if ($origin === null && $referer === null) {
            return true;
        }

        $allowedHost = $_SERVER['HTTP_HOST'] ?? 'localhost';

        if ($origin !== null) {
            $parsed = parse_url($origin);
            return ($parsed['host'] ?? '') === $allowedHost;
        }

        $parsed = parse_url($referer);
        return ($parsed['host'] ?? '') === $allowedHost;
    }
}
