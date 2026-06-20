<?php

class Database
{
    private static ?PDO $instance = null;
    private static string $dsn;
    private static ?string $user = null;
    private static ?string $password = null;
    private static array $options = [];
    private static int $queryCount = 0;
    private static array $queries = [];

    public static function config(
        string $dsn,
        ?string $user = null,
        ?string $password = null,
        array $options = [],
    ): void {
        self::$dsn = $dsn;
        self::$user = $user;
        self::$password = $password;
        self::$options = $options;
    }

    public static function connect(): PDO
    {
        if (self::$instance === null) {
            $defaultOptions = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_STRINGIFY_FETCHES => false,
            ];

            $options = array_replace($defaultOptions, self::$options);

            try {
                self::$instance = new PDO(
                    self::$dsn,
                    self::$user,
                    self::$password,
                    $options,
                );
            } catch (PDOException $e) {
                throw new RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$instance;
    }

    public static function disconnect(): void
    {
        self::$instance = null;
    }

    public static function pdo(): PDO
    {
        return self::connect();
    }

    public static function raw(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        self::$queryCount++;
        self::$queries[] = ['sql' => $sql, 'params' => $params];
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $result = self::raw($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::raw($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        self::raw($sql, array_values($data));

        return self::connect()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $sets = implode(', ', array_map(fn($col) => "$col = ?", array_keys($data)));
        $sql = "UPDATE $table SET $sets WHERE $where";
        $stmt = self::raw($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = self::raw($sql, $params);
        return $stmt->rowCount();
    }

    public static function beginTransaction(): bool
    {
        return self::connect()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::connect()->commit();
    }

    public static function rollback(): bool
    {
        return self::connect()->rollBack();
    }

    public static function transaction(callable $callback): mixed
    {
        self::connect()->beginTransaction();
        try {
            $result = $callback();
            self::connect()->commit();
            return $result;
        } catch (Throwable $e) {
            self::connect()->rollBack();
            throw $e;
        }
    }

    public static function getQueryCount(): int
    {
        return self::$queryCount;
    }

    public static function getQueries(): array
    {
        return self::$queries;
    }

    public static function quote(mixed $value): string
    {
        return self::connect()->quote($value);
    }
}

class DbConfig
{
    public static function sqlite(string $path): void
    {
        Database::config("sqlite:$path");
    }

    public static function mysql(
        string $host,
        string $dbname,
        string $user,
        string $password,
        int $port = 3306,
        string $charset = 'utf8mb4',
    ): void {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
        Database::config($dsn, $user, $password);
    }

    public static function postgres(
        string $host,
        string $dbname,
        string $user,
        string $password,
        int $port = 5432,
    ): void {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        Database::config($dsn, $user, $password);
    }

    public static function memory(): void
    {
        Database::config('sqlite::memory:');
    }
}
