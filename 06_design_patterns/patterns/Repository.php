<?php

class UserModel
{
    public function __construct(
        private int $id,
        private string $name,
        private string $email,
    ) {}

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
}

interface UserRepository
{
    public function findById(int $id): ?UserModel;
    public function findByEmail(string $email): ?UserModel;
    public function findAll(): array;
    public function save(UserModel $user): UserModel;
    public function delete(int $id): bool;
}

class InMemoryUserRepository implements UserRepository
{
    private array $users = [];
    private int $nextId = 1;

    public function findById(int $id): ?UserModel
    {
        return $this->users[$id] ?? null;
    }

    public function findByEmail(string $email): ?UserModel
    {
        foreach ($this->users as $user) {
            if ($user->getEmail() === $email) {
                return $user;
            }
        }
        return null;
    }

    public function findAll(): array
    {
        return array_values($this->users);
    }

    public function save(UserModel $user): UserModel
    {
        $id = $this->nextId++;
        $this->users[$id] = $user;
        return $user;
    }

    public function delete(int $id): bool
    {
        if (isset($this->users[$id])) {
            unset($this->users[$id]);
            return true;
        }
        return false;
    }
}
