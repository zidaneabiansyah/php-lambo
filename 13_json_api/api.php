<?php

class FileDb
{
    private string $filePath;
    private array $data;
    private int $nextId;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
        $this->load();
    }

    public function all(): array
    {
        return array_values($this->data);
    }

    public function find(int $id): ?array
    {
        return $this->data[$id] ?? null;
    }

    public function findWhere(string $field, mixed $value): array
    {
        return array_values(array_filter($this->data, fn($item) => ($item[$field] ?? null) === $value));
    }

    public function create(array $item): array
    {
        $id = $this->nextId++;
        $item['id'] = $id;
        $this->data[$id] = $item;
        $this->save();
        return $item;
    }

    public function update(int $id, array $data): ?array
    {
        if (!isset($this->data[$id])) {
            return null;
        }

        $this->data[$id] = array_merge($this->data[$id], $data);
        $this->save();
        return $this->data[$id];
    }

    public function delete(int $id): bool
    {
        if (!isset($this->data[$id])) {
            return false;
        }

        unset($this->data[$id]);
        $this->save();
        return true;
    }

    public function count(): int
    {
        return count($this->data);
    }

    public function paginate(int $page = 1, int $perPage = 10): array
    {
        $all = $this->all();
        $total = count($all);
        $lastPage = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;
        $items = array_slice($all, $offset, $perPage);

        return [
            'data' => $items,
            'meta' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $lastPage,
                'has_more' => $page < $lastPage,
            ],
        ];
    }

    public function search(string $field, string $keyword): array
    {
        return array_values(array_filter($this->data, fn($item) =>
            str_contains(strtolower($item[$field] ?? ''), strtolower($keyword))
        ));
    }

    private function load(): void
    {
        if (file_exists($this->filePath)) {
            $content = file_get_contents($this->filePath);
            $parsed = Json::decode($content);
            $this->data = [];
            $maxId = 0;
            foreach ($parsed as $item) {
                $id = $item['id'] ?? 0;
                $this->data[$id] = $item;
                if ($id > $maxId) $maxId = $id;
            }
            $this->nextId = $maxId + 1;
        } else {
            $this->data = [];
            $this->nextId = 1;
        }
    }

    private function save(): void
    {
        Json::toFile($this->filePath, array_values($this->data));
    }
}

trait HasTimestamps
{
    protected function addTimestamps(array &$data): void
    {
        $now = date('c');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;
    }

    protected function touchTimestamp(array &$data): void
    {
        $data['updated_at'] = date('c');
    }
}

trait HasValidation
{
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $fieldRules) {
            foreach ($fieldRules as $rule) {
                $error = $this->checkRule($field, $data[$field] ?? null, $rule);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }
        return $errors;
    }

    private function checkRule(string $field, mixed $value, string $rule): ?string
    {
        if ($rule === 'required' && empty($value)) {
            return "$field is required";
        }
        if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "$field must be valid email";
        }
        if (str_starts_with($rule, 'min:') && strlen($value) < (int) substr($rule, 4)) {
            return "$field minimum " . substr($rule, 4) . " characters";
        }
        return null;
    }
}

class ApiResource
{
    use HasTimestamps, HasValidation;

    private FileDb $db;
    private array $rules = [];

    public function __construct(string $filePath)
    {
        $this->db = new FileDb($filePath);
    }

    public function setRules(array $rules): void
    {
        $this->rules = $rules;
    }

    public function index(array $params = []): array
    {
        $page = (int) ($params['page'] ?? 1);
        $perPage = (int) ($params['per_page'] ?? 10);
        $search = $params['search'] ?? null;

        if ($search) {
            $items = $this->db->search('title', $search);
            return [
                'data' => $items,
                'meta' => ['total' => count($items), 'search' => $search],
            ];
        }

        return $this->db->paginate($page, $perPage);
    }

    public function show(array $params): ?array
    {
        $id = (int) ($params['id'] ?? 0);
        return $this->db->find($id);
    }

    public function store(array $data): array
    {
        if (!empty($this->rules)) {
            $errors = $this->validate($data, $this->rules);
            if (!empty($errors)) {
                return ['error' => 'Validation failed', 'messages' => $errors];
            }
        }

        $this->addTimestamps($data);
        return $this->db->create($data);
    }

    public function update(array $params, array $data): ?array
    {
        $id = (int) ($params['id'] ?? 0);
        $existing = $this->db->find($id);

        if (!$existing) {
            return null;
        }

        $this->touchTimestamp($data);
        return $this->db->update($id, $data);
    }

    public function destroy(array $params): bool
    {
        $id = (int) ($params['id'] ?? 0);
        return $this->db->delete($id);
    }
}
