<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private string $body = '';

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

    public function setBody(string $content): self
    {
        $this->body = $content;
        return $this;
    }

    public function json(mixed $data): self
    {
        $this->header('Content-Type', 'application/json');
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return $this;
    }

    public function redirect(string $url): self
    {
        $this->statusCode = 302;
        $this->header('Location', $url);
        return $this;
    }

    public function back(): self
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        return $this->redirect($referer);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        foreach ($this->headers as $key => $value) {
            header("$key: $value");
        }
        echo $this->body;
    }

    public function toString(): string
    {
        $output = "HTTP Status: {$this->statusCode}\n";
        foreach ($this->headers as $key => $value) {
            $output .= "$key: $value\n";
        }
        $output .= "\n{$this->body}";
        return $output;
    }
}
