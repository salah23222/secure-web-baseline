<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;
use App\Core\CSRF;
use App\Core\Validator;
use App\Core\RateLimiter;
use App\Core\AuditLogger;
use App\Models\User;
use App\Models\PasswordReset;

/**
 * Handles the full password reset flow:
 *  GET  /forgot-password  — show request form
 *  POST /forgot-password  — send reset link
 *  GET  /reset-password   — show new-password form (token in query string)
 *  POST /reset-password   — apply new password
 */
class PasswordResetController
{
    // ── Step 1: Request form ─────────────────────────────────────────

    public function showForgot(): void
    {
        view('auth/forgot-password');
    }

    public function sendResetLink(): void
    {
        // Rate limit: max 5 reset requests per 15 minutes per IP
        if (RateLimiter::tooManyAttempts('forgot')) {
            $retry = RateLimiter::retryAfter('forgot');
            Session::flash('error', "Too many attempts. Please wait {$retry} seconds.");
            redirect('/forgot-password');
        }
        RateLimiter::hit('forgot');

        $email = trim($_POST['email'] ?? '');

        $v = Validator::make(['email' => $email])
            ->required('email', 'Email')
            ->email('email', 'Email');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            redirect('/forgot-password');
        }

        // Always show the same success message regardless of whether
        // the email exists — prevents user enumeration.
        $user = User::findByEmail($email);

        if ($user !== null) {
            $rawToken = PasswordReset::create($email);
            self::sendEmail($email, $user['name'], $rawToken);
            AuditLogger::log('password_reset_requested', ['email' => $email]);
        }

        Session::flash('success', 'If that email exists, a reset link has been sent.');
        redirect('/forgot-password');
    }

    // ── Step 2: Reset form ───────────────────────────────────────────

    public function showReset(): void
    {
        $email = trim($_GET['email'] ?? '');
        $token = trim($_GET['token'] ?? '');

        if ($email === '' || $token === '') {
            Session::flash('error', 'Invalid or missing reset link.');
            redirect('/login');
        }

        // Validate token exists before showing the form
        if (PasswordReset::findValid($email, $token) === null) {
            Session::flash('error', 'This reset link is invalid or has expired.');
            redirect('/forgot-password');
        }

        view('auth/reset-password', ['email' => $email, 'token' => $token]);
    }

    public function applyReset(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $token    = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';

        // Rate limit reset attempts
        if (RateLimiter::tooManyAttempts('reset')) {
            $retry = RateLimiter::retryAfter('reset');
            Session::flash('error', "Too many attempts. Please wait {$retry} seconds.");
            redirect("/reset-password?email=" . urlencode($email) . "&token=" . urlencode($token));
        }
        RateLimiter::hit('reset');

        // Validate inputs
        $v = Validator::make(['password' => $password, 'password_confirmation' => $confirm])
            ->required('password', 'Password')
            ->minLength('password', 8, 'Password')
            ->confirmed('password', 'password_confirmation');

        if ($v->fails()) {
            Session::flash('error', $v->firstError());
            redirect("/reset-password?email=" . urlencode($email) . "&token=" . urlencode($token));
        }

        // Validate token
        $record = PasswordReset::findValid($email, $token);
        if ($record === null) {
            Session::flash('error', 'This reset link is invalid or has expired.');
            redirect('/forgot-password');
        }

        // Find user
        $user = User::findByEmail($email);
        if ($user === null) {
            Session::flash('error', 'This reset link is invalid or has expired.');
            redirect('/forgot-password');
        }

        // Update password
        User::updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));

        // Invalidate the token
        PasswordReset::markUsed((int) $record['id']);
        PasswordReset::purgeExpired();

        // Clear any lockouts
        RateLimiter::clearFailedLogins($email);
        RateLimiter::clear('reset');

        AuditLogger::log('password_reset_success', ['email' => $email, 'user_id' => $user['id']]);

        CSRF::regenerate();
        Session::flash('success', 'Password updated successfully. Please log in.');
        redirect('/login');
    }

    // ── Email sender ─────────────────────────────────────────────────

    /**
     * Send a password reset email.
     *
     * In production, replace this with a proper mailer (PHPMailer, Symfony Mailer, etc.)
     * For now it uses PHP's built-in mail() as a placeholder.
     */
    private static function sendEmail(string $email, string $name, string $rawToken): void
    {
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $link     = "{$scheme}://{$host}/reset-password"
                  . '?email=' . urlencode($email)
                  . '&token=' . urlencode($rawToken);

        $subject = 'Password Reset Request';
        $body    = "Hello {$name},\r\n\r\n"
                 . "We received a request to reset your password.\r\n\r\n"
                 . "Click the link below (valid for 60 minutes):\r\n{$link}\r\n\r\n"
                 . "If you did not request this, ignore this email — your account is safe.\r\n\r\n"
                 . "Secure Web Baseline";

        $headers = "From: no-reply@{$host}\r\nContent-Type: text/plain; charset=UTF-8";

        mail($email, $subject, $body, $headers);
    }
}
