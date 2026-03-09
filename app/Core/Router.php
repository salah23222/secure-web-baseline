<?php

declare(strict_types=1);

namespace App\Core;

/**
 * URI-based router supporting static and dynamic (parameterized) routes.
 *
 * Static route:  $router->get('/dashboard', fn() => ...);
 * Dynamic route: $router->get('/user/{id}', fn($id) => ...);
 */
class Router
{
    private array $routes = [];

    public function get(string $uri, callable $action): void
    {
        $this->routes['GET'][$uri] = $action;
    }

    public function post(string $uri, callable $action): void
    {
        $this->routes['POST'][$uri] = $action;
    }

    public function dispatch(string $uri, string $method): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $path !== '/' ? rtrim($path, '/') : '/';

        $routes = $this->routes[$method] ?? [];

        // 1. Try exact static match first (fastest)
        if (isset($routes[$path])) {
            call_user_func($routes[$path]);
            return;
        }

        // 2. Try dynamic pattern match
        foreach ($routes as $pattern => $action) {
            $params = self::match($pattern, $path);
            if ($params !== null) {
                call_user_func_array($action, $params);
                return;
            }
        }

        // 3. Not found
        http_response_code(404);
        if (file_exists(APP_PATH . '/Views/errors/404.php')) {
            require APP_PATH . '/Views/errors/404.php';
        } else {
            echo '404 Not Found';
        }
    }

    /**
     * Convert a route pattern like /user/{id}/edit to a regex
     * and match it against the request path.
     *
     * Returns an array of captured param values, or null on no match.
     */
    private static function match(string $pattern, string $path): ?array
    {
        // Only attempt if the pattern contains a placeholder
        if (!str_contains($pattern, '{')) {
            return null;
        }

        // Build regex: {param} → named capture group
        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches) !== 1) {
            return null;
        }

        array_shift($matches); // Remove full match
        return $matches;
    }
}
