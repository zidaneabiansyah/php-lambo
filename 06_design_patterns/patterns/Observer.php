<?php

interface Observer
{
    public function update(string $event, mixed $data): void;
}

interface Subject
{
    public function attach(Observer $observer): void;
    public function detach(Observer $observer): void;
    public function notify(string $event, mixed $data): void;
}

class UserService implements Subject
{
    private array $observers = [];

    public function attach(Observer $observer): void
    {
        $this->observers[] = $observer;
    }

    public function detach(Observer $observer): void
    {
        $this->observers = array_filter(
            $this->observers,
            fn($o) => $o !== $observer,
        );
    }

    public function notify(string $event, mixed $data): void
    {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }

    public function registerUser(string $email): void
    {
        $this->notify('user.registered', ['email' => $email]);
    }

    public function loginUser(string $email): void
    {
        $this->notify('user.login', ['email' => $email]);
    }
}

class LoggerObserver implements Observer
{
    private array $logs = [];

    public function update(string $event, mixed $data): void
    {
        $msg = "[LOG] $event: " . json_encode($data);
        $this->logs[] = $msg;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}

class EmailObserver implements Observer
{
    private array $sent = [];

    public function update(string $event, mixed $data): void
    {
        $email = $data['email'] ?? 'unknown';
        if ($event === 'user.registered') {
            $this->sent[] = "Welcome email sent to $email";
        }
    }

    public function getSent(): array
    {
        return $this->sent;
    }
}
