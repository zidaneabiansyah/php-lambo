<?php

namespace App\Services;

class LoggerService
{
    private string $logFile;
    private array $logs = [];
    private static array $levels = ['debug', 'info', 'warning', 'error', 'critical'];

    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile ?? sys_get_temp_dir() . '/app.log';
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $entry = "[$timestamp] [$level] $message$contextStr";

        $this->logs[] = $entry;
        file_put_contents($this->logFile, $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function getRecentLogs(int $count = 10): array
    {
        return array_slice($this->logs, -$count);
    }

    public function clear(): void
    {
        $this->logs = [];
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }

    public static function getLevels(): array
    {
        return self::$levels;
    }
}
