<?php

class ValidationRule
{
    public function __construct(
        public readonly string $rule,
        public readonly mixed $parameter = null,
        public readonly ?string $message = null,
    ) {}
}

class Validator
{
    private array $errors = [];
    private array $data = [];
    private array $rules = [];
    private array $customMessages = [];

    private static array $defaultMessages = [
        'required' => ':field wajib diisi',
        'email' => ':field harus format email valid',
        'min' => ':field minimal :param karakter',
        'max' => ':field maksimal :param karakter',
        'min_value' => ':field minimal :param',
        'max_value' => ':field maksimal :param',
        'numeric' => ':field harus angka',
        'integer' => ':field harus bilangan bulat',
        'alpha' => ':field hanya boleh huruf',
        'alphanumeric' => ':field hanya boleh huruf dan angka',
        'alpha_space' => ':field hanya boleh huruf dan spasi',
        'confirmed' => ':field konfirmasi tidak cocok',
        'in' => ':field harus salah satu dari: :param',
        'not_in' => ':field tidak boleh: :param',
        'url' => ':field harus URL valid',
        'phone' => ':field harus nomor telepon valid',
        'date' => ':field harus tanggal valid',
        'after' => ':field harus setelah :param',
        'before' => ':field harus sebelum :param',
        'regex' => ':field format tidak valid',
        'unique' => ':field sudah digunakan',
        'file' => ':field harus file',
        'image' => ':field harus gambar',
        'mimes' => ':field harus tipe: :param',
        'max_size' => ':field maksimal :param KB',
        'array' => ':field harus array',
        'boolean' => ':field harus boolean',
        'string' => ':field harus teks',
    ];

    public function validate(array $data, array $rules, array $messages = []): array
    {
        $this->data = $data;
        $this->rules = $rules;
        $this->customMessages = $messages;
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $fieldRules = is_string($fieldRules) ? explode('|', $fieldRules) : $fieldRules;
            $value = $this->getValue($field);

            foreach ($fieldRules as $ruleDef) {
                $ruleObj = $this->parseRule($ruleDef);
                $this->validateField($field, $value, $ruleObj);
            }
        }

        return $this->errors;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        $all = $this->allErrors();
        return !empty($all) ? $all[0] : null;
    }

    public function allErrors(): array
    {
        $result = [];
        foreach ($this->errors as $field => $msgs) {
            foreach ($msgs as $msg) {
                $result[] = $msg;
            }
        }
        return $result;
    }

    private function validateField(string $field, mixed $value, ValidationRule $rule): void
    {
        $ruleName = $rule->rule;
        $param = $rule->parameter;

        if ($ruleName === 'required' && $this->isEmpty($value)) {
            $this->addError($field, $ruleName, $param);
            return;
        }

        if ($ruleName !== 'required' && $this->isEmpty($value)) {
            return;
        }

        $valid = match ($ruleName) {
            'required' => !$this->isEmpty($value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            'min' => is_string($value) && strlen($value) >= (int) $param,
            'max' => is_string($value) && strlen($value) <= (int) $param,
            'min_value' => is_numeric($value) && $value >= (float) $param,
            'max_value' => is_numeric($value) && $value <= (float) $param,
            'numeric' => is_numeric($value),
            'integer' => filter_var($value, FILTER_VALIDATE_INT) !== false,
            'alpha' => ctype_alpha($value),
            'alphanumeric' => ctype_alnum($value),
            'alpha_space' => preg_match('/^[a-zA-Z\s]+$/', $value) === 1,
            'confirmed' => $value === ($this->data[$field . '_confirmation'] ?? null),
            'in' => in_array($value, explode(',', $param)),
            'not_in' => !in_array($value, explode(',', $param)),
            'url' => filter_var($value, FILTER_VALIDATE_URL) !== false,
            'phone' => preg_match('/^[+]?[\d\s()-]{7,15}$/', $value) === 1,
            'date' => strtotime($value) !== false,
            'after' => strtotime($value) > strtotime($param),
            'before' => strtotime($value) < strtotime($param),
            'regex' => preg_match($param, $value) === 1,
            'boolean' => in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true),
            'string' => is_string($value),
            'array' => is_array($value),
            default => true,
        };

        if (!$valid) {
            $this->addError($field, $ruleName, $param);
        }
    }

    private function parseRule(string|ValidationRule $rule): ValidationRule
    {
        if ($rule instanceof ValidationRule) {
            return $rule;
        }

        if (str_contains($rule, ':')) {
            [$name, $param] = explode(':', $rule, 2);
            return new ValidationRule($name, $param);
        }

        return new ValidationRule($rule);
    }

    private function addError(string $field, string $rule, mixed $param): void
    {
        $message = $this->customMessages["$field.$rule"]
            ?? $this->customMessages[$field]
            ?? self::$defaultMessages[$rule]
            ?? ":field tidak valid";

        $message = str_replace(
            [':field', ':param', ':value'],
            [$field, $param ?? '', $this->getValue($field) ?? ''],
            $message,
        );

        $this->errors[$field][] = $message;
    }

    private function getValue(string $key): mixed
    {
        $keys = explode('.', $key);
        $value = $this->data;
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return null;
            }
            $value = $value[$k];
        }
        return $value;
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null) return true;
        if (is_string($value) && trim($value) === '') return true;
        if (is_array($value) && empty($value)) return true;
        return false;
    }
}

class Sanitizer
{
    public static function trim(array $data, array $fields = []): array
    {
        if (empty($fields)) {
            return array_map(fn($v) => is_string($v) ? trim($v) : $v, $data);
        }
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = trim($data[$field]);
            }
        }
        return $data;
    }

    public static function stripTags(array $data, array $fields = []): array
    {
        if (empty($fields)) {
            return array_map(fn($v) => is_string($v) ? strip_tags($v) : $v, $data);
        }
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = strip_tags($data[$field]);
            }
        }
        return $data;
    }

    public static function escape(array $data, array $fields = []): array
    {
        if (empty($fields)) {
            return array_map(fn($v) => is_string($v) ? htmlspecialchars($v, ENT_QUOTES) : $v, $data);
        }
        foreach ($fields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = htmlspecialchars($data[$field], ENT_QUOTES);
            }
        }
        return $data;
    }

    public static function sanitizeEmail(string $email): string
    {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }

    public static function sanitizeUrl(string $url): string
    {
        return filter_var($url, FILTER_SANITIZE_URL);
    }

    public static function sanitizeInt(mixed $value): int
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function sanitizeFloat(mixed $value): float
    {
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
}

class FormData
{
    private array $original = [];
    private array $cleaned = [];

    public function __construct(array $data)
    {
        $this->original = $data;
        $this->cleaned = $data;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cleaned[$key] ?? $this->original[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->cleaned;
    }

    public function only(string ...$keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $this->cleaned)) {
                $result[$key] = $this->cleaned[$key];
            }
        }
        return $result;
    }

    public function except(string ...$keys): array
    {
        $result = $this->cleaned;
        foreach ($keys as $key) {
            unset($result[$key]);
        }
        return $result;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->original);
    }

    public function filled(string $key): bool
    {
        return $this->has($key) && !empty($this->original[$key]);
    }

    public function merge(array $data): void
    {
        $this->cleaned = array_merge($this->cleaned, $data);
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }
}
