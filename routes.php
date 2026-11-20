<?php

$router->get('/',                      [$products, 'index']);
$router->get('/products',              [$products, 'index']);
$router->get('/products/create',       [$products, 'create'],      'admin');
$router->post('/products',             [$products, 'store'],       'admin');
$router->get('/products/{id}/edit',    [$products, 'edit'],        'admin');
$router->post('/products/{id}',        [$products, 'update'],      'admin');
$router->post('/products/{id}/delete', [$products, 'delete'],      'admin');

$router->addAttributeRoutes($products);

$router->get('/users',                 [$users, 'index'],          'admin');
$router->get('/users/create',          [$users, 'create'],         'admin');
$router->post('/users',                [$users, 'store'],          'admin');
$router->get('/users/{id}/edit',       [$users, 'edit'],           'admin');
$router->post('/users/{id}',           [$users, 'update'],         'admin');
$router->post('/users/{id}/delete',    [$users, 'delete'],         'admin');

$router->get('/transactions',          [$history, 'index'],        'login');

$router->get('/login',                 [$auth, 'showLogin']);
$router->post('/login',                [$auth, 'login']);
$router->post('/logout',               [$auth, 'logout']);

$router->post('/api/login',                       [$authApi, 'login']);
$router->get('/api/me',                           [$authApi, 'me']);

$router->get('/api/products',                     [$productsApi, 'index']);
$router->get('/api/products/{id}',                [$productsApi, 'show']);
$router->post('/api/products',                    [$productsApi, 'store']);
$router->put('/api/products/{id}',                [$productsApi, 'update']);
$router->delete('/api/products/{id}',             [$productsApi, 'destroy']);
$router->post('/api/products/{id}/purchase',      [$productsApi, 'purchase']);

$router->get('/api/transactions',                 [$historyApi, 'index']);
