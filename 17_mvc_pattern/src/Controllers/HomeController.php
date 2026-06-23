<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;
use App\Models\Post;

class HomeController extends Controller
{
    public function index(Request $request): \App\Core\Response
    {
        $stats = [
            'users' => User::count(),
            'posts' => Post::count(),
        ];

        return $this->view('home', [
            'title' => 'Home - Belajar MVC',
            'stats' => $stats,
        ]);
    }

    public function about(Request $request): \App\Core\Response
    {
        $response = $this->view('home', [
            'title' => 'About - Belajar MVC',
        ]);
        return $response;
    }
}
