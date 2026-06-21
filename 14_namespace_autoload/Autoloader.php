<?php

class Autoloader
{
    private array $prefixes = [];
    private array $classMap = [];

    public function addNamespace(string $prefix, string $baseDir): void
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';
        $this->prefixes[$prefix] = $baseDir;
    }

    public function addClassMap(array $map): void
    {
        $this->classMap = array_merge($this->classMap, $map);
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }

    public function unregister(): void
    {
        spl_autoload_unregister([$this, 'loadClass']);
    }

    public function loadClass(string $class): ?string
    {
        if (isset($this->classMap[$class])) {
            $file = $this->classMap[$class];
            if (file_exists($file)) {
                require_once $file;
                return $file;
            }
        }

        $prefix = $class;
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);

            $mappedFile = $this->loadMappedFile($prefix, $relativeClass);
            if ($mappedFile) {
                return $mappedFile;
            }

            $prefix = rtrim($prefix, '\\');
        }

        return null;
    }

    private function loadMappedFile(string $prefix, string $relativeClass): ?string
    {
        if (!isset($this->prefixes[$prefix])) {
            return null;
        }

        $baseDir = $this->prefixes[$prefix];
        $file = $baseDir
            . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass)
            . '.php';

        if (file_exists($file)) {
            require_once $file;
            return $file;
        }

        return null;
    }

    public function findFile(string $class): ?string
    {
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        $prefix = $class;
        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relativeClass = substr($class, $pos + 1);

            if (isset($this->prefixes[$prefix])) {
                $file = $this->prefixes[$prefix]
                    . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass)
                    . '.php';
                if (file_exists($file)) {
                    return $file;
                }
            }

            $prefix = rtrim($prefix, '\\');
        }

        return null;
    }

    public function getLoadedClasses(): array
    {
        $classes = [];
        foreach (get_declared_classes() as $class) {
            $reflector = new ReflectionClass($class);
            $file = $reflector->getFileName();
            if ($file && str_contains($file, '14_namespace_autoload')) {
                $classes[] = $class;
            }
        }
        return $classes;
    }
}
