<?php

namespace App;

class Config
{
    private array $items = [];

    public function __construct()
    {
        $this->load();
    }

    private function load(): void
    {
        // Load from environment variables first
        $this->items['app_name'] = $_ENV['APP_NAME'] ?? 'BelajarPHP';
        $this->items['app_env'] = $_ENV['APP_ENV'] ?? 'production';
        $this->items['app_debug'] = ($_ENV['APP_DEBUG'] ?? false) === 'true';
        $this->items['db_host'] = $_ENV['DB_HOST'] ?? 'localhost';
        $this->items['db_port'] = (int) ($_ENV['DB_PORT'] ?? 3306);
        $this->items['db_name'] = $_ENV['DB_NAME'] ?? 'belajar_php';
        $this->items['db_user'] = $_ENV['DB_USER'] ?? 'root';
        $this->items['db_pass'] = $_ENV['DB_PASS'] ?? '';
        $this->items['mail_driver'] = $_ENV['MAIL_DRIVER'] ?? 'smtp';
        $this->items['mail_host'] = $_ENV['MAIL_HOST'] ?? 'localhost';
        $this->items['mail_port'] = (int) ($_ENV['MAIL_PORT'] ?? 587);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->items[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function isLocal(): bool
    {
        return $this->items['app_env'] === 'local';
    }

    public function isDebug(): bool
    {
        return $this->items['app_debug'];
    }
}
