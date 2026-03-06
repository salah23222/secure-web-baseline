<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\CSRF;
use App\Core\Validator;
use App\Models\User;

/**
 * Handles registration, login, and logout.
 */
class AuthController
{
    public function showLogin(): void
    {
        if (Session::has('user_id')) {
            redirect('/dashboard');
        }
        view('auth/login');
    }

    public function showRegister(): void
    {
        if (Session::has('user_id')) {
            redirect('/dashboard');
        }
        view('auth/register');
    }

    public function register(): void
    {
        $v = Validator::make($_POST)
            ->required('name', 'Name')
            ->maxLength('name', 150, 'Name')
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('password', 'Password')
            ->minLength('password', 8, 'Password');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            Session::flash('old_name', $_POST['name'] ?? '');
            Session::flash('old_email', $_POST['email'] ?? '');
            redirect('/register');
        }

        $email = trim($_POST['email']);

        if (User::findByEmail($email)) {
            Session::flash('error', 'An account with this email already exists.');
            Session::flash('old_name', $_POST['name'] ?? '');
            redirect('/register');
        }

        User::create([
            'name'          => trim($_POST['name']),
            'email'         => $email,
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role'          => 'user',
        ]);

        CSRF::regenerate();
        Session::flash('success', 'Account created. Please log in.');
        redirect('/login');
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            Session::flash('error', 'Email and password are required.');
            redirect('/login');
        }

        $user = User::findByEmail($email);

        // Always run password_verify to prevent timing side-channel on email existence.
        // When user is not found, verify against a dummy hash so response time is constant.
        $hash = $user['password_hash'] ?? '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234';
        $valid = password_verify($password, $hash) && $user !== null;

        if (!$valid) {
            Session::flash('error', 'Invalid email or password.');
            redirect('/login');
        }

        // Prevent session fixation
        session_regenerate_id(true);

        Session::set('user_id', (int) $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);

        CSRF::regenerate();
        redirect('/dashboard');
    }

    public function logout(): void
    {
        Session::destroy();
        // Start a fresh session so flash messages work on the login page
        Session::start();
        Session::flash('success', 'You have been logged out.');
        redirect('/login');
    }
}
