<?php

namespace App\Core;

class View
{
    private static string $basePath = '';

    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/') . '/';
    }

    public static function render(string $view, array $data = []): string
    {
        $content = self::partial($view, $data);
        $layout = self::partial('layout', array_merge($data, ['content' => $content]));
        return $layout;
    }

    public static function partial(string $view, array $data = []): string
    {
        $file = self::$basePath . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            return "<!-- View not found: $view -->";
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }
}
