<?php

class Schema
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connect();
    }

    public function createTable(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $sql = $blueprint->toSql();
        Database::raw($sql);
        echo "[Schema] Table '$table' created\n";
    }

    public function dropTable(string $table): void
    {
        Database::raw("DROP TABLE IF EXISTS $table");
        echo "[Schema] Table '$table' dropped\n";
    }

    public function hasTable(string $table): bool
    {
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name=" . $this->pdo->quote($table));
        return (bool) $stmt->fetch();
    }

    public function getTables(): array
    {
        $stmt = $this->pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function tableInfo(string $table): array
    {
        $stmt = $this->pdo->query("PRAGMA table_info($table)");
        return $stmt->fetchAll();
    }
}

class Blueprint
{
    private string $table;
    private array $columns = [];
    private array $indexes = [];
    private array $uniques = [];
    private ?string $primaryKey = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): ColumnDef
    {
        $col = new ColumnDef($name, 'INTEGER');
        $col->autoIncrement();
        $this->primaryKey = $name;
        $this->columns[] = $col;
        return $col;
    }

    public function string(string $name, int $length = 255): ColumnDef
    {
        $col = new ColumnDef($name, 'VARCHAR', $length);
        $this->columns[] = $col;
        return $col;
    }

    public function text(string $name): ColumnDef
    {
        $col = new ColumnDef($name, 'TEXT');
        $this->columns[] = $col;
        return $col;
    }

    public function integer(string $name): ColumnDef
    {
        $col = new ColumnDef($name, 'INTEGER');
        $this->columns[] = $col;
        return $col;
    }

    public function float(string $name): ColumnDef
    {
        $col = new ColumnDef($name, 'REAL');
        $this->columns[] = $col;
        return $col;
    }

    public function decimal(string $name, int $precision = 10, int $scale = 2): ColumnDef
    {
        $col = new ColumnDef($name, 'DECIMAL', [$precision, $scale]);
        $this->columns[] = $col;
        return $col;
    }

    public function boolean(string $name): ColumnDef
    {
        $col = new ColumnDef($name, 'INTEGER');
        $col->default(0);
        $this->columns[] = $col;
        return $col;
    }

    public function datetime(string $name): ColumnDef
    {
        $col = new ColumnDef($name, 'TEXT');
        $this->columns[] = $col;
        return $col;
    }

    public function timestamp(string $name): ColumnDef
    {
        return $this->datetime($name);
    }

    public function timestamps(): void
    {
        $this->datetime('created_at')->default('CURRENT_TIMESTAMP');
        $this->datetime('updated_at')->nullable();
    }

    public function softDeletes(): void
    {
        $this->datetime('deleted_at')->nullable();
    }

    public function foreignId(string $name): ColumnDef
    {
        $col = new ColumnDef($name, 'INTEGER');
        $this->columns[] = $col;
        return $col;
    }

    public function unique(string|array $columns): void
    {
        $this->uniques[] = is_array($columns) ? $columns : [$columns];
    }

    public function index(string|array $columns): void
    {
        $this->indexes[] = is_array($columns) ? $columns : [$columns];
    }

    public function toSql(): string
    {
        $parts = [];
        foreach ($this->columns as $col) {
            $parts[] = $col->toSql();
        }

        if ($this->primaryKey) {
            $hasAutoIncrement = false;
            foreach ($this->columns as $col) {
                if ($col->name === $this->primaryKey && $col->hasAutoIncrement) {
                    $hasAutoIncrement = true;
                    break;
                }
            }
            if (!$hasAutoIncrement) {
                $parts[] = "PRIMARY KEY ($this->primaryKey)";
            }
        }

        foreach ($this->uniques as $unique) {
            $cols = implode(', ', $unique);
            $parts[] = "UNIQUE($cols)";
        }

        $columns = implode(",\n  ", $parts);
        return "CREATE TABLE IF NOT EXISTS {$this->table} (\n  $columns\n)";
    }
}

class ColumnDef
{
    public string $name;
    public bool $hasAutoIncrement = false;
    private string $type;
    private mixed $length = null;
    private bool $nullable = false;
    private mixed $default = null;
    private bool $autoIncrement = false;
    private ?string $references = null;
    private ?string $onDelete = null;

    public function __construct(string $name, string $type, mixed $length = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
    }

    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->default = $value;
        return $this;
    }

    public function autoIncrement(): self
    {
        $this->autoIncrement = true;
        $this->hasAutoIncrement = true;
        return $this;
    }

    public function constrained(?string $table = null, ?string $column = 'id'): self
    {
        $refTable = $table ?? preg_replace('/_id$/', 's', $this->name);
        $this->references = "$refTable($column)";
        return $this;
    }

    public function onDelete(string $action): self
    {
        $this->onDelete = $action;
        return $this;
    }

    public function toSql(): string
    {
        $parts = [$this->name, $this->type];

        if ($this->length !== null) {
            $len = is_array($this->length) ? implode(', ', $this->length) : $this->length;
            $parts[1] .= "($len)";
        }

        if ($this->autoIncrement) {
            $parts[] = 'PRIMARY KEY AUTOINCREMENT';
        }

        if (!$this->nullable && !$this->autoIncrement) {
            $parts[] = 'NOT NULL';
        }

        if ($this->default !== null) {
            if ($this->default === 'CURRENT_TIMESTAMP') {
                $parts[] = "DEFAULT CURRENT_TIMESTAMP";
            } elseif (is_string($this->default)) {
                $parts[] = "DEFAULT '{$this->default}'";
            } else {
                $parts[] = "DEFAULT {$this->default}";
            }
        }

        if ($this->references) {
            $parts[] = "REFERENCES {$this->references}";
            if ($this->onDelete) {
                $parts[] = "ON DELETE {$this->onDelete}";
            }
        }

        return implode(' ', $parts);
    }
}
