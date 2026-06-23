<?php

namespace App\Core;

abstract class Controller
{
    protected function json(mixed $data, int $status = 200): Response
    {
        $response = new Response();
        $response->status($status)->json($data);
        return $response;
    }

    protected function view(string $view, array $data = []): Response
    {
        $response = new Response();
        $response->setBody(View::render($view, $data));
        return $response;
    }

    protected function redirect(string $url): Response
    {
        $response = new Response();
        $response->redirect($url);
        return $response;
    }

    protected function notFound(): Response
    {
        $response = new Response();
        $response->status(404);
        $response->setBody(View::render('errors/404'));
        return $response;
    }
}
