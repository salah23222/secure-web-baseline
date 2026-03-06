<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Session;

/**
 * Protected dashboard — requires an active user session.
 */
class DashboardController
{
    public function index(): void
    {
        if (!Session::has('user_id')) {
            redirect('/login');
        }

        view('dashboard/index', [
            'user_id'    => Session::get('user_id'),
            'user_name'  => Session::get('user_name'),
            'user_email' => Session::get('user_email'),
            'user_role'  => Session::get('user_role'),
        ]);
    }
}
