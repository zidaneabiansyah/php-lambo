<?php

namespace App\Core;

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\PostController;
use App\Middleware\AuthMiddleware;

class App
{
    private Router $router;

    public function __construct()
    {
        $dbPath = __DIR__ . '/../../database/app.sqlite';
        Database::connect($dbPath);
        Database::runMigrations();

        View::setBasePath(__DIR__ . '/../Views/');

        $this->router = new Router();
        $this->registerRoutes();
    }

    public function run(): void
    {
        $request = new Request();
        $response = $this->router->dispatch($request);
        $response->send();
    }

    public function handle(Request $request): Response
    {
        return $this->router->dispatch($request);
    }

    public function router(): Router
    {
        return $this->router;
    }

    private function registerRoutes(): void
    {
        $this->router->get('/', [HomeController::class, 'index']);

        // Auth
        $this->router->get('/login', [AuthController::class, 'loginForm']);
        $this->router->post('/login', [AuthController::class, 'login']);
        $this->router->get('/register', [AuthController::class, 'registerForm']);
        $this->router->post('/register', [AuthController::class, 'register']);
        $this->router->get('/logout', [AuthController::class, 'logout']);

        // Posts
        $this->router->get('/posts', [PostController::class, 'index']);
        $this->router->get('/posts/create', [PostController::class, 'create']);
        $this->router->post('/posts', [PostController::class, 'store']);
        $this->router->get('/posts/{id}', [PostController::class, 'show']);
        $this->router->get('/posts/{id}/edit', [PostController::class, 'edit']);
        $this->router->post('/posts/{id}', [PostController::class, 'update']);
        $this->router->post('/posts/{id}/delete', [PostController::class, 'destroy']);
    }
}
