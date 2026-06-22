<?php

namespace App\Providers;

use App\Container;
use App\Contracts\LoggerInterface;
use App\Contracts\MailerInterface;
use App\Services\FileLogger;
use App\Services\SmtpMailer;

class AppServiceProvider
{
    public function register(Container $container): void
    {
        $container->bind(LoggerInterface::class, FileLogger::class);
        $container->singleton(MailerInterface::class, function ($c) {
            return new SmtpMailer([
                'host' => 'smtp.example.com',
                'port' => 587,
                'username' => 'noreply@example.com',
            ]);
        });
    }
}
