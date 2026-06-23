<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request): \App\Core\Response
    {
        $users = User::all();

        return $this->view('users/index', [
            'title' => 'Daftar Users',
            'users' => $users,
        ]);
    }

    public function show(Request $request): \App\Core\Response
    {
        $id = (int) $request->param('id');
        $user = User::find($id);

        if (!$user) {
            return $this->notFound();
        }

        return $this->view('users/show', [
            'title' => 'Detail User - ' . $user['name'],
            'user' => $user,
        ]);
    }

    public function create(Request $request): \App\Core\Response
    {
        return $this->view('users/create', [
            'title' => 'Tambah User',
        ]);
    }

    public function store(Request $request): \App\Core\Response
    {
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'role' => $request->input('role', 'user'),
        ];

        $user = User::create($data);

        return $this->view('users/show', [
            'title' => 'User Created - ' . $user['name'],
            'user' => $user,
            'flash' => ['type' => 'success', 'message' => 'User berhasil dibuat!'],
        ]);
    }

    public function edit(Request $request): \App\Core\Response
    {
        $id = (int) $request->param('id');
        $user = User::find($id);

        if (!$user) {
            return $this->notFound();
        }

        return $this->json(['message' => 'Edit page - not implemented yet']);
    }

    public function update(Request $request): \App\Core\Response
    {
        $id = (int) $request->param('id');
        $updated = User::update($id, $request->all());

        if (!$updated) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json(['message' => 'User updated', 'user' => $updated]);
    }

    public function destroy(Request $request): \App\Core\Response
    {
        $id = (int) $request->param('id');
        $deleted = User::delete($id);

        if (!$deleted) {
            return $this->json(['error' => 'User not found'], 404);
        }

        return $this->json(['message' => 'User deleted']);
    }
}
