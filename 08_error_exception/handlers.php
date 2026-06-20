<?php

// ============================================
// Error & Exception Handlers
// ============================================

class ErrorHandler
{
    private static bool $registered = false;
    private static array $logs = [];

    public static function register(): void
    {
        if (self::$registered) return;

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
        echo "[Handler] Error & exception handler terdaftar\n";
    }

    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line,
    ): bool {
        $log = "[Error] $message in $file:$line (severity: $severity)";
        self::$logs[] = $log;

        if (error_reporting() & $severity) {
            echo "$log\n";
        }

        return true;
    }

    public static function handleException(Throwable $e): void
    {
        $log = sprintf(
            "[Exception] %s: %s in %s:%d\nStack trace:\n%s",
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
        );

        self::$logs[] = $log;

        http_response_code(match (true) {
            $e instanceof AuthenticationException => 401,
            $e instanceof AuthorizationException => 403,
            $e instanceof NotFoundException => 404,
            $e instanceof ValidationException => 400,
            $e instanceof RateLimitException => 429,
            default => 500,
        });

        echo "[Exception Handler] " . $e->getMessage() . "\n";
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $log = "[Fatal] {$error['message']} in {$error['file']}:{$error['line']}";
            self::$logs[] = $log;
            echo "$log\n";
        }
    }

    public static function getLogs(): array
    {
        return self::$logs;
    }
}

class DebugHandler
{
    public static function dump(Throwable $e): string
    {
        return sprintf(
            "<<< ERROR >>>\nType: %s\nMessage: %s\nFile: %s\nLine: %d\nCode: %d\n%s\n",
            $e::class,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getCode(),
            $e->getTraceAsString(),
        );
    }

    public static function prettyPrint(Throwable $e): void
    {
        echo "\n========================================\n";
        echo "  ERROR OCCURRED\n";
        echo "========================================\n";
        echo "  Type:    " . $e::class . "\n";
        echo "  Message: " . $e->getMessage() . "\n";
        echo "  File:    " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "  Code:    " . $e->getCode() . "\n";
        echo "----------------------------------------\n";
        echo "  Stack Trace:\n";
        foreach ($e->getTrace() as $i => $trace) {
            $file = $trace['file'] ?? '[internal]';
            $line = $trace['line'] ?? '0';
            $func = $trace['function'] ?? '?';
            $class = $trace['class'] ?? '';
            echo "  #$i $class$func() at $file:$line\n";
        }
        echo "========================================\n";
    }
}
