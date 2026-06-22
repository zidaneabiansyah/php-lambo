<?php

namespace App\Services;

use App\Contracts\LoggerInterface;

class FileLogger implements LoggerInterface
{
    private array $logs = [];

    public function log(string $level, string $message): void
    {
        $entry = sprintf('[%s] %s: %s', date('Y-m-d H:i:s'), strtoupper($level), $message);
        $this->logs[] = $entry;
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
