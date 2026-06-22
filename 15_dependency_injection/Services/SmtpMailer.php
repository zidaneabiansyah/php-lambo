<?php

namespace App\Services;

use App\Contracts\MailerInterface;

class SmtpMailer implements MailerInterface
{
    private array $config;
    private int $sentCount = 0;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(string $to, string $subject, string $body): string
    {
        $this->sentCount++;
        return sprintf(
            "SMTP(%s) -> to:%s subj:%s",
            $this->config['host'] ?? 'localhost',
            $to,
            $subject,
        );
    }

    public function getSentCount(): int
    {
        return $this->sentCount;
    }
}
