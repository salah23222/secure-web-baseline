<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\CSRF;
use App\Core\Validator;
use App\Core\RateLimiter;
use App\Core\AuditLogger;
use App\Models\User;

/**
 * Handles registration, login, and logout.
 * Includes rate limiting, account lockout, and audit logging.
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
        // ── IP Rate Limit ────────────────────────────────────────────
        if (RateLimiter::tooManyAttempts('register')) {
            AuditLogger::rateLimitHit('register');
            $retry = RateLimiter::retryAfter('register');
            Session::flash('error', "Too many attempts. Please wait {$retry} seconds.");
            redirect('/register');
        }
        RateLimiter::hit('register');

        // ── Validation ───────────────────────────────────────────────
        $v = Validator::make($_POST)
            ->required('name', 'Name')
            ->maxLength('name', 150, 'Name')
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('password', 'Password')
            ->minLength('password', 8, 'Password');

        if ($v->fails()) {
            AuditLogger::registerFailed($_POST['email'] ?? '', $v->firstError() ?? '');
            Session::flash('error', $v->firstError());
            Session::flash('old_name', $_POST['name'] ?? '');
            Session::flash('old_email', $_POST['email'] ?? '');
            redirect('/register');
        }

        $email = trim($_POST['email']);

        if (User::findByEmail($email)) {
            // Use a generic message to avoid user enumeration
            AuditLogger::registerFailed($email, 'email_already_exists');
            Session::flash('error', 'Unable to create account with the provided details.');
            Session::flash('old_name', $_POST['name'] ?? '');
            redirect('/register');
        }

        $userId = (int) User::create([
            'name'          => trim($_POST['name']),
            'email'         => $email,
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role'          => 'user',
        ]);

        AuditLogger::registerSuccess($email, $userId);
        RateLimiter::clear('register');

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

        // ── IP Rate Limit ────────────────────────────────────────────
        if (RateLimiter::tooManyAttempts('login')) {
            AuditLogger::rateLimitHit('login');
            $retry = RateLimiter::retryAfter('login');
            Session::flash('error', "Too many login attempts. Please wait {$retry} seconds.");
            redirect('/login');
        }

        // ── Account Lockout ──────────────────────────────────────────
        if (RateLimiter::isAccountLocked($email)) {
            AuditLogger::loginFailed($email, 'account_locked');
            $remaining = RateLimiter::lockoutEndsIn($email);
            $minutes   = (int) ceil($remaining / 60);
            Session::flash('error', "This account is temporarily locked. Try again in {$minutes} minute(s).");
            redirect('/login');
        }

        // ── Credential check ─────────────────────────────────────────
        $user = User::findByEmail($email);

        // Always run password_verify to prevent timing side-channel on email existence.
        $hash  = $user['password_hash'] ?? '$2y$10$abcdefghijklmnopqrstuuABCDEFGHIJKLMNOPQRSTUVWXYZ01234';
        $valid = password_verify($password, $hash) && $user !== null;

        if (!$valid) {
            RateLimiter::hit('login');
            RateLimiter::recordFailedLogin($email);

            AuditLogger::loginFailed($email, 'invalid_credentials');

            // Warn user if account just got locked
            if (RateLimiter::isAccountLocked($email)) {
                AuditLogger::accountLocked($email);
                Session::flash('error', 'Too many failed attempts. Account locked for 15 minutes.');
            } else {
                Session::flash('error', 'Invalid email or password.');
            }

            redirect('/login');
        }

        // ── Success ──────────────────────────────────────────────────
        RateLimiter::clear('login');
        RateLimiter::clearFailedLogins($email);

        // Prevent session fixation
        session_regenerate_id(true);

        Session::set('user_id', (int) $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);

        AuditLogger::loginSuccess($email, (int) $user['id']);

        CSRF::regenerate();
        redirect('/dashboard');
    }

    public function logout(): void
    {
        // Log before destroying session
        if (Session::has('user_id')) {
            AuditLogger::logout(
                (int) Session::get('user_id'),
                (string) Session::get('user_email', '')
            );
        }

        Session::destroy();
        // Start a fresh session so flash messages work on the login page
        Session::start();
        Session::flash('success', 'You have been logged out.');
        redirect('/login');
    }
}
