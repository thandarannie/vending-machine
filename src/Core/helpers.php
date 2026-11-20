<?php

function url(string $path = '/'): string
{
    return BASE_URL . $path;
}

function config(string $key): mixed
{
    static $config = null;
    $config ??= require __DIR__ . '/../../config/config.php';
    return $config[$key] ?? null;
}

function slugify(string $text): string
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $text), '-'));
    return $slug !== '' ? $slug : 'product';
}

function purchase_path(array $product): string
{
    return '/products/' . $product['id'] . '/' . slugify($product['name']) . '/purchase';
}

function purchase_url(array $product): string
{
    return url(purchase_path($product));
}
