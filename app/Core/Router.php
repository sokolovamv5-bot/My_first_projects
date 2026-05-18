<?php
namespace App\Core;

/**
 * Router Class - Handles URL routing
 */
class Router
{
    private array $routes = [];
    private array $routePatterns = [];

    public function __construct()
    {
        $this->routes = require __DIR__ . '/../../config/routes.php';
        $this->compileRoutes();
    }

    private function compileRoutes(): void
    {
        foreach ($this->routes as $route => $handler) {
            $parts = explode(' ', $route, 2);
            $method = $parts[0];
            $path = $parts[1] ?? '/';
            
            // Convert route parameters to regex pattern
            $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
            $pattern = '#^' . $pattern . '$#';
            
            $this->routePatterns[] = [
                'method' => $method,
                'pattern' => $pattern,
                'handler' => $handler,
                'original_path' => $path
            ];
        }
    }

    public function dispatch(string $uri, string $method): void
    {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        
        foreach ($this->routePatterns as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                $this->callHandler($route['handler'], $params);
                return;
            }
        }
        
        // 404 Not Found
        http_response_code(404);
        $this->callHandler('HomeController@notFound', []);
    }

    private function callHandler(string $handler, array $params): void
    {
        [$controllerName, $methodName] = explode('@', $handler);
        
        // Build full controller class name
        $controllerClass = "App\\Controllers\\{$controllerName}";
        
        // Check for admin controllers
        if (strpos($controllerName, 'Admin\\') === 0) {
            $controllerClass = "App\\Controllers\\Admin\\{$controllerName}";
        }
        
        // Check for API controllers
        if (strpos($controllerName, 'Api\\') === 0) {
            $controllerClass = "App\\Controllers\\Api\\{$controllerName}";
        }
        
        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller not found: {$controllerClass}");
        }
        
        $controller = new $controllerClass();
        
        if (!method_exists($controller, $methodName)) {
            throw new \Exception("Method not found: {$methodName} in {$controllerClass}");
        }
        
        // Call the method with parameters
        call_user_func_array([$controller, $methodName], $params);
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }
}
