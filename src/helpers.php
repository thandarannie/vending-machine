<?php

// Build a link inside this app.
// url('/products/1/edit')  ->  '/vm/products/1/edit'
// (BASE_URL is the folder the app lives in, set in index.php)
function url(string $path = '/'): string
{
    return BASE_URL . $path;
}

// Read a setting from config/config.php: config('jwt_secret')
function config(string $key): mixed
{
    static $config = null;                                   // load the file only once
    $config ??= require __DIR__ . '/../config/config.php';
    return $config[$key] ?? null;
}

// Make a URL-friendly name: 'Coke Zero!' -> 'coke-zero'
function slugify(string $text): string
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
    return $slug !== '' ? $slug : 'product';
}

// purchase path inside the app: /products/1/coke/purchase
function purchase_path(array $product): string
{
    return '/products/' . $product['id'] . '/' . slugify($product['name']) . '/purchase';
}

// SEO-friendly purchase link for HTML: /vm/products/1/coke/purchase
function purchase_url(array $product): string
{
    return url(purchase_path($product));
}
