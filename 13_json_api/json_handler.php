<?php

class Json
{
    public static function encode(mixed $data, int $options = 0): string
    {
        $flags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | $options;
        $result = json_encode($data, $flags);

        if ($result === false && json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON encode error: ' . json_last_error_msg());
        }

        return $result;
    }

    public static function decode(string $json, bool $associative = true): mixed
    {
        if (empty($json)) {
            return $associative ? [] : null;
        }

        $result = json_decode($json, $associative);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('JSON decode error: ' . json_last_error_msg());
        }

        return $result;
    }

    public static function pretty(mixed $data): string
    {
        return self::encode($data, JSON_PRETTY_PRINT);
    }

    public static function isValid(string $json): bool
    {
        json_decode($json);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function fromFile(string $path): mixed
    {
        if (!file_exists($path)) {
            return [];
        }
        $content = file_get_contents($path);
        return self::decode($content);
    }

    public static function toFile(string $path, mixed $data, int $options = 0): int
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return file_put_contents($path, self::encode($data, $options | JSON_PRETTY_PRINT));
    }
}

class JsonSerializer
{
    public static function serialize(object $object): array
    {
        $data = [];
        $ref = new ReflectionClass($object);

        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            $data[$prop->getName()] = $prop->getValue($object);
        }

        if ($object instanceof JsonSerializable) {
            return $object->jsonSerialize();
        }

        if (method_exists($object, 'toArray')) {
            return $object->toArray();
        }

        if (method_exists($object, 'toJson')) {
            return self::decode($object->toJson());
        }

        return $data;
    }

    public static function collection(array $objects): array
    {
        return array_map(fn($obj) => self::serialize($obj), $objects);
    }
}

trait Jsonable
{
    public function toJson(int $options = 0): string
    {
        return Json::encode($this->toArray(), $options);
    }

    abstract public function toArray(): array;
}

class JsonStream
{
    private $stream;

    public function __construct()
    {
        $this->stream = fopen('php://temp', 'r+');
    }

    public function write(mixed $data): void
    {
        fwrite($this->stream, Json::encode($data) . "\n");
    }

    public function readAll(): array
    {
        rewind($this->stream);
        $items = [];
        while (($line = fgets($this->stream)) !== false) {
            $line = trim($line);
            if (!empty($line)) {
                $items[] = Json::decode($line);
            }
        }
        return $items;
    }

    public function __toString(): string
    {
        rewind($this->stream);
        return stream_get_contents($this->stream);
    }
}

function json_response(mixed $data, int $code = 200, array $headers = []): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    foreach ($headers as $key => $value) {
        header("$key: $value");
    }
    echo Json::encode($data);
}
