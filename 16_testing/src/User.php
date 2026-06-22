<?php

class User
{
    private int $id;
    private string $name;
    private string $email;
    private string $role;
    private \DateTimeImmutable $createdAt;

    public function __construct(string $name, string $email, string $role = 'user')
    {
        $this->validateEmail($email);
        $this->validateRole($role);

        $this->id = rand(1000, 9999);
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->createdAt = new \DateTimeImmutable();
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function updateEmail(string $email): void
    {
        $this->validateEmail($email);
        $this->email = $email;
    }

    public function promote(): void
    {
        $this->role = 'admin';
    }

    public function demote(): void
    {
        $this->role = 'user';
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

    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email: $email");
        }
    }

    private function validateRole(string $role): void
    {
        $valid = ['user', 'admin', 'moderator'];
        if (!in_array($role, $valid, true)) {
            throw new \InvalidArgumentException("Invalid role: $role");
        }
    }
}
