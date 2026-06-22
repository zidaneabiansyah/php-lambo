<?php

interface Notification
{
    public function send(string $message): string;
}

class EmailNotification implements Notification
{
    public function send(string $message): string
    {
        return "Email sent: $message";
    }
}

class SMSNotification implements Notification
{
    public function send(string $message): string
    {
        return "SMS sent: $message";
    }
}

class PushNotification implements Notification
{
    public function send(string $message): string
    {
        return "Push sent: $message";
    }
}

class NotificationFactory
{
    public static function create(string $type): Notification
    {
        return match ($type) {
            'email' => new EmailNotification(),
            'sms' => new SMSNotification(),
            'push' => new PushNotification(),
            default => throw new \InvalidArgumentException("Unknown type: $type"),
        };
    }
}
