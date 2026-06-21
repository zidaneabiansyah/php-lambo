<?php

namespace App\Models;

class Post
{
    private static array $posts = [];

    public function __construct(
        private int $id,
        private string $title,
        private string $content,
        private int $userId,
        private string $createdAt = '',
    ) {
        $this->createdAt = $createdAt ?: date('Y-m-d H:i:s');
        self::$posts[$id] = $this;
    }

    public static function find(int $id): ?self
    {
        return self::$posts[$id] ?? null;
    }

    public static function all(): array
    {
        return array_values(self::$posts);
    }

    public static function findByUser(int $userId): array
    {
        return array_values(array_filter(self::$posts, fn($p) => $p->userId === $userId));
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getExcerpt(int $length = 100): string
    {
        return strlen($this->content) > $length
            ? substr($this->content, 0, $length) . '...'
            : $this->content;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->getExcerpt(),
            'user_id' => $this->userId,
            'created_at' => $this->createdAt,
        ];
    }
}
