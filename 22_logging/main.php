<?php

// ============================================
// 22 - LOGGING (PSR-3)
// ============================================
// Topik: PSR-3 Logger Interface, Log levels,
//        File logger, Multiple handlers,
//        Context data, Formatted output
// ============================================

echo "==========================================\n";
echo "  LOGGING (PSR-3)\n";
echo "==========================================\n\n";

// ============================================
// BAGIAN A: PSR-3 LOGGABLE INTERFACE
// ============================================

// PSR-3 adalah standar interface untuk logging di PHP
// Semua method menerima string $message dan optional array $context

echo "--- 1. LOG LEVELS ---\n\n";

// 8 Log levels dari PSR-3 (dari paling serius ke paling ringan):
$levels = [
    'EMERGENCY' => 'Sistem tidak bisa berjalan',
    'ALERT'     => 'Perlu tindakan segera',
    'CRITICAL'  => 'Kondisi kritis',
    'ERROR'     => 'Error normal',
    'WARNING'   => 'Peringatan',
    'NOTICE'    => 'Informasi normal',
    'INFO'      => 'Informasi umum',
    'DEBUG'     => 'Debug detail',
];

foreach ($levels as $level => $desc) {
    echo "  " . str_pad($level, 12) . " -> $desc\n";
}
echo "\n";


// ============================================
// BAGIAN B: LOGGER IMPLEMENTATION
// ============================================

class Logger
{
    private string $name;
    private array $handlers = [];
    private int $minLevel = 0;

    private const LEVELS = [
        'EMERGENCY' => 0,
        'ALERT'     => 1,
        'CRITICAL'  => 2,
        'ERROR'     => 3,
        'WARNING'   => 4,
        'NOTICE'    => 5,
        'INFO'      => 6,
        'DEBUG'     => 7,
    ];

    public function __construct(string $name = 'app')
    {
        $this->name = $name;
    }

    public function pushHandler(callable $handler): self
    {
        $this->handlers[] = $handler;
        return $this;
    }

    public function setMinLevel(string $level): self
    {
        $this->minLevel = self::LEVELS[strtoupper($level)] ?? 0;
        return $this;
    }

    private function log(string $level, string $message, array $context = []): void
    {
        $levelNum = self::LEVELS[strtoupper($level)] ?? 7;

        if ($levelNum > $this->minLevel) {
            return;
        }

        $record = [
            'message' => $message,
            'context' => $context,
            'level' => $level,
            'channel' => $this->name,
            'timestamp' => date('Y-m-d H:i:s'),
            'extra' => [],
        ];

        // Replace context placeholders dalam message
        $record['message'] = $this->interpolate($message, $context);

        foreach ($this->handlers as $handler) {
            $handler($record);
        }
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_string($val) || is_numeric($val)) {
                $replace['{' . $key . '}'] = $val;
            }
        }
        return strtr($message, $replace);
    }

    // Public logging methods
    public function emergency(string $message, array $context = []): void
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert(string $message, array $context = []): void
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice(string $message, array $context = []): void
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }

    public function getName(): string
    {
        return $this->name;
    }
}


// ============================================
// BAGIAN C: HANDLERS
// ============================================

echo "--- 2. CONSOLE/STDOUT HANDLER ---\n\n";

function consoleHandler(array $record): void
{
    $level = str_pad($record['level'], 10);
    $time = $record['timestamp'];
    $msg = $record['message'];

    // Color coding berdasarkan level
    $color = match ($record['level']) {
        'EMERGENCY', 'ALERT', 'CRITICAL' => "\033[1;31m", // Bold Red
        'ERROR'    => "\033[0;31m", // Red
        'WARNING'  => "\033[0;33m", // Yellow
        'NOTICE'   => "\033[0;36m", // Cyan
        'INFO'     => "\033[0;32m", // Green
        'DEBUG'    => "\033[0;37m", // Gray
        default    => "\033[0m",
    };

    echo "  {$color}[{$level}]\033[0m {$time} - {$msg}\n";
}

$logger = new Logger('console');
$logger->pushHandler('consoleHandler');

$logger->info('Application started');
$logger->debug('Loading configuration');
$logger->warning('Memory usage high');
$logger->error('Database connection failed', ['host' => 'localhost']);

