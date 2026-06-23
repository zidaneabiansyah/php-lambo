<?php

namespace App\Models;

use App\Core\Database;

class User
{
    public static function all(): array
    {
        return Database::fetchAll("SELECT id, name, email, created_at FROM users ORDER BY id DESC");
    }

    public static function find(int $id): ?array
    {
        return Database::fetch("SELECT id, name, email, created_at FROM users WHERE id = ?", [$id]);
    }

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public static function create(array $data): string
    {
        return Database::insert('users', [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ]);
    }

    public static function authenticate(string $email, string $password): ?array
    {
        $user = self::findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return null;
        }

        return $user;
    }

    public static function count(): int
    {
        $result = Database::fetch("SELECT COUNT(*) as count FROM users");
        return (int) ($result['count'] ?? 0);
    }
}
