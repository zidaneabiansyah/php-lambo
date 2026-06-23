<?php

namespace App\Core;

class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $label = str_replace('_', ' ', ucfirst($field));

            foreach ($fieldRules as $rule) {
                $error = $this->checkRule($field, $label, $value, $rule);
                if ($error) {
                    $this->errors[$field][] = $error;
                }
            }
        }

        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    public function all(): array
    {
        $result = [];
        foreach ($this->errors as $field => $msgs) {
            foreach ($msgs as $msg) {
                $result[] = $msg;
            }
        }
        return $result;
    }

    private function checkRule(string $field, string $label, mixed $value, string $rule): ?string
    {
        if ($rule === 'required' && ($value === null || trim((string) $value) === '')) {
            return "$label wajib diisi";
        }

        if (($value === null || trim((string) $value) === '') && !str_contains($rule, 'required')) {
            return null;
        }

        if (str_starts_with($rule, 'min:')) {
            $min = (int) substr($rule, 4);
            if (is_string($value) && strlen($value) < $min) {
                return "$label minimal $min karakter";
            }
        }

        if (str_starts_with($rule, 'max:')) {
            $max = (int) substr($rule, 4);
            if (is_string($value) && strlen($value) > $max) {
                return "$label maksimal $max karakter";
            }
        }

        if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "$label harus format email valid";
        }

        if ($rule === 'numeric' && !is_numeric($value)) {
            return "$label harus angka";
        }

        return null;
    }
}