echo "\n";


echo "--- 3. FILE LOGGER ---\n\n";

class FileLogger
{
    private string $filename;
    private string $format;

    public function __construct(string $filename, string $format = 'text')
    {
        $this->filename = $filename;
        $this->format = $format;

        // Pastikan directory exists
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    public function __invoke(array $record): void
    {
        $line = $this->format === 'json'
            ? json_encode($record) . "\n"
            : "[{$record['timestamp']}] [{$record['level']}] [{$record['channel']}] {$record['message']}\n";

        file_put_contents($this->filename, $line, FILE_APPEND | LOCK_EX);
    }

    public function read(int $lines = 10): array
    {
        if (!file_exists($this->filename)) {
            return [];
        }

        $content = file_get_contents($this->filename);
        $allLines = explode("\n", trim($content));
        return array_slice($allLines, -$lines);
    }

    public function clear(): void
    {
        file_put_contents($this->filename, '');
    }

    public function getSize(): int
    {
        return file_exists($this->filename) ? filesize($this->filename) : 0;
    }
}

$logDir = sys_get_temp_dir() . '/php_lambo_logs';
$textLogger = new FileLogger("$logDir/app.log");
$jsonLogger = new FileLogger("$logDir/app.json", 'json');

$logger = new Logger('file');
$logger->pushHandler($textLogger);
$logger->pushHandler($jsonLogger);

$logger->info('User login', ['user_id' => 123, 'ip' => '192.168.1.1']);
$logger->warning('Disk space low', ['free_mb' => 50]);
$logger->error('Payment failed', ['order_id' => 'ORD-001', 'amount' => 150000]);

// Baca log
$recentLogs = $textLogger->read(5);
echo "  Recent log entries:\n";
foreach ($recentLogs as $log) {
    echo "    $log\n";
}
echo "  Log file size: " . $textLogger->getSize() . " bytes\n\n";


echo "--- 4. MULTIPLE HANDLERS (Composite Logger) ---\n\n";

class CompositeLogger
{
    private array $loggers = [];

    public function addLogger(Logger $logger): self
    {
        $this->loggers[] = $logger;
        return $this;
    }

    public function info(string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->info($message, $context);
        }
    }

    public function error(string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->error($message, $context);
        }
    }

    public function warning(string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->warning($message, $context);
        }
    }

    public function debug(string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->debug($message, $context);
        }
    }

    public function emergency(string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->emergency($message, $context);
        }
    }
}

$consoleLogger = new Logger('console');
$consoleLogger->pushHandler('consoleHandler');

$fileLogger = new Logger('file');
$fileLogger->pushHandler(new FileLogger("$logDir/composite.log"));

$composite = new CompositeLogger();
$composite->addLogger($consoleLogger);
$composite->addLogger($fileLogger);

echo "  Composite logging (console + file):\n";
$composite->info('Composite log message');
$composite->error('Error in composite', ['code' => 500]);
echo "\n";


// ============================================
// BAGIAN D: CONTEXT & EXTRA DATA
// ============================================

echo "--- 5. CONTEXT & EXTRA DATA ---\n\n";

class ContextLogger
{
    private array $records = [];
    private array $globalContext = [];

    public function __construct(array $globalContext = [])
    {
        $this->globalContext = $globalContext;
    }

    public function pushProcessor(callable $processor): self
    {
        $this->processors[] = $processor;
        return $this;
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $record = [
            'level' => $level,
            'message' => $message,
            'context' => array_merge($this->globalContext, $context),
            'timestamp' => date('Y-m-d H:i:s.u'),
            'extra' => [],
        ];

        // Apply processors
        foreach ($this->processors ?? [] as $processor) {
            $record = $processor($record);
        }

        $this->records[] = $record;

        // Tampilkan
        $ctxStr = !empty($record['context']) ? ' ' . json_encode($record['context']) : '';
        echo "  [{$record['level']}] {$record['timestamp']} - {$message}{$ctxStr}\n";
    }

    public function getRecords(): array
    {
        return $this->records;
    }
}

$contextLogger = new ContextLogger([
    'app' => 'php-lambo',
    'env' => 'development',
]);

