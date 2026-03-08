<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

/**
 * User model — CRUD operations against the `users` table.
 */
class User
{
    public static function findByEmail(string $email): ?array
    {
        return Database::getInstance()->fetch(
            'SELECT * FROM users WHERE email = ? LIMIT 1',
            [$email]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::getInstance()->fetch(
            'SELECT * FROM users WHERE id = ? LIMIT 1',
            [$id]
        );
    }

    public static function create(array $data): string
    {
        return Database::getInstance()->insert('users', [
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'role'          => $data['role'] ?? 'user',
            'created_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public static function all(): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT id, name, email, role, created_at FROM users ORDER BY id DESC'
        );
    }

    public static function updateRole(int $userId, string $role): int
    {
        return Database::getInstance()->update(
            'users',
            ['role' => $role],
            'id = ?',
            [$userId]
        );
    }

    /**
     * Update a user's password hash after a successful password reset.
     */
    public static function updatePassword(int $userId, string $passwordHash): int
    {
        return Database::getInstance()->update(
            'users',
            ['password_hash' => $passwordHash],
            'id = ?',
            [$userId]
        );
    }

    public static function delete(int $userId): int
    {
        return Database::getInstance()->delete(
            'users',
            'id = ?',
            [$userId]
        );
    }
}
