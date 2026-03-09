<?php

declare(strict_types=1);

namespace Tests\Core;

use App\Core\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        // Define constants needed by Router if not already defined
        if (!defined('APP_PATH')) {
            define('APP_PATH', BASE_PATH . '/app');
        }

        $this->router = new Router();
    }

    public function testStaticGetRouteDispatches(): void
    {
        $called = false;
        $this->router->get('/', function () use (&$called) {
            $called = true;
        });

        ob_start();
        $this->router->dispatch('/', 'GET');
        ob_end_clean();

        $this->assertTrue($called);
    }

    public function testStaticPostRouteDispatches(): void
    {
        $called = false;
        $this->router->post('/login', function () use (&$called) {
            $called = true;
        });

        ob_start();
        $this->router->dispatch('/login', 'POST');
        ob_end_clean();

        $this->assertTrue($called);
    }

    public function testDynamicRoutePassesParams(): void
    {
        $captured = null;
        $this->router->get('/user/{id}', function (string $id) use (&$captured) {
            $captured = $id;
        });

        ob_start();
        $this->router->dispatch('/user/42', 'GET');
        ob_end_clean();

        $this->assertSame('42', $captured);
    }

    public function testDynamicRouteWithMultipleParams(): void
    {
        $capturedA = null;
        $capturedB = null;

        $this->router->get('/post/{slug}/comment/{id}', function (string $slug, string $id) use (&$capturedA, &$capturedB) {
            $capturedA = $slug;
            $capturedB = $id;
        });

        ob_start();
        $this->router->dispatch('/post/hello-world/comment/7', 'GET');
        ob_end_clean();

        $this->assertSame('hello-world', $capturedA);
        $this->assertSame('7', $capturedB);
    }

    public function testTrailingSlashNormalized(): void
    {
        $called = false;
        $this->router->get('/about', function () use (&$called) {
            $called = true;
        });

        ob_start();
        $this->router->dispatch('/about/', 'GET');
        ob_end_clean();

        $this->assertTrue($called);
    }

    public function testUnknownRouteReturns404(): void
    {
        ob_start();
        $this->router->dispatch('/does-not-exist', 'GET');
        ob_end_clean();

        $this->assertSame(404, http_response_code());
    }
}
