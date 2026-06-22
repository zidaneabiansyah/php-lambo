<?php

class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    private int $connectionId;

    private function __construct()
    {
        $this->connectionId = rand(100, 999);
    }

    public static function getInstance(): DatabaseConnection
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql): string
    {
        return "Result of: $sql (connection #{$this->connectionId})";
    }

    public function getConnectionId(): int
    {
        return $this->connectionId;
    }

    private function __clone() {}
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
