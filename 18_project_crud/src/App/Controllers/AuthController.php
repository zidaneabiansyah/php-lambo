<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Core\Validator;
use App\Models\User;
use App\Models\Post;

class AuthController
{
    public function loginForm(Request $request): Response
    {
        $body = View::partial('auth/login');
        $html = View::partial('layout', [
            'title' => 'Login',
            'content' => $body,
        ]);

        $response = new Response();
        $response->setBody($html);
        return $response;
    }

    public function login(Request $request): Response
    {
        $validator = new Validator();
        $valid = $validator->validate($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!$valid) {
            Session::setOld($request->except('password'));
            foreach ($validator->all() as $error) {
                Session::flash('error', $error);
            }
            return $this->redirect('/login');
        }

        $user = User::authenticate($request->input('email'), $request->input('password'));

        if (!$user) {
            Session::setOld($request->except('password'));
            Session::flash('error', 'Email atau password salah');
            return $this->redirect('/login');
        }

        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::flash('success', 'Selamat datang, ' . $user['name'] . '!');
        return $this->redirect('/');
    }

    public function registerForm(Request $request): Response
    {
        $body = View::partial('auth/register');
        $html = View::partial('layout', [
            'title' => 'Register',
            'content' => $body,
        ]);

        $response = new Response();
        $response->setBody($html);
        return $response;
    }

    public function register(Request $request): Response
    {
        $validator = new Validator();
        $valid = $validator->validate($request->all(), [
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        if (!$valid) {
            Session::setOld($request->except('password'));
            foreach ($validator->all() as $error) {
                Session::flash('error', $error);
            }
            return $this->redirect('/register');
        }

        $existing = User::findByEmail($request->input('email'));
        if ($existing) {
            Session::setOld($request->except('password'));
            Session::flash('error', 'Email sudah terdaftar');
            return $this->redirect('/register');
        }

        $userId = User::create($request->only('name', 'email', 'password'));

        Session::set('user_id', $userId);
        Session::set('user_name', $request->input('name'));
        Session::flash('success', 'Registrasi berhasil!');
        return $this->redirect('/');
    }

    public function logout(Request $request): Response
    {
        Session::destroy();
        Session::flash('success', 'Anda telah logout');
        return $this->redirect('/login');
    }

    private function redirect(string $url): Response
    {
        $response = new Response();
        $response->redirect($url);
        return $response;
    }
}
