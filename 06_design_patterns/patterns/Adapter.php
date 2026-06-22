<?php

interface FileStorage
{
    public function write(string $path, string $content): string;
    public function read(string $path): string;
    public function delete(string $path): bool;
    public function exists(string $path): bool;
}

class LocalFileStorage implements FileStorage
{
    public function write(string $path, string $content): string
    {
        file_put_contents($path, $content);
        return "Local: wrote to $path";
    }

    public function read(string $path): string
    {
        return file_get_contents($path);
    }

    public function delete(string $path): bool
    {
        return unlink($path);
    }

    public function exists(string $path): bool
    {
        return file_exists($path);
    }
}

class CloudStorageAPI
{
    public function upload(string $bucket, string $key, string $data): string
    {
        return "Cloud: uploaded $key to bucket $bucket";
    }

    public function download(string $bucket, string $key): string
    {
        return "content from $key";
    }

    public function remove(string $bucket, string $key): bool
    {
        return true;
    }

    public function has(string $bucket, string $key): bool
    {
        return true;
    }
}

class CloudStorageAdapter implements FileStorage
{
    private CloudStorageAPI $cloud;
    private string $bucket;

    public function __construct(CloudStorageAPI $cloud, string $bucket)
    {
        $this->cloud = $cloud;
        $this->bucket = $bucket;
    }

    public function write(string $path, string $content): string
    {
        return $this->cloud->upload($this->bucket, $path, $content);
    }

    public function read(string $path): string
    {
        return $this->cloud->download($this->bucket, $path);
    }

    public function delete(string $path): bool
    {
        return $this->cloud->remove($this->bucket, $path);
    }

    public function exists(string $path): bool
    {
        return $this->cloud->has($this->bucket, $path);
    }
}