// Tambah processor untuk menambahkan request ID
$contextLogger->pushProcessor(function (array $record): array {
    $record['extra']['request_id'] = bin2hex(random_bytes(8));
    return $record;
});

// Tambah processor untuk menambahkan memory usage
$contextLogger->pushProcessor(function (array $record): array {
    $record['extra']['memory_mb'] = round(memory_get_usage() / 1024 / 1024, 2);
    return $record;
});

$contextLogger->log('INFO', 'Processing request', ['url' => '/api/users']);
$contextLogger->log('WARNING', 'Slow query detected', ['query_time_ms' => 1500]);
$contextLogger->log('ERROR', 'Exception caught', ['exception' => 'RuntimeException']);

echo "\n";


echo "--- 6. FORMATTED OUTPUT ---\n\n";

// Berbagai format output
interface LogFormatter
{
    public function format(array $record): string;
}

class TextFormatter implements LogFormatter
{
    public function format(array $record): string
    {
        return "[{$record['timestamp']}] [{$record['level']}] [{$record['channel']}] {$record['message']}";
    }
}

class JsonFormatter implements LogFormatter
{
    public function format(array $record): string
    {
        return json_encode($record);
    }
}

class HtmlFormatter implements LogFormatter
{
    private const COLORS = [
        'ERROR' => '#e74c3c',
        'WARNING' => '#f39c12',
        'INFO' => '#2ecc71',
        'DEBUG' => '#95a5a6',
    ];

    public function format(array $record): string
    {
        $color = self::COLORS[$record['level']] ?? '#333';
        $context = !empty($record['context']) ? '<br><small>' . json_encode($record['context']) . '</small>' : '';

        return "<div style=\"color:{$color};padding:4px;border-bottom:1px solid #eee;\">\n"
            . "<strong>[{$record['level']}]</strong> "
            . "<span style=\"color:#999;\">{$record['timestamp']}</span> "
            . "{$record['message']}"
            . "{$context}\n"
            . "</div>";
    }
}

$records = [
    ['timestamp' => date('Y-m-d H:i:s'), 'level' => 'INFO', 'channel' => 'app', 'message' => 'User logged in', 'context' => ['user_id' => 1]],
    ['timestamp' => date('Y-m-d H:i:s'), 'level' => 'ERROR', 'channel' => 'app', 'message' => 'Database timeout', 'context' => ['query' => 'SELECT *']],
];

$textFormatter = new TextFormatter();
$jsonFormatter = new JsonFormatter();
$htmlFormatter = new HtmlFormatter();

echo "  Text format:\n";
foreach ($records as $record) {
    echo "    " . $textFormatter->format($record) . "\n";
}

echo "\n  JSON format:\n";
foreach ($records as $record) {
    echo "    " . $jsonFormatter->format($record) . "\n";
}

echo "\n  HTML format (rendered):\n";
$html = '';
foreach ($records as $record) {
    $html .= $htmlFormatter->format($record);
}
echo "    " . str_replace("\n", "\n    ", $html) . "\n\n";


// ============================================
// BAGIAN E: ROTATING FILE LOGGER
// ============================================

echo "--- 7. ROTATING FILE LOGGER ---\n\n";

class RotatingFileLogger
{
    private string $directory;
    private string $filename;
    private int $maxFiles;
    private int $maxSize;

    public function __construct(string $directory, string $filename = 'app.log', int $maxFiles = 5, int $maxSize = 1024 * 1024)
    {
        $this->directory = $directory;
        $this->filename = $filename;
        $this->maxFiles = $maxFiles;
        $this->maxSize = $maxSize;

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    public function __invoke(array $record): void
    {
        $filepath = $this->directory . '/' . $this->filename;

        // Check if rotation needed
        if (file_exists($filepath) && filesize($filepath) >= $this->maxSize) {
            $this->rotate();
        }

        $line = "[{$record['timestamp']}] [{$record['level']}] {$record['message']}\n";
        file_put_contents($filepath, $line, FILE_APPEND | LOCK_EX);
    }

    private function rotate(): void
    {
        // Hapus file tertua
        $oldestFile = $this->directory . '/' . $this->filename . '.' . $this->maxFiles;
        if (file_exists($oldestFile)) {
            unlink($oldestFile);
        }

        // Shift existing files
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $from = $this->directory . '/' . $this->filename . '.' . $i;
            $to = $this->directory . '/' . $this->filename . '.' . ($i + 1);
            if (file_exists($from)) {
                rename($from, $to);
            }
        }

        // Rename current to .1
        $current = $this->directory . '/' . $this->filename;
        if (file_exists($current)) {
            rename($current, $current . '.1');
        }
    }

