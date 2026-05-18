<?php
/**
 * Application Entry Point
 */

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load environment variables from .env file if exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        [$name, $value] = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define base URL and root path for subdirectory installations (e.g., XAMPP htdocs/dom-uzorov)
define('BASE_URL', '/dom-uzorov'); // Change this to match your folder name in htdocs
define('ROOT_PATH', dirname(__DIR__));

// Get the request URI and strip the base directory if running in a subdirectory
$requestUri = $_SERVER['REQUEST_URI'];
$scriptName = $_SERVER['SCRIPT_NAME'];

// Determine the base path (e.g., /dom-uzorov)
$basePath = '';
if ($scriptName !== '/index.php') {
    $basePath = dirname($scriptName);
    if ($basePath !== '/' && $basePath !== '\\') {
        // Strip the base path from the request URI
        if (strpos($requestUri, $basePath) === 0) {
            $requestUri = substr($requestUri, strlen($basePath));
            if (empty($requestUri)) {
                $requestUri = '/';
            }
        }
    }
}

// Initialize router and dispatch request
try {
    $router = new App\Core\Router();
    $router->dispatch($requestUri, $_SERVER['REQUEST_METHOD']);
} catch (\Exception $e) {
    http_response_code(500);
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    
    if (getenv('APP_DEBUG') === 'true') {
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    }
}
