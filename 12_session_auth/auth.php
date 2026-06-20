<?php

class Auth
{
    private const USER_KEY = '_auth_user';

    public static function attempt(string $email, string $password, array $users): bool
    {
        $user = self::findByEmail($email, $users);
        if (!$user) return false;

        if (!password_verify($password, $user['password'])) return false;

        Session::set(self::USER_KEY, [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'user',
        ]);

        Session::regenerate();
        return true;
    }

    public static function loginById(int $id, array $users): bool
    {
        $user = null;
        foreach ($users as $u) {
            if ($u['id'] === $id) {
                $user = $u;
                break;
            }
        }
        if (!$user) return false;

        Session::set(self::USER_KEY, [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'user',
        ]);

        Session::regenerate();
        return true;
    }

    public static function user(): ?array
    {
        return Session::get(self::USER_KEY);
    }

    public static function id(): ?int
    {
        return Session::get(self::USER_KEY)['id'] ?? null;
    }

    public static function check(): bool
    {
        return Session::has(self::USER_KEY);
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function logout(): void
    {
        Session::remove(self::USER_KEY);
        Session::regenerate();
    }

    public static function role(): ?string
    {
        return Session::get(self::USER_KEY)['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function can(string $permission): bool
    {
        $role = self::role();

        $permissions = [
            'admin' => ['*'],
            'user' => ['read', 'create'],
        ];

        $rolePerms = $permissions[$role] ?? [];
        return in_array('*', $rolePerms) || in_array($permission, $rolePerms);
    }

    public static function once(array $credentials, array $users): bool
    {
        $user = self::findByEmail($credentials['email'] ?? '', $users);
        if (!$user) return false;

        return password_verify($credentials['password'] ?? '', $user['password']);
    }

    private static function findByEmail(string $email, array $users): ?array
    {
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }
}

class Password
{
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public static function validate(string $password): array
    {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = 'Minimal 8 karakter';
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Harus mengandung huruf besar';
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Harus mengandung huruf kecil';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Harus mengandung angka';
        }
        return $errors;
    }

    public static function generate(int $length = 16): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        return $password;
    }
}
