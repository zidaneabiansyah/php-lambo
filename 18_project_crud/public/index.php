<?php

// Front Controller - Entry point untuk web server
// Jalankan: php -S localhost:8000 -t public/

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\App;
use App\Core\Session;
use App\Middleware\AuthMiddleware;

Session::start();
Session::ageFlash();

$app = new App();
$app->run();
