<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $query;
    private array $body;
    private array $headers;
    private array $params;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->query = $_GET;
        $this->body = $_POST;
        $this->params = [];
        $this->headers = $this->parseHeaders();
    }

    public static function from(string $method, string $uri, array $query = [], array $body = []): self
    {
        $req = new self();
        $req->method = strtoupper($method);
        $req->uri = '/' . ltrim($uri, '/');
        $req->query = $query;
        $req->body = $body;
        return $req;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function header(string $key, ?string $default = null): ?string
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function wantsJson(): bool
    {
        $accept = $this->header('Accept');
        return $accept !== null && str_contains($accept, 'application/json');
    }

    public function isAjax(): bool
    {
        $header = $this->header('X-Requested-With');
        return strtolower($header ?? '') === 'xmlhttprequest';
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
        }
        return $headers;
    }
}
