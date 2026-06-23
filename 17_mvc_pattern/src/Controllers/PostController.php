<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Post;

class PostController extends Controller
{
    public function index(Request $request): \App\Core\Response
    {
        $posts = Post::all();

        return $this->view('posts/index', [
            'title' => 'Daftar Posts',
            'posts' => $posts,
        ]);
    }

    public function show(Request $request): \App\Core\Response
    {
        $id = (int) $request->param('id');
        $post = Post::find($id);

        if (!$post) {
            return $this->notFound();
        }

        return $this->view('posts/show', [
            'title' => $post['title'],
            'post' => $post,
        ]);
    }

    public function create(Request $request): \App\Core\Response
    {
        return $this->json(['message' => 'Create form - not implemented']);
    }

    public function store(Request $request): \App\Core\Response
    {
        $post = Post::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'user_id' => (int) $request->input('user_id', 1),
            'author' => $request->input('author', 'Anonymous'),
        ]);

        return $this->json(['message' => 'Post created', 'post' => $post], 201);
    }

    public function update(Request $request): \App\Core\Response
    {
        $id = (int) $request->param('id');
        $updated = Post::update($id, $request->all());

        if (!$updated) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        return $this->json(['message' => 'Post updated', 'post' => $updated]);
    }

    public function destroy(Request $request): \App\Core\Response
    {
        $id = (int) $request->param('id');
        $deleted = Post::delete($id);

        if (!$deleted) {
            return $this->json(['error' => 'Post not found'], 404);
        }

        return $this->json(['message' => 'Post deleted']);
    }
}
