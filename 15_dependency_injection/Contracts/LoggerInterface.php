<?php

namespace App\Contracts;

interface LoggerInterface
{
    public function log(string $level, string $message): void;
    public function info(string $message): void;
    public function error(string $message): void;
    public function getLogs(): array;
}
