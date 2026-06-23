<?php

// 17 - MVC PATTERN
// Topik: Model-View-Controller, Router, Request,
//        Response, View template, Front Controller

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $relative = substr($class, strlen($prefix));
        $file = __DIR__ . '/src/' . str_replace('\\', '/', $relative) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\PostController;
use App\Models\User;
use App\Models\Post;

// Set view base path
View::setBasePath(__DIR__ . '/src/Views/');

// Seed data
User::seed();
Post::seed();

// ----- 1. APA ITU MVC -----

echo "APA ITU MVC\n";
echo "MVC (Model-View-Controller) adalah pola arsitektur yang memisahkan aplikasi menjadi 3 komponen:\n";
echo "  Model: Mengelola data dan logika bisnis\n";
echo "  View: Menampilkan data ke pengguna (template)\n";
echo "  Controller: Menghubungkan Model dan View\n\n";

echo "Alur request:\n";
echo "  1. Request masuk ke Front Controller (Router)\n";
echo "  2. Router menentukan Controller dan Action\n";
echo "  3. Controller memanggil Model untuk ambil data\n";
echo "  4. Controller passing data ke View\n";
echo "  5. View me-render HTML response\n";
echo "  6. Response dikirim ke browser\n\n";

// ----- 2. ROUTER SETUP -----

echo "ROUTER SETUP\n";

$router = new Router();

$router->get('/', [HomeController::class, 'index']);

$router->resource('users', UserController::class);

$router->get('/posts', [PostController::class, 'index']);
$router->get('/posts/create', [PostController::class, 'create']);
$router->post('/posts', [PostController::class, 'store']);
$router->get('/posts/{id}', [PostController::class, 'show']);
$router->post('/posts/{id}/update', [PostController::class, 'update']);
$router->post('/posts/{id}/delete', [PostController::class, 'destroy']);

echo "Routes registered: " . count($router->listRoutes()) . "\n";
foreach ($router->listRoutes() as $route) {
    echo "  $route\n";
}

echo "\n";

// ----- 3. SIMULATE REQUESTS -----

echo "SIMULATE REQUESTS\n";

function simulateRequest(Router $router, string $method, string $uri, array $query = [], array $body = []): void
{
    echo "\n--- $method $uri ---\n";

    $request = Request::from($method, $uri, $query, $body);
    $response = $router->dispatch($request);

    $body = $response->getBody();
    $status = $response->getStatusCode();

    if (str_contains($body, '<!DOCTYPE html>')) {
        $body = substr($body, 0, 500) . "...\n[HTML truncated]";
    }

    echo "[$status] " . $body . "\n";
}

simulateRequest($router, 'GET', '/');
simulateRequest($router, 'GET', '/users');
simulateRequest($router, 'GET', '/users/1');
simulateRequest($router, 'GET', '/users/2');
simulateRequest($router, 'GET', '/users/99');
simulateRequest($router, 'GET', '/posts');
simulateRequest($router, 'GET', '/posts/1');
simulateRequest($router, 'GET', '/posts/3');

echo "\n";

// ----- 4. POST REQUEST (Create User) -----

echo "POST REQUEST (Create)\n";

simulateRequest($router, 'POST', '/users', [], [
    'name' => 'Dewi Lestari',
    'email' => 'dewi@test.com',
    'password' => 'dewi123',
    'role' => 'user',
]);

simulateRequest($router, 'POST', '/posts', [], [
    'title' => 'MVC Pattern di PHP',
    'content' => 'MVC adalah pola desain yang sangat berguna untuk mengorganisir kode aplikasi.',
    'user_id' => 1,
    'author' => 'Budi Santoso',
]);

echo "\n";

// ----- 5. REQUEST NOT FOUND -----

echo "404 HANDLING\n";

simulateRequest($router, 'GET', '/nonexistent');
simulateRequest($router, 'POST', '/users/abc');

echo "\n";

// ----- 6. MVC DIAGRAM -----

echo "MVC FLOW DIAGRAM\n";
echo "
    Browser
       |
      GET /users/1
       |
    Front Controller (index.php)
       |
    Router
       |
    UserController->show(1)
       |
    +--------+--------+
    |                 |
    Model (User)     View (users/show.php)
    |                 |
    find(1)          render HTML
    |                 |
    +--------+--------+
       |
    HTTP Response (HTML)
       |
    Browser renders page
";

echo "\n";

// ----- 7. PATTERN LAIN DI MVC -----

echo "PATTERN LAIN DALAM MVC\n";

$patterns = [
    'Front Controller' => 'Single entry point (index.php) yang menangani semua request',
    'Active Record' => 'Model merepresentasikan baris database dan berisi method CRUD',
    'Repository' => 'Layer terpisah untuk akses data antara Model dan Controller',
    'Service Layer' => 'Logika bisnis kompleks dipisah ke Service class',
    'ViewModel' => 'Data yang sudah siap tampil, berbeda dengan Model mentah',
    'Middleware' => 'Filter request sebelum mencapai Controller (auth, log, csrf)',
];

foreach ($patterns as $name => $desc) {
    echo "  $name: $desc\n";
}

echo "\nSelesai belajar MVC pattern!\n";
