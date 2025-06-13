<?php

namespace App\Config;

class Router
{
    private array $routes = [];

    /**
     * Add a GET route
     * 
     * @param string $pattern
     * @param callable $handler
     */
    public function get(string $pattern, callable $handler): void
    {
        $this->routes['GET'][$pattern] = $handler;
    }

    /**
     * Add a POST route
     *
     * @param string $pattern
     * @param callable $handler
     */
    public function post(string $pattern, callable $handler): void
    {
        $this->routes['POST'][$pattern] = $handler;
    }

    /**
     * Add an OPTIONS route
     * 
     * @param string $pattern
     * @param callable $handler
     */
    public function options(string $pattern, callable $handler): void
    {
        $this->routes['OPTIONS'][$pattern] = $handler;
    }

    /**
     * Dispatch the request
     * 
     * @param string $method
     * @param string $uri
     */
    public function dispatch(string $method, string $uri): void
    {
        // Remove query string from URI
        $uri = parse_url($uri, PHP_URL_PATH);
        
        // Remove trailing slash
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        // Check if method exists
        if (!isset($this->routes[$method])) {
            $this->sendNotFound();
            return;
        }

        // Try to match routes
        foreach ($this->routes[$method] as $pattern => $handler) {
            $matches = $this->matchRoute($pattern, $uri);
            if ($matches !== false) {
                call_user_func_array($handler, $matches);
                return;
            }
        }

        $this->sendNotFound();
    }

    /**
     * Match route pattern against URI
     * 
     * @param string $pattern
     * @param string $uri
     * @return bool|array
     */
    private function matchRoute(string $pattern, string $uri): bool|array
    {
        // Convert pattern to regex
        $regex = preg_replace('/{([^}]+)}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            // Remove the full match
            array_shift($matches);
            return $matches;
        }

        return false;
    }

    /**
     * Send 404 Not Found response
     */
    private function sendNotFound(): void
    {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => 404,
                'message' => 'Endpoint not found'
            ],
            'timestamp' => date('c')
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

