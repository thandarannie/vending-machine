<?php

// A small router: matches "METHOD + URL" to a controller method.
class Router
{
    // every route: ['method' => 'GET', 'path' => '/products/{id}/edit', 'handler' => [$controller, 'edit'], 'access' => 'admin']
    private array $routes = [];

    // ---------- 1. register routes ----------

    public function get(string $path, array $handler, ?string $access = null): void
    {
        $this->add('GET', $path, $handler, $access);
    }

    public function post(string $path, array $handler, ?string $access = null): void
    {
        $this->add('POST', $path, $handler, $access);
    }

    // PUT and DELETE are used by the REST API
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

    // read the #[Route(...)] attributes written above a controller's methods
    public function addAttributeRoutes(object $controller): void
    {
        $class = new ReflectionClass($controller);

        foreach ($class->getMethods() as $method) {
            foreach ($method->getAttributes(Route::class) as $attribute) {
                $route = $attribute->newInstance(); // the Route object: method, path, access

                $this->add($route->method, $route->path, [$controller, $method->getName()], $route->access);
            }
        }
    }

    // ---------- 2. find the route for this request and run it ----------

    public function dispatch(string $method, string $path): void
    {
        foreach ($this->routes as $route) {
            // turn the route into a regex:
            //   {id}   -> (\d+)           numbers only:  5
            //   {slug} -> ([a-z0-9-]+)    a name:        coke-zero
            // '/products/{id}/{slug}/purchase' -> '#^/products/(\d+)/([a-z0-9-]+)/purchase$#'
            $pattern = str_replace(['{id}', '{slug}'], ['(\d+)', '([a-z0-9-]+)'], $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if ($route['method'] === $method && preg_match($pattern, $path, $matches)) {
                array_shift($matches); // $matches[0] is the whole URL, we only want the {id} / {slug} values

                // '5' -> 5 (number), 'coke' stays a string
                $params = array_map(fn ($value) => is_numeric($value) ? (int) $value : $value, $matches);

                $this->checkAccess($route['access']);

                // call e.g. $productsController->edit(5)
                call_user_func_array($route['handler'], $params);
                return;
            }
        }

        // no route matched
        http_response_code(404);

        if (str_starts_with($path, '/api/')) { // API callers get JSON, not HTML
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Endpoint not found.']);
            return;
        }

        echo '<h1>404 - Page not found</h1><a href="' . url('/products') . '">Back to products</a>';
    }

    // ---------- 3. role-based access (checks the role stored in the session) ----------

    private function checkAccess(?string $access): void
    {
        if ($access === null) {
            return; // public page
        }

        // guest -> go to login
        if (!Auth::check()) {
            $_SESSION['flash'] = 'Please log in first.';
            header('Location: ' . url('/login'));
            exit;
        }

        // normal user on an admin page -> back to products
        if ($access === 'admin' && !Auth::isAdmin()) {
            $_SESSION['flash'] = 'Only admins can manage products.';
            header('Location: ' . url('/products'));
            exit;
        }
    }
}
