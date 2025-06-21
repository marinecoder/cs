<?php

class Router {
    private array $routes = [];
    private string $basePath;
    
    public function __construct(string $basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }
    
    public function get(string $path, callable $handler): void {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post(string $path, callable $handler): void {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put(string $path, callable $handler): void {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete(string $path, callable $handler): void {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    public function any(string $path, callable $handler): void {
        $this->addRoute('*', $path, $handler);
    }
    
    private function addRoute(string $method, string $path, callable $handler): void {
        $path = $this->basePath . '/' . ltrim($path, '/');
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'pattern' => $this->createPattern($path)
        ];
    }
    
    private function createPattern(string $path): string {
        // Convert route parameters like {id} to regex patterns
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '([^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];
        
        // Remove query string
        $uri = strtok($uri, '?');
        
        // Remove base path if it exists
        if($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        $uri = '/' . ltrim($uri, '/');
        
        foreach($this->routes as $route) {
            if($route['method'] === '*' || $route['method'] === $method) {
                if(preg_match($route['pattern'], $uri, $matches)) {
                    // Remove the full match
                    array_shift($matches);
                    
                    // Call the handler with matched parameters
                    call_user_func_array($route['handler'], $matches);
                    return;
                }
            }
        }
        
        // No route matched
        $this->handle404();
    }
    
    private function handle404(): void {
        http_response_code(404);
        
        if(file_exists(__DIR__ . '/../views/errors/404.php')) {
            include __DIR__ . '/../views/errors/404.php';
        } else {
            echo '<h1>404 - Page Not Found</h1>';
        }
    }
    
    public static function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
    }
    
    public static function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public static function render(string $view, array $data = []): void {
        extract($data);
        $viewPath = __DIR__ . "/../views/{$view}.php";
        
        if(file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new Exception("View not found: {$view}");
        }
    }
    
    public static function renderWithLayout(string $view, array $data = [], string $layout = 'app'): void {
        $viewPath = __DIR__ . "/../views/{$view}.php";
        $layoutPath = __DIR__ . "/../views/layouts/{$layout}.php";
        
        if(!file_exists($viewPath)) {
            throw new Exception("View not found: {$view}");
        }
        
        if(!file_exists($layoutPath)) {
            throw new Exception("Layout not found: {$layout}");
        }
        
        // Extract data for views
        extract($data);
        
        // Start output buffering to capture view content
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        
        // Include layout with content
        include $layoutPath;
    }
    
    public static function getCurrentUrl(): string {
        return $_SERVER['REQUEST_URI'];
    }
    
    public static function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public static function isAjax(): bool {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
