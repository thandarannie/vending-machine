<?php

class TestProductsController extends ProductsController
{
    public ?array $rendered   = null;
    public ?array $redirected = null;

    protected function render(string $view, array $data): void
    {
        $this->rendered = ['view' => $view, 'data' => $data];
    }

    protected function redirect(string $path, int $status = 302): void
    {
        $this->redirected = ['path' => $path, 'status' => $status];
    }

    protected function notFound(string $message = 'Page not found.'): never
    {
        throw new NotFoundException($message);
    }
}

class NotFoundException extends RuntimeException
{
}
