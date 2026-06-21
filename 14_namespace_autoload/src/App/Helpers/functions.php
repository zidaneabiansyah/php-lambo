<?php

namespace App\Helpers;

function formatRupiah(int $amount): string
{
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function now(): string
{
    return (new \DateTime())->format('Y-m-d H:i:s');
}

function slugify(string $text): string
{
    $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
    $text = strtolower(trim($text));
    $text = preg_replace('/\s+/', '-', $text);
    return $text;
}
