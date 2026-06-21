<?php

class Router
{
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function put(string $path, callable $handler): void
    {
        $this->routes['PUT'][$path] = $handler;
    }

    public function patch(string $path, callable $handler): void
    {
        $this->routes['PATCH'][$path] = $handler;
    }

    public function delete(string $path, callable $handler): void
    {
        $this->routes['DELETE'][$path] = $handler;
    }

    public function resource(string $base, callable $handler): void
    {
        $this->get("$base", fn($p) => $handler('index', $p));
        $this->get("$base/{id}", fn($p) => $handler('show', $p));
        $this->post("$base", fn($p) => $handler('store', $p));
        $this->put("$base/{id}", fn($p) => $handler('update', $p));
        $this->delete("$base/{id}", fn($p) => $handler('destroy', $p));
    }

    public function match(string $method, string $uri): array
    {
        $method = strtoupper($method);
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        $routes = $this->routes[$method] ?? [];

        foreach ($routes as $pattern => $handler) {
            $params = $this->matchRoute($pattern, $uri);
            if ($params !== null) {
                return ['handler' => $handler, 'params' => $params];
            }
        }

        return ['handler' => null, 'params' => []];
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $matched = $this->match($method, $uri);

        if ($matched['handler'] === null) {
            return $this->notFound($method, $uri);
        }

        return ($matched['handler'])($matched['params']);
    }

    private function matchRoute(string $pattern, string $uri): ?array
    {
        $pattern = rtrim($pattern, '/') ?: '/';
        if ($pattern === $uri) {
            return [];
        }

        $patternParts = explode('/', trim($pattern, '/'));
        $uriParts = explode('/', trim($uri, '/'));

        if (count($patternParts) !== count($uriParts)) {
            return null;
        }

        $params = [];
        foreach ($patternParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $key = trim($part, '{}');
                $params[$key] = $uriParts[$i];
            } elseif ($part !== $uriParts[$i]) {
                return null;
            }
        }

        return $params;
    }

    private function notFound(string $method, string $uri): array
    {
        return [
            'error' => 'Route not found',
            'method' => $method,
            'uri' => $uri,
            'available' => $this->availableRoutes(),
        ];
    }

    public function availableRoutes(): array
    {
        $routes = [];
        foreach ($this->routes as $method => $paths) {
            foreach ($paths as $path => $handler) {
                $ref = new ReflectionFunction($handler);
                $routes[] = "$method $path";
            }
        }
        return $routes;
    }

    public function list(): string
    {
        $output = "Registered routes:\n";
        foreach ($this->routes as $method => $paths) {
            foreach ($paths as $path => $handler) {
                $output .= "  $method $path\n";
            }
        }
        return $output;
    }
}
