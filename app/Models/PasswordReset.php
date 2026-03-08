<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * Password reset token model.
 *
 * Flow:
 *  1. create()     — generate a one-time token, store its hash, return the raw token.
 *  2. findValid()  — look up a non-expired, unused token by email + hash.
 *  3. markUsed()   — mark the token as consumed after password change.
 *  4. purgeExpired()— housekeeping: delete old rows.
 */
class PasswordReset
{
    private const EXPIRY_MINUTES = 60; // Token valid for 60 minutes

    /**
     * Create a new reset token for the given email.
     * Returns the raw (unhashed) token to be sent by email.
     */
    public static function create(string $email): string
    {
        // Invalidate any existing unused tokens for this email
        Database::getInstance()->query(
            'UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0',
            [$email]
        );

        $rawToken  = bin2hex(random_bytes(32)); // 64-char hex string
        $tokenHash = hash('sha256', $rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + self::EXPIRY_MINUTES * 60);

        Database::getInstance()->insert('password_resets', [
            'email'      => $email,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'used'       => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $rawToken;
    }

    /**
     * Find a valid (unexpired, unused) reset record for the given email + raw token.
     * Returns the row or null.
     */
    public static function findValid(string $email, string $rawToken): ?array
    {
        $tokenHash = hash('sha256', $rawToken);

        return Database::getInstance()->fetch(
            'SELECT * FROM password_resets
              WHERE email      = ?
                AND token_hash = ?
                AND used       = 0
                AND expires_at > NOW()
              LIMIT 1',
            [$email, $tokenHash]
        );
    }

    /**
     * Mark a token row as used so it cannot be replayed.
     */
    public static function markUsed(int $id): void
    {
        Database::getInstance()->update(
            'password_resets',
            ['used' => 1],
            'id = ?',
            [$id]
        );
    }

    /**
     * Delete all expired or used tokens — call from a cron or on each reset request.
     */
    public static function purgeExpired(): void
    {
        Database::getInstance()->query(
            'DELETE FROM password_resets WHERE expires_at < NOW() OR used = 1'
        );
    }
}
