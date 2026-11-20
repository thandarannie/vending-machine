<?php

abstract class Controller
{

    protected function render(string $view, array $data): void
    {
        extract($data);

        ob_start();
        require __DIR__ . '/../../views/' . $view . '.php';
        $content = ob_get_clean();

        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        require __DIR__ . '/../../views/layout.php';
    }

    protected function flash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }

    protected function redirect(string $path, int $status = 302): void
    {
        header('Location: ' . url($path), true, $status);
        exit;
    }

    protected function notFound(string $message = 'Page not found.'): never
    {
        http_response_code(404);
        exit($message);
    }
}
