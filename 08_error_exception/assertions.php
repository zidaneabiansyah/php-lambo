<?php

// ============================================
// Assertions
// ============================================

class Validator
{
    public static function assertNotEmpty(mixed $value, string $field): void
    {
        assert(
            !empty($value),
            "Field '$field' tidak boleh kosong",
        );
    }

    public static function assertEmail(string $email): void
    {
        assert(
            filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
            "Format email tidak valid: $email",
        );
    }

    public static function assertRange(int|float $value, int|float $min, int|float $max, string $field): void
    {
        assert(
            $value >= $min && $value <= $max,
            "Field '$field' harus antara $min dan $max, nilai: $value",
        );
    }

    public static function assertMinLength(string $value, int $min, string $field): void
    {
        assert(
            strlen($value) >= $min,
            "Field '$field' minimal $min karakter, sekarang: " . strlen($value),
        );
    }

    public static function assertMatch(string $value, string $pattern, string $field): void
    {
        assert(
            preg_match($pattern, $value) === 1,
            "Field '$field' tidak sesuai format: $value",
        );
    }

    public static function assertUnique(mixed $value, array $existing, string $field): void
    {
        assert(
            !in_array($value, $existing),
            "Field '$field' harus unik: $value sudah ada",
        );
    }
}

class DataProcessor
{
    public static function process(array $data): array
    {
        assert(!empty($data), 'Data input tidak boleh kosong');

        $existingNames = [];

        foreach ($data as $item) {
            Validator::assertNotEmpty($item['name'] ?? '', 'name');
            Validator::assertEmail($item['email'] ?? '');
            Validator::assertRange($item['age'] ?? 0, 17, 100, 'age');
            Validator::assertMinLength($item['password'] ?? '', 8, 'password');
            Validator::assertUnique($item['name'], $existingNames, 'name');

            $existingNames[] = $item['name'];
        }

        return $data;
    }
}
