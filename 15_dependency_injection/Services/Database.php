<?php

namespace App\Services;

class Database
{
    private string $dsn;
    private string $user;

    public function __construct(string $dsn, string $user = 'root')
    {
        $this->dsn = $dsn;
        $this->user = $user;
    }

    public function query(string $sql): string
    {
        return "Executed on $this->dsn as $this->user: $sql";
    }
}
