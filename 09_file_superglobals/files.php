<?php

class FileManager
{
    private string $baseDir;

    public function __construct(?string $baseDir = null)
    {
        $this->baseDir = $baseDir ?? sys_get_temp_dir() . '/php_learning';
        if (!is_dir($this->baseDir)) {
            mkdir($this->baseDir, 0777, true);
        }
    }

    public function write(string $path, string $content): int
    {
        $fullPath = $this->resolve($path);
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return file_put_contents($fullPath, $content);
    }

    public function read(string $path): string|false
    {
        $fullPath = $this->resolve($path);
        if (!file_exists($fullPath)) {
            return false;
        }
        return file_get_contents($fullPath);
    }

    public function append(string $path, string $content): int
    {
        $fullPath = $this->resolve($path);
        return file_put_contents($fullPath, $content, FILE_APPEND | LOCK_EX);
    }

    public function delete(string $path): bool
    {
        $fullPath = $this->resolve($path);
        if (!file_exists($fullPath)) return false;
        return unlink($fullPath);
    }

    public function copy(string $source, string $dest): bool
    {
        return copy($this->resolve($source), $this->resolve($dest));
    }

    public function rename(string $old, string $new): bool
    {
        return rename($this->resolve($old), $this->resolve($new));
    }

    public function exists(string $path): bool
    {
        return file_exists($this->resolve($path));
    }

    public function size(string $path): int|false
    {
        return filesize($this->resolve($path));
    }

    public function info(string $path): array
    {
        $full = $this->resolve($path);
        return [
            'dirname' => pathinfo($full, PATHINFO_DIRNAME),
            'basename' => pathinfo($full, PATHINFO_BASENAME),
            'filename' => pathinfo($full, PATHINFO_FILENAME),
            'extension' => pathinfo($full, PATHINFO_EXTENSION),
            'size' => file_exists($full) ? filesize($full) : 0,
            'modified' => file_exists($full) ? date('Y-m-d H:i:s', filemtime($full)) : null,
            'permissions' => file_exists($full) ? substr(sprintf('%o', fileperms($full)), -4) : null,
            'type' => file_exists($full) ? filetype($full) : null,
            'mime' => file_exists($full) ? mime_content_type($full) : null,
        ];
    }

    public function list(string $dir = ''): array
    {
        $full = $this->resolve($dir);
        if (!is_dir($full)) return [];
        $items = scandir($full);
        $result = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $full . '/' . $item;
            $result[] = [
                'name' => $item,
                'type' => is_dir($path) ? 'dir' : 'file',
                'size' => is_file($path) ? filesize($path) : 0,
                'modified' => date('Y-m-d H:i:s', filemtime($path)),
            ];
        }
        return $result;
    }

    public function walk(string $dir = ''): Generator
    {
        $full = $this->resolve($dir);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($full, RecursiveDirectoryIterator::SKIP_DOTS),
        );
        foreach ($iterator as $file) {
            yield [
                'path' => $file->getPathname(),
                'size' => $file->getSize(),
                'modified' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }
    }

    public function makeDir(string $path, int $permissions = 0777): bool
    {
        return mkdir($this->resolve($path), $permissions, true);
    }

    public function removeDir(string $path): bool
    {
        $full = $this->resolve($path);
        if (!is_dir($full)) return false;
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($full, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        return rmdir($full);
    }

    public function getBaseDir(): string
    {
        return $this->baseDir;
    }

    private function resolve(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }
        return $this->baseDir . '/' . ltrim($path, '/');
    }
}

class CsvHandler
{
    public static function read(string $path, bool $header = true): array
    {
        $rows = [];
        $handle = fopen($path, 'r');
        if ($handle === false) return [];

        if ($header) {
            $headers = fgetcsv($handle);
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = array_combine($headers, $data);
            }
        } else {
            while (($data = fgetcsv($handle)) !== false) {
                $rows[] = $data;
            }
        }
        fclose($handle);
        return $rows;
    }

    public static function write(string $path, array $data, bool $header = true): int
    {
        $handle = fopen($path, 'w');
        if ($handle === false) return 0;

        $written = 0;
        if ($header && !empty($data)) {
            fputcsv($handle, array_keys($data[0]));
            $written++;
        }
        foreach ($data as $row) {
            fputcsv($handle, $row);
            $written++;
        }
        fclose($handle);
        return $written;
    }
}

class IniConfig
{
    private array $data = [];

    public function __construct(?string $path = null)
    {
        if ($path && file_exists($path)) {
            $this->data = parse_ini_file($path, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (str_contains($key, '.')) {
            [$section, $item] = explode('.', $key, 2);
            return $this->data[$section][$item] ?? $default;
        }
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        if (str_contains($key, '.')) {
            [$section, $item] = explode('.', $key, 2);
            $this->data[$section][$item] = $value;
        } else {
            $this->data[$key] = $value;
        }
    }

    public function save(string $path): int
    {
        $content = '';
        foreach ($this->data as $key => $value) {
            if (is_array($value)) {
                $content .= "[$key]\n";
                foreach ($value as $k => $v) {
                    $content .= "$k = " . (is_numeric($v) ? $v : "\"$v\"") . "\n";
                }
            } else {
                $content .= "$key = " . (is_numeric($value) ? $value : "\"$value\"") . "\n";
            }
            $content .= "\n";
        }
        return file_put_contents($path, $content);
    }
}
