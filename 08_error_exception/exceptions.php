<?php

// ============================================
// Custom Exception Classes
// ============================================

class ValidationException extends Exception
{
    private array $errors;

    public function __construct(
        string $message = "Validasi gagal",
        array $errors = [],
        int $code = 400,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}

class AuthenticationException extends Exception
{
    public function __construct(
        string $message = "Tidak terautentikasi",
        int $code = 401,
    ) {
        parent::__construct($message, $code);
    }
}

class AuthorizationException extends Exception
{
    public function __construct(
        string $message = "Tidak punya akses",
        int $code = 403,
    ) {
        parent::__construct($message, $code);
    }
}

class NotFoundException extends Exception
{
    public function __construct(
        string $message = "Data tidak ditemukan",
        int $code = 404,
    ) {
        parent::__construct($message, $code);
    }
}

class DatabaseException extends Exception
{
    private string $sqlState;

    public function __construct(
        string $message = "Database error",
        string $sqlState = "",
        int $code = 500,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
        $this->sqlState = $sqlState;
    }

    public function getSqlState(): string
    {
        return $this->sqlState;
    }
}

class RateLimitException extends Exception
{
    public function __construct(
        private int $retryAfter,
        string $message = "Terlalu banyak request",
    ) {
        parent::__construct($message, 429);
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}

class ConfigurationException extends Exception
{
    public function __construct(
        string $key,
        string $message = "Konfigurasi tidak ditemukan: ",
    ) {
        parent::__construct($message . $key, 500);
    }
}
