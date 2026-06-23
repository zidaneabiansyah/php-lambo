<?php

namespace App\Middleware;

use App\Core\Session;
use App\Core\Request;
use App\Core\Response;

class AuthMiddleware
{
    public static function handle(): void
    {
        if (!Session::has('user_id')) {
            Session::flash('error', 'Silakan login terlebih dahulu');
            header('Location: /login');
            exit;
        }
    }

    public static function guest(): void
    {
        if (Session::has('user_id')) {
            header('Location: /');
            exit;
        }
    }
}
