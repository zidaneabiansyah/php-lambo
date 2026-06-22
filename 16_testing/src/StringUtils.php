<?php

class StringUtils
{
    public static function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^\w\s-]/', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        return trim($text, '-');
    }

    public static function truncate(string $text, int $length, string $suffix = '...'): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }

    public static function toCamelCase(string $text): string
    {
        $words = preg_split('/[\s_-]+/', $text);
        $result = strtolower($words[0]);
        for ($i = 1; $i < count($words); $i++) {
            $result .= ucfirst(strtolower($words[$i]));
        }
        return $result;
    }

    public static function isPalindrome(string $text): bool
    {
        $cleaned = preg_replace('/[^a-z0-9]/i', '', strtolower($text));
        return $cleaned === strrev($cleaned);
    }
}
