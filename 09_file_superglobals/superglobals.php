<?php

// Superglobals: Variables built-in yang bisa diakses dari mana saja

class SuperglobalDemo
{
    public function demoServer(): void
    {
        echo "--- \$_SERVER ---\n";
        echo "PHP_SELF: " . ($_SERVER['PHP_SELF'] ?? 'N/A') . "\n";
        echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'N/A') . "\n";
        echo "SERVER_ADDR: " . ($_SERVER['SERVER_ADDR'] ?? 'N/A') . "\n";
        echo "SERVER_PORT: " . ($_SERVER['SERVER_PORT'] ?? 'N/A') . "\n";
        echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A') . "\n";
        echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "\n";
        echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? 'N/A') . "\n";
        echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'N/A') . "\n";
        echo "HTTP_USER_AGENT: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'N/A') . "\n";
        echo "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? 'N/A') . "\n";
    }

    public function demoGet(array $params): void
    {
        echo "\n--- \$_GET (query string) ---\n";
        if (empty($params)) {
            echo "Tidak ada parameter GET\n";
            return;
        }
        foreach ($params as $key => $value) {
            echo "$key: $value\n";
        }
    }

    public function demoPost(array $data): void
    {
        echo "\n--- \$_POST (form data) ---\n";
        if (empty($data)) {
            echo "Tidak ada data POST\n";
            return;
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                echo "$key: " . implode(", ", $value) . "\n";
            } else {
                echo "$key: $value\n";
            }
        }
    }

    public function demoRequest(): void
    {
        echo "\n--- \$_REQUEST (GET + POST + COOKIE) ---\n";
        echo "Menggabungkan semua input request\n";
    }

    public function demoCookie(): void
    {
        echo "\n--- \$_COOKIE ---\n";
        if (empty($_COOKIE)) {
            echo "Tidak ada cookie\n";
        } else {
            foreach ($_COOKIE as $key => $value) {
                echo "$key: $value\n";
            }
        }
    }

    public function demoSession(): void
    {
        echo "\n--- \$_SESSION ---\n";
        if (session_status() === PHP_SESSION_NONE) {
            echo "Session belum di-start\n";
            return;
        }
        foreach ($_SESSION as $key => $value) {
            echo "$key: " . (is_scalar($value) ? $value : json_encode($value)) . "\n";
        }
    }

    public function demoEnv(): void
    {
        echo "\n--- \$_ENV / \$_SERVER (env vars) ---\n";
        $vars = ['HOME', 'USER', 'SHELL', 'PWD', 'PATH'];
        foreach ($vars as $var) {
            $val = $_SERVER[$var] ?? getenv($var) ?? 'N/A';
            echo "$var: $val\n";
        }
    }

    public function demoFiles(array $files): void
    {
        echo "\n--- \$_FILES (upload) ---\n";
        if (empty($files)) {
            echo "Tidak ada file upload\n";
            return;
        }
        foreach ($files as $name => $info) {
            echo "$name:\n";
            echo "  name: {$info['name']}\n";
            echo "  type: {$info['type']}\n";
            echo "  size: {$info['size']} bytes\n";
            echo "  tmp_name: {$info['tmp_name']}\n";
            echo "  error: {$info['error']}\n";
        }
    }

    public function demoGlobals(): void
    {
        echo "\n--- \$GLOBALS ---\n";
        echo "Berisi semua variable global\n";
        echo "Contoh: ada " . count($GLOBALS) . " variable global\n";
    }
}

class Request
{
    private array $query = [];
    private array $body = [];
    private array $cookies = [];
    private array $files = [];
    private array $server = [];
    private array $headers = [];

    public function __construct(array $options = [])
    {
        $this->query = $options['query'] ?? [];
        $this->body = $options['body'] ?? [];
        $this->cookies = $options['cookies'] ?? [];
        $this->files = $options['files'] ?? [];
        $this->server = array_merge($_SERVER, $options['server'] ?? []);

        foreach ($this->server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = str_replace('_', '-', strtolower(substr($key, 5)));
                $this->headers[$header] = $value;
            }
        }
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $this->body[$key] ?? $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    public function header(string $key, ?string $default = null): ?string
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function method(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function isAjax(): bool
    {
        return strtolower($this->header('X-Requested-With') ?? '') === 'xmlhttprequest';
    }

    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $this->server['HTTP_USER_AGENT'] ?? '';
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }
}
