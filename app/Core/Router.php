<?php
namespace App\Core;

class Router {
    private $routes = [];

    public function get($path, $callback) {
        $this->addRoute('GET', $path, $callback);
    }

    public function post($path, $callback) {
        $this->addRoute('POST', $path, $callback);
    }

    private function addRoute($method, $path, $callback) {
        // Convert route params like {id} to regex
        $pathRegex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $path);
        $pathRegex = "#^" . $pathRegex . "$#";
        
        $this->routes[] = [
            'method' => $method,
            'path' => $pathRegex,
            'callback' => $callback
        ];
    }

    public function resolve() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if not running at domain root
        // Assumption: running at /news_page/public/
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }
        if (empty($uri)) {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['path'], $uri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                
                $callback = $route['callback'];
                if (is_array($callback)) {
                    $controller = new $callback[0]();
                    $callback[0] = $controller;
                }
                
                return call_user_func_array($callback, array_values($params));
            }
        }

        // 404
        http_response_code(404);
        echo "404 Not Found";
    }
}
