<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Models\Post;

class HomeController
{
    public function index(Request $request): Response
    {
        $posts = Post::latest(5);

        $body = View::partial('home', [
            'posts' => $posts,
        ]);

        $html = View::partial('layout', [
            'title' => 'Home - BlogApp',
            'content' => $body,
        ]);

        $response = new Response();
        $response->setBody($html);
        return $response;
    }
}
