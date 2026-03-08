<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Secure Web Baseline — Front Controller
|--------------------------------------------------------------------------
| All HTTP requests are routed through this file.
*/

require_once __DIR__ . '/../app/Core/bootstrap.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PasswordResetController;

/*
|--------------------------------------------------------------------------
| Create Router
|--------------------------------------------------------------------------
*/
$router = new Router();

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
$router->get('/',       fn() => (new HomeController())->index());
$router->get('/health', fn() => (new HomeController())->health());

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
$router->get('/login',    fn() => (new AuthController())->showLogin());
$router->post('/login',   fn() => (new AuthController())->login());

$router->get('/register',  fn() => (new AuthController())->showRegister());
$router->post('/register', fn() => (new AuthController())->register());

$router->get('/logout', fn() => (new AuthController())->logout());

/*
|--------------------------------------------------------------------------
| Password Reset Routes
|--------------------------------------------------------------------------
*/
$router->get('/forgot-password',  fn() => (new PasswordResetController())->showForgot());
$router->post('/forgot-password', fn() => (new PasswordResetController())->sendResetLink());

$router->get('/reset-password',   fn() => (new PasswordResetController())->showReset());
$router->post('/reset-password',  fn() => (new PasswordResetController())->applyReset());

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/
$router->get('/dashboard', fn() => (new DashboardController())->index());

/*
|--------------------------------------------------------------------------
| Dispatch
|--------------------------------------------------------------------------
*/
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
