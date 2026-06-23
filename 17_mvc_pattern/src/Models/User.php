<?php

namespace App\Models;

class User
{
    private static array $users = [];
    private static int $nextId = 1;

    public static function all(): array
    {
        return array_values(self::$users);
    }

    public static function find(int $id): ?array
    {
        return self::$users[$id] ?? null;
    }

    public static function findByEmail(string $email): ?array
    {
        foreach (self::$users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public static function create(array $data): array
    {
        $id = self::$nextId++;
        $user = [
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'user',
            'bio' => $data['bio'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        self::$users[$id] = $user;
        return $user;
    }

    public static function update(int $id, array $data): ?array
    {
        if (!isset(self::$users[$id])) return null;

        self::$users[$id] = array_merge(self::$users[$id], $data);
        return self::$users[$id];
    }

    public static function delete(int $id): bool
    {
        if (!isset(self::$users[$id])) return false;
        unset(self::$users[$id]);
        return true;
    }

    public static function count(): int
    {
        return count(self::$users);
    }

    public static function seed(): void
    {
        self::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'password' => 'admin123',
            'role' => 'admin',
            'bio' => 'Fullstack developer',
        ]);
        self::create([
            'name' => 'Ani Wijaya',
            'email' => 'ani@test.com',
            'password' => 'user123',
            'role' => 'user',
            'bio' => 'UI/UX designer',
        ]);
        self::create([
            'name' => 'Citra Dewi',
            'email' => 'citra@test.com',
            'password' => 'user456',
            'role' => 'user',
            'bio' => 'Content writer',
        ]);
    }
}
