<?php

declare(strict_types=1);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('UTC');

// Load Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Set default environment variables if not set
$_ENV['APP_ENV'] = $_ENV['APP_ENV'] ?? 'production';
$_ENV['DB_HOST'] = $_ENV['DB_HOST'] ?? 'mysql';
$_ENV['DB_NAME'] = $_ENV['DB_NAME'] ?? 'movement_ranking';
$_ENV['DB_USER'] = $_ENV['DB_USER'] ?? 'api_user';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASSWORD'] ?? 'api_password';
$_ENV['DB_PORT'] = $_ENV['DB_PORT'] ?? '3306';

use App\Config\Database;
use App\Config\Router;
use App\Providers\ProviderManager;
use App\Providers\RateLimitProvider;
use App\Providers\SecurityHeadersProvider;
use App\Providers\RequestLogProvider;
use App\Utils\HttpStatus;

try {
    // Prepare request data
    $request = [
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'uri' => parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH),
        'query' => $_GET,
        'body' => file_get_contents('php://input'),
        'headers' => getallheaders() ?: [],
        'server' => $_SERVER
    ];

    // Initialize Provider Manager
    $providerManager = new ProviderManager();

    // Register providers (order matters for priority)
    $providerManager->register(new SecurityHeadersProvider());
    $providerManager->register(new RateLimitProvider(
        intval($_ENV['RATE_LIMIT_MAX']) ?? 100,
        intval($_ENV['RATE_LIMIT_WINDOW']) ?? 3600
    ));
    $providerManager->register(new RequestLogProvider());

    // Execute providers
    $providerResponse = $providerManager->execute($request);

    if ($providerResponse !== null) {
        // Provider returned a response, send it and exit
        $responseCode = $providerResponse['error']['code'] ?? HttpStatus::OK;
        http_response_code($responseCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($providerResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    // Initialize database connection
    Database::initialize();

    // Create DI container
    $container = require __DIR__ . '/../app/Config/container.php';

    // Create router and define routes
    $router = new Router();
    $routes = require __DIR__ . '/../app/Config/routes.php';
    $routes($router, $container);

    // Dispatch the request
    $method = $_SERVER['REQUEST_METHOD'];
    $uri    = $_SERVER['REQUEST_URI'];
    $router->dispatch($method, $uri);

} catch (\Exception $e) {
    // Log error
    error_log('Fatal error: ' . $e->getMessage());

    // Send error response
    http_response_code(HttpStatus::INTERNAL_SERVER_ERROR);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => [
            'code' => HttpStatus::INTERNAL_SERVER_ERROR,
            'message' => 'Internal server error'
        ],
        'timestamp' => date('c')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

