<?php

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private string $body = '';

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function setBody(string $content): self
    {
        $this->body = $content;
        return $this;
    }

    public function json(mixed $data): self
    {
        $this->body = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return $this;
    }

    public function redirect(string $url): self
    {
        $this->statusCode = 302;
        $this->body = '';
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        echo $this->body;
    }

    public function toString(): string
    {
        return "[{$this->statusCode}] {$this->body}";
    }
}
