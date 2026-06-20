<?php

class UploadedFile
{
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly string $tmpName,
        public readonly int $error,
        public readonly int $size,
    ) {}

    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    public function getExtension(): string
    {
        return strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    public function getClientFilename(): string
    {
        return $this->name;
    }

    public function getClientMediaType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function getErrorMessage(): string
    {
        return match ($this->error) {
            UPLOAD_ERR_OK => 'Tidak ada error',
            UPLOAD_ERR_INI_SIZE => 'File melebihi upload_max_filesize di php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File melebihi MAX_FILE_SIZE di form',
            UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ada',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Ekstensi PHP menghentikan upload',
            default => 'Unknown error',
        };
    }

    public function getStream()
    {
        return fopen($this->tmpName, 'r');
    }

    public function moveTo(string $destination): bool
    {
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return move_uploaded_file($this->tmpName, $destination);
    }

    public function store(string $directory, ?string $name = null): string
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filename = $name ?? $this->generateFilename();
        $path = rtrim($directory, '/') . '/' . $filename;
        $this->moveTo($path);
        return $path;
    }

    public function storeAs(string $directory, string $name): string
    {
        return $this->store($directory, $name);
    }

    public function generateFilename(): string
    {
        $ext = $this->getExtension();
        $random = bin2hex(random_bytes(16));
        return $random . ($ext ? '.' . $ext : '');
    }
}

class FileUploadHandler
{
    private array $allowedMimes = [];
    private int $maxSize = 2097152;
    private array $errors = [];

    public function allowedMimes(array $mimes): self
    {
        $this->allowedMimes = $mimes;
        return $this;
    }

    public function maxSize(int $bytes): self
    {
        $this->maxSize = $bytes;
        return $this;
    }

    public function process(string $key): ?UploadedFile
    {
        if (!isset($_FILES[$key])) {
            $this->errors[] = "Field '$key' tidak ditemukan";
            return null;
        }

        $file = $_FILES[$key];

        if (is_array($file['name'])) {
            $this->errors[] = "Gunakan processMultiple() untuk multiple files";
            return null;
        }

        $uploaded = new UploadedFile(
            name: $file['name'],
            type: $file['type'],
            tmpName: $file['tmp_name'],
            error: $file['error'],
            size: $file['size'],
        );

        if (!$uploaded->isValid()) {
            $this->errors[] = $uploaded->getErrorMessage();
            return null;
        }

        if (!empty($this->allowedMimes)) {
            $ext = $uploaded->getExtension();
            if (!in_array($ext, $this->allowedMimes)) {
                $this->errors[] = "Tipe file tidak diizinkan: .$ext. Diizinkan: " . implode(', ', $this->allowedMimes);
                return null;
            }
        }

        if ($uploaded->getSize() > $this->maxSize) {
            $maxMb = round($this->maxSize / 1048576, 2);
            $this->errors[] = "File terlalu besar. Maksimal {$maxMb}MB";
            return null;
        }

        return $uploaded;
    }

    public function processMultiple(string $key): array
    {
        if (!isset($_FILES[$key]) || !is_array($_FILES[$key]['name'])) {
            $this->errors[] = "Field '$key' tidak ditemukan atau bukan multiple files";
            return [];
        }

        $files = [];
        $fileCount = count($_FILES[$key]['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES[$key]['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

            $uploaded = new UploadedFile(
                name: $_FILES[$key]['name'][$i],
                type: $_FILES[$key]['type'][$i],
                tmpName: $_FILES[$key]['tmp_name'][$i],
                error: $_FILES[$key]['error'][$i],
                size: $_FILES[$key]['size'][$i],
            );

            if ($uploaded->isValid()) {
                $files[] = $uploaded;
            } else {
                $this->errors[] = "File {$uploaded->getClientFilename()}: {$uploaded->getErrorMessage()}";
            }
        }

        return $files;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}

class ImageValidator
{
    private const IMAGE_MIMES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];

    public static function isImage(string $path): bool
    {
        $info = @getimagesize($path);
        return $info !== false;
    }

    public static function getDimensions(string $path): ?array
    {
        $info = @getimagesize($path);
        if ($info === false) return null;

        return [
            'width' => $info[0],
            'height' => $info[1],
            'type' => $info[2],
            'mime' => $info['mime'],
        ];
    }

    public static function validateDimensions(string $path, int $maxWidth, int $maxHeight): bool
    {
        $dim = self::getDimensions($path);
        if ($dim === null) return false;
        return $dim['width'] <= $maxWidth && $dim['height'] <= $maxHeight;
    }

    public static function validMimes(): array
    {
        return self::IMAGE_MIMES;
    }
}
