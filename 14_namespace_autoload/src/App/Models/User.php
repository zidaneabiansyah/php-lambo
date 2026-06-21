<?php

namespace App\Models;

class User
{
    private static array $users = [];

    public function __construct(
        private int $id,
        private string $name,
        private string $email,
        private string $role = 'user',
    ) {
        self::$users[$id] = $this;
    }

    public static function find(int $id): ?self
    {
        return self::$users[$id] ?? null;
    }

    public static function all(): array
    {
        return array_values(self::$users);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
        ];
    }
}
