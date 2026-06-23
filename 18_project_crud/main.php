<?php

// 18 - FINAL PROJECT: CRUD BLOG
// Topik: Implementasi lengkap MVC + PDO + Auth +
//        Form Validation + Session + Composer

require_once __DIR__ . '/vendor/autoload.php';

use App\Core\App;
use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\Response;

// Inisialisasi database + seed data
$dbPath = __DIR__ . '/database/app.sqlite';
Database::connect($dbPath);
Database::runMigrations();
Database::seed();

echo "FINAL PROJECT: CRUD BLOG\n";
echo "========================================\n\n";

echo "Teknologi yang digunakan:\n";
echo "  - PHP 8.1+ dengan PSR-4 autoload\n";
echo "  - SQLite via PDO (prepared statement)\n";
echo "  - MVC pattern (Router, Controller, Model, View)\n";
echo "  - Session-based authentication\n";
echo "  - Password hashing (bcrypt)\n";
echo "  - Form validation\n";
echo "  - Flash messages & old input\n\n";

echo "Struktur aplikasi:\n";
echo "  public/index.php  - Front controller (entry point)\n";
echo "  src/App/Core/     - Framework core (Router, View, DB, dll)\n";
echo "  src/App/Controllers/ - Controller logic\n";
echo "  src/App/Models/   - Database queries\n";
echo "  src/App/Views/    - HTML templates\n";
echo "  src/App/Middleware/ - Auth middleware\n";
echo "  src/App/Middleware/AuthMiddleware.php\n\n";

echo "Routes:\n";

$app = new App();
foreach ($app->router()->list() as $route) {
    echo "  $route\n";
}

echo "\n";

// Simulasi request flow
echo "SIMULASI REQUEST\n";
echo "========================================\n\n";

function req(App $app, string $method, string $uri, array $body = []): void
{
    $request = Request::simulate($method, $uri, [], $body);
    $response = $app->handle($request);

    $bodyOutput = $response->getBody();
    if (str_contains($bodyOutput, '<!DOCTYPE html>')) {
        preg_match('/<title>(.*?)<\/title>/', $bodyOutput, $m);
        $title = $m[1] ?? 'No title';
        echo "  [$method $uri] -> HTTP {$response->getStatusCode()} | $title\n";
    } else {
        echo "  [$method $uri] -> HTTP {$response->getStatusCode()} | " . substr($bodyOutput, 0, 80) . "\n";
    }
}

echo "--- Guest access ---\n";
req($app, 'GET', '/');
req($app, 'GET', '/posts');
req($app, 'GET', '/posts/1');
req($app, 'GET', '/login');
req($app, 'GET', '/register');

echo "\n--- Register ---\n";
req($app, 'POST', '/register', [
    'name' => 'Citra Dewi',
    'email' => 'citra@test.com',
    'password' => 'citra123',
]);

echo "\n--- Login ---\n";
req($app, 'POST', '/login', [
    'email' => 'budi@test.com',
    'password' => 'admin123',
]);

echo "\n--- Authenticated access ---\n";
$_SESSION['user_id'] = 1;
$_SESSION['user_name'] = 'Budi Santoso';

req($app, 'GET', '/');
req($app, 'GET', '/posts');
req($app, 'GET', '/posts/create');
req($app, 'GET', '/posts/1');

echo "\n--- Create post ---\n";
req($app, 'POST', '/posts', [
    'title' => 'Belajar PHP Itu Menyenangkan',
    'content' => 'Setelah belajar PHP dari dasar sampai MVC, saya bisa membuat aplikasi web sendiri. PDO membuat akses database aman dari SQL injection.',
]);

echo "\n--- Edit post ---\n";
req($app, 'GET', '/posts/1/edit');
req($app, 'POST', '/posts/1', [
    'title' => 'Belajar PHP Itu Menyenangkan (Updated)',
    'content' => 'Updated content untuk post ini.',
]);

echo "\n--- Validation error ---\n";
req($app, 'POST', '/posts', [
    'title' => 'A',
    'content' => 'short',
]);

echo "\n--- Delete post ---\n";
req($app, 'POST', '/posts/2/delete');

echo "\n--- Logout test ---\n";
unset($_SESSION['user_id'], $_SESSION['user_name']);

echo "\n========================================\n";
echo "               STATISTIK\n";
echo "========================================\n";
echo "  Users: " . \App\Models\User::count() . "\n";
echo "  Posts: " . \App\Models\Post::count() . "\n";
echo "  Routes: " . count($app->router()->list()) . "\n";
echo "========================================\n\n";

echo "Cara menjalankan:\n";
echo "  cd 18_project_crud\n";
echo "  composer dump-autoload\n";
echo "  php -S localhost:8000 -t public/\n";
echo "  Buka http://localhost:8000 di browser\n\n";

echo "Selesai! Semua modul PHP sudah dipelajari!\n";
