<?php

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupAttributes = [];
    private array $middleware = [];

    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function put(string $path, array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    public function patch(string $path, array $handler): void
    {
        $this->addRoute('PATCH', $path, $handler);
    }

    public function delete(string $path, array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    public function resource(string $name, string $controller): void
    {
        $this->get("/$name", [$controller, 'index']);
        $this->get("/$name/create", [$controller, 'create']);
        $this->post("/$name", [$controller, 'store']);
        $this->get("/$name/{id}", [$controller, 'show']);
        $this->get("/$name/{id}/edit", [$controller, 'edit']);
        $this->put("/$name/{id}", [$controller, 'update']);
        $this->delete("/$name/{id}", [$controller, 'destroy']);
    }

    public function group(callable $callback, array $attributes = []): void
    {
        $previous = $this->groupAttributes;
        $this->groupAttributes = array_merge($this->groupAttributes, $attributes);
        $callback($this);
        $this->groupAttributes = $previous;
    }

    public function middleware(string $middleware): self
    {
        $this->middleware[] = $middleware;
        return $this;
    }

    public function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->uri();

        foreach ($this->routes as $route) {
            $params = $this->match($route, $method, $uri);
            if ($params !== null) {
                $request->setParams($params);
                return $this->handleRoute($route, $request);
            }
        }

        return $this->handleNotFound($request);
    }

    public function listRoutes(): array
    {
        $list = [];
        foreach ($this->routes as $route) {
            $list[] = $route['method'] . ' ' . $route['path'];
        }
        return $list;
    }

    public function all(): array
    {
        return $this->routes;
    }

    private function addRoute(string $method, string $path, array $handler): void
    {
        $prefix = $this->groupAttributes['prefix'] ?? '';
        $path = $prefix . '/' . ltrim($path, '/');
        $path = '/' . ltrim($path, '/') ?: '/';

        $middleware = array_merge(
            $this->middleware,
            $this->groupAttributes['middleware'] ?? [],
        );
        $this->middleware = [];

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    private function match(array $route, string $method, string $uri): ?array
    {
        if ($route['method'] !== $method) {
            return null;
        }

        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route['path']);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $uri, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }

        return null;
    }

    private function handleRoute(array $route, Request $request): Response
    {
        [$controllerClass, $action] = $route['handler'];

        if (!class_exists($controllerClass)) {
            throw new \RuntimeException("Controller $controllerClass not found");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            throw new \RuntimeException("Action $action not found in $controllerClass");
        }

        foreach ($route['middleware'] as $middleware) {
            if (method_exists($controller, $middleware)) {
                $controller->$middleware($request);
            }
        }

        return $controller->$action($request);
    }

    private function handleNotFound(Request $request): Response
    {
        $response = new Response();
        $response->status(404);
        $response->setBody(View::render('errors/404', [
            'uri' => $request->uri(),
            'method' => $request->method(),
        ]));
        return $response;
    }
}
