<?php

abstract class Model
{
    protected static string $table;
    protected string $primaryKey = 'id';
    protected bool $timestamps = true;
    protected array $fillable = [];
    protected array $guarded = ['id'];
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    public function fill(array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        if ($this->isFillable($name)) {
            $this->attributes[$name] = $value;
        }
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function isDirty(?string $key = null): bool
    {
        if ($key) {
            return ($this->original[$key] ?? null) !== ($this->attributes[$key] ?? null);
        }
        return $this->original !== $this->attributes;
    }

    public function fresh(): ?static
    {
        $key = $this->getKey();
        if (!$key) return null;
        return static::find($key);
    }

    public function save(): bool
    {
        if ($this->exists) {
            return $this->performUpdate();
        }
        return $this->performInsert();
    }

    public function delete(): bool
    {
        $key = $this->getKey();
        if (!$key) return false;

        $deleted = QueryBuilder::table(static::tableName())
            ->where($this->primaryKey, '=', $key)
            ->delete();

        if ($deleted) {
            $this->exists = false;
        }

        return $deleted > 0;
    }

    public static function tableName(): string
    {
        return static::$table ?? strtolower((new \ReflectionClass(static::class))->getShortName()) . 's';
    }

    public static function query(): QueryBuilder
    {
        return QueryBuilder::table(static::tableName());
    }

    public static function all(): array
    {
        $rows = static::query()->get();
        return array_map(fn($row) => static::hydrate($row), $rows);
    }

    public static function find(mixed $id): ?static
    {
        $data = static::query()->find($id);
        if (!$data) return null;
        return static::hydrate($data);
    }

    public static function where(string $column, string $operator = '=', mixed $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    public static function create(array $attributes): static
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    public static function updateOrCreate(array $conditions, array $attributes): static
    {
        $existing = static::query();
        foreach ($conditions as $key => $value) {
            $existing = $existing->where($key, '=', $value);
        }
        $data = $existing->first();

        if ($data) {
            $model = static::hydrate($data);
            $model->fill($attributes);
            $model->save();
            return $model;
        }

        return static::create(array_merge($conditions, $attributes));
    }

    protected static function hydrate(array $data): static
    {
        $model = new static();
        $model->attributes = $data;
        $model->original = $data;
        $model->exists = true;
        return $model;
    }

    protected function performInsert(): bool
    {
        $data = $this->attributes;

        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            if (!isset($data['created_at'])) $data['created_at'] = $now;
            if (!isset($data['updated_at'])) $data['updated_at'] = $now;
        }

        unset($data[$this->primaryKey]);

        $id = static::query()->insert($data);
        $this->attributes[$this->primaryKey] = $id;
        $this->original = $this->attributes;
        $this->exists = true;

        return true;
    }

    protected function performUpdate(): bool
    {
        $data = $this->attributes;

        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        $changes = array_diff_assoc($data, $this->original);
        if (empty($changes)) return true;

        unset($changes[$this->primaryKey]);

        if (empty($changes)) return true;

        static::query()
            ->where($this->primaryKey, '=', $this->getKey())
            ->update($changes);

        $this->original = $this->attributes;
        return true;
    }

    protected function isFillable(string $key): bool
    {
        if (in_array($key, $this->guarded)) return false;
        if (empty($this->fillable)) return true;
        return in_array($key, $this->fillable);
    }
}