    public function getFiles(): array
    {
        $files = glob($this->directory . '/' . $this->filename . '*');
        sort($files);
        return $files;
    }
}

$rotatingDir = sys_get_temp_dir() . '/php_lambo_rotating';
$rotatingLogger = new RotatingFileLogger($rotatingDir, 'app.log', 3, 200);

$logger = new Logger('rotating');
$logger->pushHandler($rotatingLogger);

// Generate beberapa log entries
for ($i = 0; $i < 5; $i++) {
    $logger->info("Log entry $i", ['sequence' => $i]);
}

$files = $rotatingLogger->getFiles();
echo "  Rotating log files:\n";
foreach ($files as $file) {
    echo "    " . basename($file) . " (" . filesize($file) . " bytes)\n";
}
echo "\n";


// ============================================
// BAGIAN F: BUFFERED LOGGING
// ============================================

echo "--- 8. BUFFERED LOGGING ---\n\n";

class BufferedLogger
{
    private array $buffer = [];
    private int $bufferSize;
    private callable $flushHandler;

    public function __construct(int $bufferSize = 100, callable $flushHandler = null)
    {
        $this->bufferSize = $bufferSize;
        $this->flushHandler = $flushHandler ?? function (array $records): void {
            foreach ($records as $record) {
                echo "  [FLUSH] {$record['level']}: {$record['message']}\n";
            }
        };
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $this->buffer[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        if (count($this->buffer) >= $this->bufferSize) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        ($this->flushHandler)($this->buffer);
        $this->buffer = [];
    }

    public function getBufferSize(): int
    {
        return count($this->buffer);
    }

    public function __destruct()
    {
        $this->flush();
    }
}

$bufferedLogger = new BufferedLogger(3, function (array $records): void {
    echo "  Flushing " . count($records) . " records to storage\n";
});

$bufferedLogger->log('INFO', 'Log 1');
$bufferedLogger->log('INFO', 'Log 2');
echo "  Buffer size: " . $bufferedLogger->getBufferSize() . "\n";

$bufferedLogger->log('INFO', 'Log 3'); // This triggers flush
echo "  Buffer size after flush: " . $bufferedLogger->getBufferSize() . "\n\n";


// ============================================
// BAGIAN G: LOGGER DECORATOR
// ============================================

echo "--- 9. LOGGER DECORATOR ---\n\n";

class TimingLogger
{
    private Logger $logger;
    private array $timings = [];

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function startTimer(string $operation): void
    {
        $this->timings[$operation] = microtime(true);
    }

    public function endTimer(string $operation): void
    {
        if (!isset($this->timings[$operation])) {
            return;
        }

        $duration = (microtime(true) - $this->timings[$operation]) * 1000;
        unset($this->timings[$operation]);

        $this->logger->info("Operation completed", [
            'operation' => $operation,
            'duration_ms' => round($duration, 2),
        ]);
    }

    public function info(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }
}

$timingLogger = new TimingLogger($logger);

$timingLogger->startTimer('db_query');
usleep(50000); // Simulasi query 50ms
$timingLogger->endTimer('db_query');

$timingLogger->startTimer('api_call');
usleep(30000); // Simulasi API call 30ms
$timingLogger->endTimer('api_call');

$timingLogger->info('All operations completed');
echo "\n";


// ============================================
// BAGIAN H: PRACTICAL APPLICATION LOGGING
// ============================================

echo "--- 10. PRACTICAL: APPLICATION LOGGING ---\n\n";

class AppLogger
{
    private ContextLogger $logger;
    private string $logDir;

