<?php

namespace App\Services;

use App\Contracts\LoggerInterface;

class DatabaseLogger implements LoggerInterface
{
    private array $logs = [];

    public function log(string $level, string $message): void
    {
        $this->logs[] = "[DB] [$level] $message";
    }

    public function info(string $message): void
    {
        $this->log('info', $message);
    }

    public function error(string $message): void
    {
        $this->log('error', $message);
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}
