<?php

namespace App\Core;

class View
{
    private static string $basePath = '';
    private static array $shared = [];

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/') . '/';
    }

    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = [], string $layout = 'layout'): string
    {
        $content = self::renderPartial($view, $data);

        if ($layout) {
            $layoutData = array_merge(self::$shared, $data, ['content' => $content]);
            return self::renderPartial($layout, $layoutData);
        }

        return $content;
    }

    public static function renderPartial(string $view, array $data = []): string
    {
        $file = self::$basePath . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            return "View not found: $view ($file)";
        }

        extract(array_merge(self::$shared, $data), EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    public static function exists(string $view): bool
    {
        $file = self::$basePath . str_replace('.', '/', $view) . '.php';
        return file_exists($file);
    }
}
