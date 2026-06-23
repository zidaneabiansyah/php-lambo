<?php

namespace App\Core;

class Database
{
    private static ?\PDO $instance = null;
    private static string $path = '';

    public static function connect(?string $path = null): \PDO
    {
        if ($path) {
            self::$path = $path;
        }

        if (self::$instance === null) {
            self::$instance = new \PDO(
                'sqlite:' . self::$path,
                null,
                null,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );
            self::$instance->exec('PRAGMA journal_mode=WAL');
            self::$instance->exec('PRAGMA foreign_keys=ON');
        }

        return self::$instance;
    }

    public static function pdo(): \PDO
    {
        return self::connect();
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        self::query("INSERT INTO $table ($columns) VALUES ($placeholders)", array_values($data));
        return self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $stmt = self::query("UPDATE $table SET $sets WHERE $where", array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        $stmt = self::query("DELETE FROM $table WHERE $where", $params);
        return $stmt->rowCount();
    }

    public static function runMigrations(): void
    {
        self::query("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        )");

        self::query("CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            user_id INTEGER NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            updated_at TEXT NOT NULL DEFAULT (datetime('now')),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
    }

    public static function seed(): void
    {
        $count = self::fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
        if ($count > 0) return;

        $userId = self::insert('users', [
            'name' => 'Budi Santoso',
            'email' => 'budi@test.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
        ]);

        self::insert('posts', [
            'title' => 'Belajar PHP dari Nol',
            'content' => 'PHP adalah bahasa pemrograman yang sangat populer untuk web development. Dengan PHP, kita bisa membuat aplikasi web dinamis dengan mudah.',
            'user_id' => $userId,
        ]);

        self::insert('posts', [
            'title' => 'MVC Pattern untuk Pemula',
            'content' => 'MVC memisahkan aplikasi menjadi Model, View, dan Controller. Pola ini membantu kode lebih terstruktur dan mudah dipelihara.',
            'user_id' => $userId,
        ]);

        self::insert('posts', [
            'title' => 'Database dengan PDO',
            'content' => 'PDO (PHP Data Objects) adalah cara aman untuk mengakses database. Dengan prepared statement, kita terhindar dari SQL injection.',
            'user_id' => $userId,
        ]);
    }
}
