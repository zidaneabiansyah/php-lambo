<?php

namespace App\Services;

class EmailService
{
    private int $sentCount = 0;
    private array $providers = [
        'smtp' => 'SMTP',
        'mailgun' => 'Mailgun API',
        'sendgrid' => 'SendGrid API',
    ];

    public function __construct(
        private string $provider = 'smtp',
        private array $config = [],
    ) {}

    public function send(string $to, string $subject, string $body): bool
    {
        $from = $this->config['from'] ?? 'noreply@example.com';
        $providerName = $this->providers[$this->provider] ?? $this->provider;

        echo "[EmailService] Sending via $providerName\n";
        echo "  From: $from\n";
        echo "  To: $to\n";
        echo "  Subject: $subject\n";
        echo "  Body: " . substr($body, 0, 50) . "...\n";

        $this->sentCount++;
        return true;
    }

    public function sendBatch(array $recipients, string $subject, string $body): int
    {
        $success = 0;
        foreach ($recipients as $recipient) {
            if ($this->send($recipient, $subject, $body)) {
                $success++;
            }
        }
        return $success;
    }

    public function getSentCount(): int
    {
        return $this->sentCount;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function switchProvider(string $provider): void
    {
        if (isset($this->providers[$provider])) {
            $this->provider = $provider;
        }
    }
}
