<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Sends security-related HTTP headers including CSP with per-request nonce.
 */
class SecurityHeaders
{
    private static string $nonce = '';

    public static function send(): void
    {
        $nonce = self::generateNonce();

        header('X-Frame-Options: DENY');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');

        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}'",
            "style-src 'self' 'nonce-{$nonce}'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);

        header('Content-Security-Policy: ' . $csp);
    }

    public static function generateNonce(): string
    {
        if (self::$nonce === '') {
            self::$nonce = base64_encode(random_bytes(16));
        }
        return self::$nonce;
    }

    public static function getNonce(): string
    {
        return self::$nonce !== '' ? self::$nonce : self::generateNonce();
    }
}