    public function __construct(string $logDir)
    {
        $this->logDir = $logDir;
        $this->logger = new ContextLogger([
            'app' => 'php-lambo',
            'version' => '1.0.0',
        ]);

        // Tambah processor untuk user context
        $this->logger->pushProcessor(function (array $record): array {
            // Simulasi user context
            $record['extra']['user_id'] = $_SERVER['REMOTE_USER'] ?? 'anonymous';
            $record['extra']['ip'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            return $record;
        });
    }

    public function logRequest(string $method, string $uri, int $statusCode, float $duration): void
    {
        $level = $statusCode >= 500 ? 'ERROR' : ($statusCode >= 400 ? 'WARNING' : 'INFO');

        $this->logger->log($level, "{$method} {$uri} {$statusCode}", [
            'method' => $method,
            'uri' => $uri,
            'status' => $statusCode,
            'duration_ms' => round($duration * 1000, 2),
        ]);
    }

    public function logError(\Throwable $e, array $context = []): void
    {
        $this->logger->log('ERROR', $e->getMessage(), array_merge($context, [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]));
    }

    public function logPerformance(string $metric, float $value, string $unit = 'ms'): void
    {
        $this->logger->log('NOTICE', "Performance: {$metric}", [
            'metric' => $metric,
            'value' => round($value, 2),
            'unit' => $unit,
        ]);
    }
}

$appLogger = new AppLogger($logDir);

// Log berbagai events
$appLogger->logRequest('GET', '/api/users', 200, 0.045);
$appLogger->logRequest('POST', '/api/users', 201, 0.120);
$appLogger->logRequest('GET', '/api/users/999', 404, 0.015);
$appLogger->logRequest('POST', '/api/orders', 500, 2.350);

// Log error
try {
    throw new RuntimeException('Database connection timeout');
} catch (\Throwable $e) {
    $appLogger->logError($e, ['query' => 'SELECT * FROM users']);
}

// Log performance
$appLogger->logPerformance('page_load_time', 1.25);
$appLogger->logPerformance('db_query_time', 0.45);
$appLogger->logPerformance('api_response_time', 0.32);

echo "\n";


// ============================================
// BAGIAN I: LOG AGGREGATION
// ============================================

echo "--- 11. LOG AGGREGATION ---\n\n";

class LogAggregator
{
    private array $loggers = [];

    public function addLogger(string $name, Logger $logger): self
    {
        $this->loggers[$name] = $logger;
        return $this;
    }

    public function info(string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->info($message, $context);
        }
    }

    public function error(string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger) {
            $logger->error($message, $context);
        }
    }

    public function getLoggerNames(): array
    {
        return array_keys($this->loggers);
    }
}

$appLog = new Logger('app');
$appLog->pushHandler(new FileLogger("$logDir/app_aggregate.log"));

$securityLog = new Logger('security');
$securityLog->pushHandler(new FileLogger("$logDir/security.log"));

$aggregator = new LogAggregator();
$aggregator->addLogger('app', $appLog);
$aggregator->addLogger('security', $securityLog);

echo "  Aggregator loggers: " . implode(', ', $aggregator->getLoggerNames()) . "\n";

$aggregator->info('User logged in', ['user_id' => 123]);
$aggregator->error('Security breach attempt', ['ip' => '10.0.0.1']);

// Cleanup temp files
$files = glob($logDir . '/*');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
rmdir($logDir);

$rotatingFiles = glob($rotatingDir . '/*');
foreach ($rotatingFiles as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}
rmdir($rotatingDir);

echo "\n";


echo "==========================================\n";
echo "  RINGKASAN\n";
echo "==========================================\n";
echo "\n";
echo "PSR-3 LOGGABLE INTERFACE:\n";
echo "  - emergency, alert, critical, error\n";
echo "  - warning, notice, info, debug\n";
echo "  - Context placeholders: {key}\n";
echo "\n";
echo "LOG PATTERNS:\n";
echo "  - Multiple handlers: Console + File\n";
echo "  - Context & Extra data\n";
echo "  - Processors: Transform records\n";
echo "  - Rotating files: Size-based rotation\n";
echo "  - Buffered logging: Batch writes\n";
echo "  - Decorators: Add timing/metrics\n";
echo "\n";
echo "PRACTICAL:\n";
echo "  - Request logging (method, URI, status, duration)\n";
echo "  - Error logging (exception details, stack trace)\n";
echo "  - Performance logging (metrics)\n";
echo "  - Log aggregation (multiple channels)\n";
echo "\n";

echo "Selesai!\n";
