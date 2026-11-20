<?php

// The real controller sends HTML, sends headers and calls exit.
// That would stop PHPUnit, so in tests we replace those 3 methods
// with versions that only REMEMBER what happened. The tests then check it.
// Everything else (validation, purchase logic ...) is the real code.
class TestProductsController extends ProductsController
{
    public ?array $rendered   = null; // ['view' => 'products/form', 'data' => [...]]
    public ?array $redirected = null; // ['path' => '/products', 'status' => 302]

    protected function render(string $view, array $data): void
    {
        $this->rendered = ['view' => $view, 'data' => $data];
    }

    protected function redirect(string $path, int $status = 302): void
    {
        $this->redirected = ['path' => $path, 'status' => $status];
    }

    // the real one calls exit; here we throw, so the test can catch it
    protected function notFound(string $message = 'Page not found.'): never
    {
        throw new NotFoundException($message);
    }
}

class NotFoundException extends RuntimeException
{
}
