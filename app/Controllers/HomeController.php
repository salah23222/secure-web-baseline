<?php

declare(strict_types=1);

namespace App\Controllers;

/**
 * Public-facing pages: landing page and health check endpoint.
 */
class HomeController
{
    public function index(): void
    {
        view('home/index');
    }

    /** JSON health check — useful for monitoring. */
    public function health(): void
    {
        header('Content-Type: application/json');
        echo json_encode([
            'status'    => 'ok',
            'session'   => session_status() === PHP_SESSION_ACTIVE,
            'php'       => PHP_VERSION,
            'timestamp' => date('c'),
        ]);
    }
}
