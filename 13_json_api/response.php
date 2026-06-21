<?php

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private mixed $body = null;

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function json(mixed $data): self
    {
        $this->header('Content-Type', 'application/json; charset=utf-8');
        $this->body = Json::encode($data);
        return $this;
    }

    public function text(string $text): self
    {
        $this->header('Content-Type', 'text/plain; charset=utf-8');
        $this->body = $text;
        return $this;
    }

    public function html(string $html): self
    {
        $this->header('Content-Type', 'text/html; charset=utf-8');
        $this->body = $html;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }

        echo $this->body;
    }

    public function getBody(): mixed
    {
        return $this->body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public static function ok(mixed $data): self
    {
        return (new self())->status(200)->json($data);
    }

    public static function created(mixed $data): self
    {
        return (new self())->status(201)->json($data);
    }

    public static function noContent(): self
    {
        return (new self())->status(204);
    }

    public static function badRequest(string $message = 'Bad request'): self
    {
        return (new self())->status(400)->json(['error' => $message]);
    }

    public static function unauthorized(string $message = 'Unauthorized'): self
    {
        return (new self())->status(401)->json(['error' => $message]);
    }

    public static function forbidden(string $message = 'Forbidden'): self
    {
        return (new self())->status(403)->json(['error' => $message]);
    }

    public static function notFound(string $message = 'Not found'): self
    {
        return (new self())->status(404)->json(['error' => $message]);
    }

    public static function error(string $message = 'Internal server error', int $code = 500): self
    {
        return (new self())->status($code)->json(['error' => $message]);
    }
}
