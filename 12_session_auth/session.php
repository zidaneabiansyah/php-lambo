<?php

class Session
{
    private const FLASH_KEY = '_flash';
    private const FLASH_NEW = '_flash_new';
    private const OLD_KEY = '_old';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function id(): string
    {
        return session_id();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = self::get($key, $default);
        self::remove($key);
        return $value;
    }

    public static function all(): array
    {
        return $_SESSION;
    }

    public static function clear(): void
    {
        $_SESSION = [];
    }

    public static function destroy(): void
    {
        self::clear();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION[self::FLASH_NEW][$key] = $value;
            return null;
        }

        $stored = $_SESSION[self::FLASH_KEY][$key] ?? null;
        unset($_SESSION[self::FLASH_KEY][$key]);
        return $stored;
    }

    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION[self::FLASH_KEY][$key]);
    }

    public static function flashAll(): array
    {
        $flashes = $_SESSION[self::FLASH_KEY] ?? [];
        unset($_SESSION[self::FLASH_KEY]);
        return $flashes;
    }

    public static function reflash(): void
    {
        if (isset($_SESSION[self::FLASH_KEY])) {
            foreach ($_SESSION[self::FLASH_KEY] as $key => $value) {
                $_SESSION[self::FLASH_NEW][$key] = $value;
            }
        }
    }

    public static function keep(string ...$keys): void
    {
        foreach ($keys as $key) {
            if (isset($_SESSION[self::FLASH_KEY][$key])) {
                $_SESSION[self::FLASH_NEW][$key] = $_SESSION[self::FLASH_KEY][$key];
            }
        }
    }

    public static function ageFlashData(): void
    {
        $_SESSION[self::FLASH_KEY] = $_SESSION[self::FLASH_NEW] ?? [];
        unset($_SESSION[self::FLASH_NEW]);
    }

    public static function old(string $key, mixed $default = null): mixed
    {
        return $_SESSION[self::OLD_KEY][$key] ?? $default;
    }

    public static function setOld(array $data): void
    {
        $_SESSION[self::OLD_KEY] = $data;
    }

    public static function increment(string $key, int $amount = 1): int
    {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = 0;
        }
        $_SESSION[$key] += $amount;
        return $_SESSION[$key];
    }

    public static function decrement(string $key, int $amount = 1): int
    {
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = 0;
        }
        $_SESSION[$key] -= $amount;
        return $_SESSION[$key];
    }

    public static function token(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['_token'] = $token;
        return $token;
    }

    public static function verifyToken(string $token): bool
    {
        return hash_equals($_SESSION['_token'] ?? '', $token);
    }
}


