<?php

namespace App\Models;

class Post
{
    private static array $posts = [];
    private static int $nextId = 1;

    public static function all(): array
    {
        return array_values(self::$posts);
    }

    public static function find(int $id): ?array
    {
        return self::$posts[$id] ?? null;
    }

    public static function findByUser(int $userId): array
    {
        return array_values(array_filter(self::$posts, fn($p) => $p['user_id'] === $userId));
    }

    public static function create(array $data): array
    {
        $id = self::$nextId++;
        $post = [
            'id' => $id,
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $data['user_id'],
            'author' => $data['author'] ?? 'Unknown',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        self::$posts[$id] = $post;
        return $post;
    }

    public static function update(int $id, array $data): ?array
    {
        if (!isset(self::$posts[$id])) return null;
        self::$posts[$id] = array_merge(self::$posts[$id], $data);
        return self::$posts[$id];
    }

    public static function delete(int $id): bool
    {
        if (!isset(self::$posts[$id])) return false;
        unset(self::$posts[$id]);
        return true;
    }

    public static function count(): int
    {
        return count(self::$posts);
    }

    public static function search(string $keyword): array
    {
        return array_values(array_filter(self::$posts, fn($p) =>
            str_contains(strtolower($p['title']), strtolower($keyword))
        ));
    }

    public static function latest(int $limit = 5): array
    {
        $all = array_reverse(self::$posts, true);
        return array_slice(array_values($all), 0, $limit);
    }

    public static function seed(): void
    {
        self::create([
            'title' => 'Belajar MVC Pattern di PHP',
            'content' => 'MVC adalah singkatan dari Model-View-Controller. MVC memisahkan aplikasi menjadi tiga komponen utama: Model untuk data, View untuk tampilan, dan Controller untuk logika. Pola ini sangat populer di framework PHP seperti Laravel.',
            'user_id' => 1,
            'author' => 'Budi Santoso',
        ]);
        self::create([
            'title' => 'Apa itu Routing?',
            'content' => 'Routing adalah proses menentukan Controller dan Action mana yang menangani request tertentu. URL seperti /users/1 akan di-route ke UserController dengan parameter id=1.',
            'user_id' => 1,
            'author' => 'Budi Santoso',
        ]);
        self::create([
            'title' => 'Pengenalan REST API',
            'content' => 'REST API menggunakan method HTTP (GET, POST, PUT, DELETE) untuk operasi CRUD. GET untuk membaca data, POST untuk membuat, PUT untuk mengupdate, DELETE untuk menghapus.',
            'user_id' => 2,
            'author' => 'Ani Wijaya',
        ]);
    }
}
