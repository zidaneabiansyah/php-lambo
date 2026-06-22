<?php

namespace App\Services;

use App\Contracts\LoggerInterface;
use App\Contracts\MailerInterface;

class UserService
{
    private UserRepository $repo;
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private string $appName;

    public function __construct(
        UserRepository $repo,
        MailerInterface $mailer,
        LoggerInterface $logger,
        string $appName = 'App',
    ) {
        $this->repo = $repo;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->appName = $appName;
    }

    public function registerUser(string $name, string $email): array
    {
        $this->logger->info("Registering $name ($email)");
        $result = $this->mailer->send($email, 'Welcome', "Hi $name, welcome to $this->appName!");
        return [
            'user' => ['name' => $name, 'email' => $email],
            'email_result' => $result,
        ];
    }

    public function listUsers(): array
    {
        return $this->repo->findAll();
    }
}
