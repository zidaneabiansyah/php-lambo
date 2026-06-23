<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $query;
    private array $body;
    private array $params = [];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $this->query = $_GET;
        $this->body = $_POST;
    }

    public static function simulate(string $method, string $uri, array $query = [], array $body = []): self
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

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function except(string ...$keys): array
    {
        $data = $this->all();
        foreach ($keys as $key) {
            unset($data[$key]);
        }
        return $data;
    }

    public function only(string ...$keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            if (isset($this->body[$key]) || isset($this->query[$key])) {
                $data[$key] = $this->input($key);
            }
        }
        return $data;
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }

    public function isMethod(string $method): bool
    {
        return strtoupper($this->method) === strtoupper($method);
    }

    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    public function methodField(): string
    {
        return $this->body['_method'] ?? $this->method;
    }
}
