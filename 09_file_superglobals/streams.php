<?php

class StreamDemo
{
    public function phpInputStream(): void
    {
        echo "--- php://input (read raw body) ---\n";
        echo "Biasanya dipakai untuk baca JSON body dari request POST\n";
        $raw = file_get_contents('php://input');
        echo "Raw input: " . ($raw ?: '(kosong, karena CLI)') . "\n";
    }

    public function phpOutput(): void
    {
        echo "--- php://output (write langsung ke response) ---\n";
        file_put_contents('php://output', "Tulis langsung ke output\n");
    }

    public function phpMemory(): string
    {
        echo "--- php://memory / php://temp (in-memory stream) ---\n";
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, "Data di memory, ga perlu file fisik\n");
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        return $content;
    }

    public function phpFilter(string $input): string
    {
        echo "--- Stream Filter ---\n";
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $input);
        rewind($stream);

        stream_filter_append($stream, 'string.toupper');
        $result = stream_get_contents($stream);
        fclose($stream);
        return $result;
    }

    public function compressFilter(string $data): string
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $data);
        rewind($stream);

        stream_filter_append($stream, 'zlib.deflate');
        $compressed = stream_get_contents($stream);
        fclose($stream);
        return base64_encode($compressed);
    }

    public function wrapperDemo(): void
    {
        echo "--- Stream Wrappers ---\n";
        $wrappers = stream_get_wrappers();
        echo "Wrappers tersedia: " . implode(', ', $wrappers) . "\n";

        $filters = stream_get_filters();
        echo "Filters tersedia: " . implode(', ', array_slice($filters, 0, 10)) . "...\n";

        $transports = stream_get_transports();
        echo "Transports: " . implode(', ', array_slice($transports, 0, 5)) . "...\n";
    }
}

class CustomStreamWrapper
{
    private int $position = 0;
    private string $data = '';
    private string $path = '';

    public function stream_open(string $path, string $mode, int $options, ?string &$opened_path): bool
    {
        $this->path = $path;
        $this->position = 0;
        $this->data = '';
        echo "[Stream] Open: $path (mode: $mode)\n";
        return true;
    }

    public function stream_read(int $count): string
    {
        $result = substr($this->data, $this->position, $count);
        $this->position += strlen($result);
        return $result;
    }

    public function stream_write(string $data): int
    {
        $this->data .= $data;
        $this->position += strlen($data);
        return strlen($data);
    }

    public function stream_tell(): int
    {
        return $this->position;
    }

    public function stream_eof(): bool
    {
        return $this->position >= strlen($this->data);
    }

    public function stream_seek(int $offset, int $whence): bool
    {
        switch ($whence) {
            case SEEK_SET:
                $this->position = $offset;
                break;
            case SEEK_CUR:
                $this->position += $offset;
                break;
            case SEEK_END:
                $this->position = strlen($this->data) + $offset;
                break;
        }
        return true;
    }

    public function stream_stat(): array
    {
        return [];
    }

    public function stream_metadata(string $path, string $option, mixed $value): bool
    {
        return true;
    }

    public function stream_close(): void
    {
        echo "[Stream] Close: {$this->path}\n";
    }
}

function registerCustomStream(): void
{
    stream_wrapper_register('custom', CustomStreamWrapper::class);
    echo "Custom stream wrapper 'custom://' telah didaftarkan\n";
}

function lowLevelFileDemo(): void
{
    echo "--- Low Level File Operations ---\n";
    $tmpFile = tempnam(sys_get_temp_dir(), 'php_');

    // fopen / fwrite / fread / fclose
    $handle = fopen($tmpFile, 'w+');
    fwrite($handle, "Baris pertama\n");
    fwrite($handle, "Baris kedua\n");
    fwrite($handle, "Baris ketiga\n");
    rewind($handle);

    echo "Read line by line:\n";
    while (($line = fgets($handle)) !== false) {
        echo "  > " . rtrim($line) . "\n";
    }
    fclose($handle);

    // fseek / ftell
    $handle = fopen($tmpFile, 'r');
    fseek($handle, 6);
    echo "Karakter ke-6: " . fgetc($handle) . "\n";
    echo "Posisi: " . ftell($handle) . "\n";
    fclose($handle);

    unlink($tmpFile);
}

function fileLockDemo(): void
{
    echo "--- File Locking ---\n";
    $tmpFile = tempnam(sys_get_temp_dir(), 'lock_');

    $handle = fopen($tmpFile, 'w');
    if (flock($handle, LOCK_EX | LOCK_NB)) {
        echo "Lock exclusive didapatkan\n";
        fwrite($handle, "Data aman karena di-lock\n");
        flock($handle, LOCK_UN);
        echo "Lock dilepaskan\n";
    } else {
        echo "Gagal mendapatkan lock\n";
    }
    fclose($handle);
    unlink($tmpFile);
}
