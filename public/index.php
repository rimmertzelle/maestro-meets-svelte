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
    'APP_DB_DSN' => getenv('APP_DB_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=maestro;charset=utf8mb4',
    'APP_DB_USER' => getenv('APP_DB_USER') ?: 'maestro',
    'APP_DB_PASS' => getenv('APP_DB_PASS') ?: 'secret',
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

// Get POST data
$postData = $_POST;

// Create the Request object
$request = new Request($method, $urlPath, $queryParams, $postData);

// Handle the request and get the response
$response = $kernel->handle($request);

// Send the response to the client
$response->echo();
