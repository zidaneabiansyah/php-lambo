<?php

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    public function resource(string $name, string $controller): void
    {
        $this->get("/$name", [$controller, 'index']);
        $this->get("/$name/create", [$controller, 'create']);
        $this->post("/$name", [$controller, 'store']);
        $this->get("/$name/{id}", [$controller, 'show']);
        $this->get("/$name/{id}/edit", [$controller, 'edit']);
        $this->post("/$name/{id}", [$controller, 'update']);
        $this->post("/$name/{id}/delete", [$controller, 'destroy']);
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->isPost() ? $request->methodField() : $request->method();
        $uri = '/' . trim($request->uri(), '/');

        foreach ($this->routes as $route) {
            $params = $this->match($route, $method, $uri);
            if ($params !== null) {
                $request->setParams($params);
                return $this->run($route, $request);
            }
        }

        return $this->notFound($request);
    }

    public function list(): array
    {
        return array_map(fn($r) => $r['method'] . ' ' . $r['path'], $this->routes);
    }

    private function add(string $method, string $path, array $handler): void
    {
        $path = '/' . trim($path, '/');
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
        ];
    }

    private function match(array $route, string $method, string $uri): ?array
    {
        if ($route['method'] !== $method && $route['method'] !== 'ANY') {
            return null;
        }

        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return null;
    }

    private function run(array $route, Request $request): Response
    {
        [$class, $action] = $route['handler'];
        $controller = new $class();

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action $action not found in $class");
        }

        return $controller->$action($request);
    }

    private function notFound(Request $request): Response
    {
        $response = new Response();
        $response->status(404);
        $response->json([
            'error' => 'Route not found',
            'method' => $request->method(),
            'uri' => $request->uri(),
        ]);
        return $response;
    }
}
