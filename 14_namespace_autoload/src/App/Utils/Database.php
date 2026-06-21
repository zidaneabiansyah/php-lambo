<?php

namespace App\Utils;

class Database
{
    private static ?Database $instance = null;
    private ?\PDO $pdo = null;
    private int $queryCount = 0;

    public function __construct(
        private string $driver = 'sqlite',
        private string $host = 'localhost',
        private string $dbname = ':memory:',
        private string $user = 'root',
        private string $password = '',
    ) {
        if ($driver === 'sqlite') {
            $dsn = "sqlite:$dbname";
            $this->pdo = new \PDO($dsn, null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $this->queryCount++;
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $this->queryCount++;
        return $stmt->rowCount();
    }

    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
}
