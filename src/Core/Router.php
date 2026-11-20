<?php

class Router
{

    private array $routes = [];

    public function get(string $path, array $handler, ?string $access = null): void
    {
        $this->add('GET', $path, $handler, $access);
    }

    public function post(string $path, array $handler, ?string $access = null): void
    {
        $this->add('POST', $path, $handler, $access);
    }

    public function put(string $path, array $handler, ?string $access = null): void
    {
        $this->add('PUT', $path, $handler, $access);
    }

    public function delete(string $path, array $handler, ?string $access = null): void
    {
        $this->add('DELETE', $path, $handler, $access);
    }

    public function add(string $method, string $path, array $handler, ?string $access = null): void
    {
        $this->routes[] = [
            'method'  => $method,
            'path'    => $path,
            'handler' => $handler,
            'access'  => $access,
        ];
    }

    public function addAttributeRoutes(object $controller): void
    {
        $class = new ReflectionClass($controller);

        foreach ($class->getMethods() as $method) {
            foreach ($method->getAttributes(Route::class) as $attribute) {
                $route = $attribute->newInstance();

                $this->add($route->method, $route->path, [$controller, $method->getName()], $route->access);
            }
        }
    }

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {

            $pattern = str_replace(['{id}', '{slug}'], ['(\d+)', '([a-z0-9-]+)'], $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                array_shift($matches);

                $params = array_map(fn ($value) => is_numeric($value) ? (int) $value : $value, $matches);

                $this->checkAccess($route['access']);

                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        http_response_code(404);

        if (str_starts_with($path, '/api/')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Endpoint not found.']);
            return;
        }

        echo '<h1>404 - Page not found</h1><a href="' . url('/products') . '">Back to products</a>';
    }

    private function checkAccess(?string $access): void
    {
        if ($access === null) {
            return;
        }

        if (!Auth::check()) {
            $_SESSION['flash'] = 'Please log in first.';
            header('Location: ' . url('/login'));
            exit;
        }

        if ($access === 'admin' && !Auth::isAdmin()) {
            $_SESSION['flash'] = 'Only admins can manage products.';
            header('Location: ' . url('/products'));
            exit;
        }
    }
}
