<?php

// Autoload dependencies and classes
require __DIR__ . '/../vendor/autoload.php';

use App\RouteProvider;
use App\ServiceProvider;
use Framework\Kernel;
use Framework\Request;

$config = array(
    'APP_ENV'    => getenv('APP_ENV')    ?: 'development',
    'VIEWS_PATH' => 'app/views',
    'APP_DB_DSN' => getenv('APP_DB_DSN') ?: 'sqlite:' . __DIR__ . '/../database/maestro.sqlite',
    'APP_DB_USER' => getenv('APP_DB_USER') ?: '',
    'APP_DB_PASS' => getenv('APP_DB_PASS') ?: '',
);

// Initialize the Kernel with configuration
$kernel = new Kernel($config);

$kernel->registerServices(new ServiceProvider());

// Define routes
$kernel->registerRoutes(new RouteProvider());

// Get Request data from the global variables
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Extract the path from the URL
$urlPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (!is_string($urlPath)) {
    $urlPath = '/';
}

// Get query (GET) parameters
$queryParams = $_GET;

// Serve the Svelte SPA for all non-API paths (assets are served directly by the web server)
if (!str_starts_with($urlPath, '/api/')) {
    $svelteApp = __DIR__ . '/build/index.html';
    if (file_exists($svelteApp)) {
        readfile($svelteApp);
        exit;
    }
}

// Parse POST data — support both form-encoded and JSON bodies
$postData = $_POST;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $rawBody = file_get_contents('php://input');
    if ($rawBody !== false && $rawBody !== '') {
        $decoded = json_decode($rawBody, true);
        if (is_array($decoded)) {
            /** @var array<string, mixed> $decoded */
            $postData = $decoded;
        }
    }
}

// Create the Request object
$request = new Request($method, $urlPath, $queryParams, $postData);

// Handle the request and get the response
$response = $kernel->handle($request);

// Send the response to the client
$response->echo();
