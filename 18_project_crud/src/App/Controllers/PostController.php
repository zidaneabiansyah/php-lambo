<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Core\Validator;
use App\Models\Post;

class PostController
{
    public function index(Request $request): Response
    {
        $posts = Post::all();

        $body = View::partial('posts/index', ['posts' => $posts]);
        $html = View::partial('layout', [
            'title' => 'Posts',
            'content' => $body,
        ]);

        $response = new Response();
        $response->setBody($html);
        return $response;
    }

    public function show(Request $request): Response
    {
        $post = Post::find((int) $request->param('id'));

        if (!$post) {
            $response = new Response();
            $response->status(404);
            $response->setBody(View::partial('layout', [
                'title' => 'Not Found',
                'content' => '<h1>404</h1><p>Post tidak ditemukan</p>',
            ]));
            return $response;
        }

        $body = View::partial('posts/show', ['post' => $post]);
        $html = View::partial('layout', [
            'title' => $post['title'],
            'content' => $body,
        ]);

        $response = new Response();
        $response->setBody($html);
        return $response;
    }

    public function create(Request $request): Response
    {
        $body = View::partial('posts/create');
        $html = View::partial('layout', [
            'title' => 'Buat Post',
            'content' => $body,
        ]);

        $response = new Response();
        $response->setBody($html);
        return $response;
    }

    public function store(Request $request): Response
    {
        $validator = new Validator();
        $valid = $validator->validate($request->all(), [
            'title' => ['required', 'min:3'],
            'content' => ['required', 'min:10'],
        ]);

        if (!$valid) {
            Session::setOld($request->all());
            foreach ($validator->all() as $error) {
                Session::flash('error', $error);
            }
            return $this->redirect('/posts/create');
        }

        Post::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'user_id' => Session::get('user_id'),
        ]);

        Session::flash('success', 'Post berhasil dibuat!');
        return $this->redirect('/posts');
    }

    public function edit(Request $request): Response
    {
        $post = Post::find((int) $request->param('id'));

        if (!$post) {
            $response = new Response();
            $response->status(404);
            return $response;
        }

        $body = View::partial('posts/edit', ['post' => $post]);
        $html = View::partial('layout', [
            'title' => 'Edit Post',
            'content' => $body,
        ]);

        $response = new Response();
        $response->setBody($html);
        return $response;
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');

        $validator = new Validator();
        $valid = $validator->validate($request->all(), [
            'title' => ['required', 'min:3'],
            'content' => ['required', 'min:10'],
        ]);

        if (!$valid) {
            Session::setOld($request->all());
            foreach ($validator->all() as $error) {
                Session::flash('error', $error);
            }
            return $this->redirect("/posts/$id/edit");
        }

        Post::update($id, $request->only('title', 'content'));
        Session::flash('success', 'Post berhasil diupdate!');
        return $this->redirect('/posts');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        Post::delete($id);
        Session::flash('success', 'Post berhasil dihapus!');
        return $this->redirect('/posts');
    }

    private function redirect(string $url): Response
    {
        $response = new Response();
        $response->redirect($url);
        return $response;
    }
}
