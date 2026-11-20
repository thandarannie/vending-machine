<?php

// Shared helpers for all controllers (ProductsController, AuthController).
// "abstract" = you never use this class directly, only extend it.
abstract class Controller
{
    // 1. render the page body into $content
    // 2. put $content inside layout.php (header + flash message)
    protected function render(string $view, array $data): void
    {
        extract($data); // turns ['products' => ...] into $products

        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';
        $content = ob_get_clean();

        // read the flash message once, then remove it
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../views/layout.php';
    }

    // save a one-time message to show on the next page
    protected function flash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }

    // redirect('/products') -> goes to /vm/products
    // $status: 302 = normal redirect, 301 = "moved for good" (for SEO)
    protected function redirect(string $path, int $status = 302): void
    {
        header('Location: ' . url($path), true, $status);
        exit;
    }

    // show a 404 page and stop
    // ("never" = this function never returns, it always stops the script)
    protected function notFound(string $message = 'Page not found.'): never
    {
        http_response_code(404);
        exit($message);
    }
}
