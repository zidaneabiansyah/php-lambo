<?php

class CsrfProtection
{
    private const TOKEN_KEY = '_csrf_token';
    private const TOKEN_LENGTH = 32;

    public static function generate(): string
    {
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION[self::TOKEN_KEY] = $token;
        }

        return $token;
    }

    public static function token(): ?string
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return $_SESSION[self::TOKEN_KEY] ?? null;
        }
        return null;
    }

    public static function verify(?string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $stored = $_SESSION[self::TOKEN_KEY] ?? null;

        if (empty($token) || empty($stored)) {
            return false;
        }

        $valid = hash_equals($stored, $token);

        if ($valid) {
            unset($_SESSION[self::TOKEN_KEY]);
        }

        return $valid;
    }

    public static function field(): string
    {
        $token = static::generate();
        return '<input type="hidden" name="_token" value="' . $token . '">';
    }

    public static function header(): string
    {
        $token = static::generate();
        return $token;
    }
}

class FlashMessage
{
    private const FLASH_KEY = '_flash_messages';

    public static function set(string $type, string $message): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION[self::FLASH_KEY][$type][] = $message;
    }

    public static function success(string $message): void
    {
        self::set('success', $message);
    }

    public static function error(string $message): void
    {
        self::set('error', $message);
    }

    public static function warning(string $message): void
    {
        self::set('warning', $message);
    }

    public static function info(string $message): void
    {
        self::set('info', $message);
    }

    public static function has(string $type): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }
        return !empty($_SESSION[self::FLASH_KEY][$type]);
    }

    public static function get(string $type): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return [];
        }
        $messages = $_SESSION[self::FLASH_KEY][$type] ?? [];
        unset($_SESSION[self::FLASH_KEY][$type]);
        return $messages;
    }

    public static function all(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return [];
        }
        $messages = $_SESSION[self::FLASH_KEY] ?? [];
        unset($_SESSION[self::FLASH_KEY]);
        return $messages;
    }

    public static function render(string $type): string
    {
        $messages = self::get($type);
        if (empty($messages)) return '';

        $colors = [
            'success' => 'green',
            'error' => 'red',
            'warning' => 'orange',
            'info' => 'blue',
        ];

        $color = $colors[$type] ?? 'gray';
        $html = '';
        foreach ($messages as $msg) {
            $html .= "<div class=\"alert alert-$color\">" . htmlspecialchars($msg) . "</div>\n";
        }
        return $html;
    }

    public static function renderAll(): string
    {
        $html = '';
        foreach (['success', 'error', 'warning', 'info'] as $type) {
            $html .= self::render($type);
        }
        return $html;
    }
}

class OldInput
{
    private const KEY = '_old_input';

    public static function set(array $data): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $_SESSION[self::KEY] = $data;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return $default;
        }
        return $_SESSION[self::KEY][$key] ?? $default;
    }

    public static function all(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return [];
        }
        return $_SESSION[self::KEY] ?? [];
    }

    public static function clear(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        unset($_SESSION[self::KEY]);
    }

    public static function repopulate(array $data): void
    {
        self::set($data);
    }
}
