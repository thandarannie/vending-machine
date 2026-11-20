<?php

// Shared helpers for all API controllers: JSON in, JSON out, token check.
abstract class ApiController
{
    // send JSON and stop
    protected function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    // send an error as JSON: {"error": "..."}
    protected function error(string $message, int $status, array $extra = []): never
    {
        $this->json(['error' => $message] + $extra, $status);
    }

    // read the request body: JSON {"name": "Coke"} (or a normal form post)
    protected function input(): array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        return is_array($data) ? $data : $_POST;
    }

    // ---------- Task 17: JWT check ----------
    // reads "Authorization: Bearer <token>" and returns the user from the token.
    // $role = 'admin' -> only admins may continue
    protected function requireUser(?string $role = null): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/^Bearer\s+(\S+)$/i', $header, $match)) {
            $this->error('Missing token. Send the header: Authorization: Bearer <token>', 401);
        }

        $user = JWT::decode($match[1], config('jwt_secret'));
        if (!$user) {
            $this->error('Invalid or expired token. Log in again at POST /api/login.', 401);
        }

        if ($role === 'admin' && $user['role'] !== 'admin') {
            $this->error('Only admins can do this.', 403);
        }

        return $user; // ['sub' => user id, 'username' => ..., 'role' => ...]
    }

    // a product with real number types (the database gives strings like "3.990")
    protected function productJson(array $product): array
    {
        return [
            'id'                 => (int) $product['id'],
            'name'               => $product['name'],
            'price'              => (float) $product['price'],
            'quantity_available' => (int) $product['quantity_available'],
        ];
    }
}
