<?php

namespace App\Models;

use App\Core\Database;

class Post
{
    public static function all(): array
    {
        return Database::fetchAll("
            SELECT p.*, u.name as author
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
        ");
    }

    public static function find(int $id): ?array
    {
        return Database::fetch("
            SELECT p.*, u.name as author
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ", [$id]);
    }

    public static function findByUser(int $userId): array
    {
        return Database::fetchAll(
            "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC",
            [$userId],
        );
    }

    public static function create(array $data): string
    {
        return Database::insert('posts', [
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $data['user_id'],
        ]);
    }

    public static function update(int $id, array $data): int
    {
        $fields = [];
        $params = [];

        if (isset($data['title'])) {
            $fields[] = 'title';
            $params[] = $data['title'];
        }
        if (isset($data['content'])) {
            $fields[] = 'content';
            $params[] = $data['content'];
        }

        $fields[] = 'updated_at';
        $params[] = date('Y-m-d H:i:s');
        $params[] = $id;

        $sets = implode(' = ?, ', $fields) . ' = ?';
        return Database::update('posts', array_combine($fields, array_slice($params, 0, -1)), "id = ?", [$id]);
    }

    public static function delete(int $id): int
    {
        return Database::delete('posts', "id = ?", [$id]);
    }

    public static function count(): int
    {
        $result = Database::fetch("SELECT COUNT(*) as count FROM posts");
        return (int) ($result['count'] ?? 0);
    }

    public static function latest(int $limit = 5): array
    {
        return Database::fetchAll("
            SELECT p.*, u.name as author
            FROM posts p
            JOIN users u ON p.user_id = u.id
            ORDER BY p.created_at DESC
            LIMIT ?
        ", [$limit]);
    }
}
