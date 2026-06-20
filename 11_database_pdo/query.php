<?php

class QueryBuilder
{
    private string $table;
    private array $selects = ['*'];
    private array $wheres = [];
    private array $whereParams = [];
    private ?string $orderBy = null;
    private ?int $limit = null;
    private ?int $offset = null;
    private array $joins = [];
    private array $groups = [];
    private array $havings = [];
    private ?string $distinct = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public static function table(string $table): self
    {
        return new self($table);
    }

    public function select(string|array $columns): self
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function distinct(): self
    {
        $this->distinct = 'DISTINCT';
        return $this;
    }

    public function where(string $column, string $operator = '=', mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = [$column, $operator, $value, 'AND'];
        return $this;
    }

    public function orWhere(string $column, string $operator = '=', mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        $this->wheres[] = [$column, $operator, $value, 'OR'];
        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $this->wheres[] = [$column, 'IN', "($placeholders)", 'AND', $values];
        return $this;
    }

    public function whereNull(string $column): self
    {
        $this->wheres[] = [$column, 'IS NULL', null, 'AND'];
        return $this;
    }

    public function whereNotNull(string $column): self
    {
        $this->wheres[] = [$column, 'IS NOT NULL', null, 'AND'];
        return $this;
    }

    public function whereBetween(string $column, mixed $min, mixed $max): self
    {
        $this->wheres[] = [$column, 'BETWEEN', [$min, $max], 'AND'];
        return $this;
    }

    public function join(string $table, string $first, string $operator = '=', string $second = '', string $type = 'INNER'): self
    {
        $this->joins[] = "$type JOIN $table ON $first $operator $second";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator = '=', string $second = ''): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator = '=', string $second = ''): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "$column $direction";
        return $this;
    }

    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;
        return $this;
    }

    public function groupBy(string|array $columns): self
    {
        $this->groups = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function having(string $column, string $operator, mixed $value): self
    {
        $this->havings[] = [$column, $operator, $value];
        return $this;
    }

    public function get(): array
    {
        [$sql, $params] = $this->buildSelect();
        return Database::fetchAll($sql, $params);
    }

    public function first(): ?array
    {
        $this->limit(1);
        [$sql, $params] = $this->buildSelect();
        return Database::fetch($sql, $params);
    }

    public function find(mixed $id, string $column = 'id'): ?array
    {
        return $this->where($column, '=', $id)->first();
    }

    public function pluck(string $column): array
    {
        $this->selects = [$column];
        $rows = $this->get();
        return array_map(fn($row) => $row[$column], $rows);
    }

    public function count(): int
    {
        $this->selects = ['COUNT(*) as count'];
        $result = $this->first();
        return (int) ($result['count'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function insert(array $data): string
    {
        return Database::insert($this->table, $data);
    }

    public function insertMultiple(array $data): array
    {
        $ids = [];
        foreach ($data as $row) {
            $ids[] = $this->insert($row);
        }
        return $ids;
    }

    public function update(array $data): int
    {
        [$whereClause, $whereParams] = $this->buildWhere();
        return Database::update($this->table, $data, $whereClause, $whereParams);
    }

    public function delete(): int
    {
        [$whereClause, $whereParams] = $this->buildWhere();

        if (empty($whereClause)) {
            throw new RuntimeException("Delete tanpa WHERE tidak diizinkan. Gunakan truncate() untuk hapus semua.");
        }

        return Database::delete($this->table, $whereClause, $whereParams);
    }

    public function truncate(): void
    {
        Database::raw("DELETE FROM {$this->table}");
    }

    public function min(string $column): mixed
    {
        return $this->aggregate('MIN', $column);
    }

    public function max(string $column): mixed
    {
        return $this->aggregate('MAX', $column);
    }

    public function sum(string $column): mixed
    {
        return $this->aggregate('SUM', $column);
    }

    public function avg(string $column): mixed
    {
        return $this->aggregate('AVG', $column);
    }

    private function aggregate(string $function, string $column): mixed
    {
        $this->selects = ["$function($column) as result"];
        $result = $this->first();
        return $result['result'] ?? null;
    }

    public function toSql(): string
    {
        [$sql] = $this->buildSelect();
        return $sql;
    }

    public function getBindings(): array
    {
        [, $params] = $this->buildSelect();
        return $params;
    }

    private function buildSelect(): array
    {
        $params = [];

        $select = $this->distinct ? "SELECT {$this->distinct}" : 'SELECT';
        $columns = implode(', ', $this->selects);
        $sql = "$select $columns FROM {$this->table}";

        foreach ($this->joins as $join) {
            $sql .= " $join";
        }

        if (!empty($this->wheres)) {
            [$whereClause, $whereParams] = $this->buildWhere();
            $sql .= " WHERE $whereClause";
            $params = array_merge($params, $whereParams);
        }

        if (!empty($this->groups)) {
            $sql .= " GROUP BY " . implode(', ', $this->groups);
        }

        if (!empty($this->havings)) {
            $havingParts = [];
            foreach ($this->havings as [$col, $op, $val]) {
                $havingParts[] = "$col $op ?";
                $params[] = $val;
            }
            $sql .= " HAVING " . implode(' AND ', $havingParts);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return [$sql, $params];
    }

    private function buildWhere(): array
    {
        if (empty($this->wheres)) {
            return ['1=1', []];
        }

        $clauses = [];
        $params = [];

        foreach ($this->wheres as $i => [$col, $op, $val, $boolean]) {
            $prefix = $i === 0 ? '' : " $boolean ";

            if (strtoupper($op) === 'BETWEEN') {
                $clauses[] = "$prefix$col BETWEEN ? AND ?";
                $params[] = $val[0];
                $params[] = $val[1];
            } elseif (strtoupper($op) === 'IN') {
                $clauses[] = "$prefix$col IN $val";
                if (is_array($val)) {
                    $params = array_merge($params, $val);
                } elseif (isset($this->wheres[$i][4])) {
                    $params = array_merge($params, $this->wheres[$i][4]);
                }
            } elseif (strtoupper($op) === 'IS NULL' || strtoupper($op) === 'IS NOT NULL') {
                $clauses[] = "$prefix$col $op";
            } else {
                $clauses[] = "$prefix$col $op ?";
                $params[] = $val;
            }
        }

        return [implode('', $clauses), $params];
    }
}
