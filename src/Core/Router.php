<?php

namespace App\Core;

class Router {
    private $routes = [];

    public function get(string $path, $handler): void {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, $handler): void {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler
        ];
    }

    public function dispatch(string $method, string $uri): void {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                $handler = $route['handler'];
                if (is_array($handler)) {
                    $controllerName = $handler[0];
                    $actionName = $handler[1];
                    $controller = new $controllerName();
                    $controller->$actionName();
                    return;
                }
            }
        }

        // 404 Handler
        http_response_code(404);
        View::render('errors/404', ['title' => 'Not Found']);
    }
}
