<?php

namespace App;

class Helpers
{
    public static function formatRupiah(int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    public static function generateSlug(string $text): string
    {
        $text = preg_replace('/[^a-zA-Z0-9\s-]/', '', $text);
        $text = strtolower(trim($text));
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    public static function truncate(string $text, int $length = 100): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . '...';
    }

    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return $email;

        $name = $parts[0];
        $domain = $parts[1];

        $masked = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
        return $masked . '@' . $domain;
    }

    public static function randomString(int $length = 16): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }
        return $result;
    }

    public static function timeAgo(string $datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) return 'baru saja';
        if ($diff < 3600) return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400) return floor($diff / 3600) . ' jam lalu';
        if ($diff < 2592000) return floor($diff / 86400) . ' hari lalu';
        return date('d M Y', $timestamp);
    }
}
