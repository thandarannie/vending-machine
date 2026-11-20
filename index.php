<?php
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

session_start();

// Core: routing, auth, helpers (load first — other classes build on these)
require __DIR__ . '/src/Core/helpers.php';
require __DIR__ . '/src/Core/Auth.php';
require __DIR__ . '/src/Core/JWT.php';
require __DIR__ . '/src/Core/Route.php';
require __DIR__ . '/src/Core/Router.php';

// Repositories: database + data access
require __DIR__ . '/src/Repositories/Database.php';
require __DIR__ . '/src/Repositories/ProductRepository.php';
require __DIR__ . '/src/Repositories/TransactionRepository.php';
require __DIR__ . '/src/Repositories/UserRepository.php';

// Services: business logic
require __DIR__ . '/src/Services/ProductValidator.php';
require __DIR__ . '/src/Services/PurchaseService.php';

// Controllers: base classes first, then web + API controllers
require __DIR__ . '/src/Controllers/Controller.php';
require __DIR__ . '/src/Controllers/ApiController.php';
require __DIR__ . '/src/Controllers/ProductsController.php';
require __DIR__ . '/src/Controllers/AuthController.php';
require __DIR__ . '/src/Controllers/TransactionsController.php';
require __DIR__ . '/src/Controllers/ProductsApiController.php';
require __DIR__ . '/src/Controllers/AuthApiController.php';
require __DIR__ . '/src/Controllers/TransactionsApiController.php';

define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = substr($path, strlen(BASE_URL));
$path = '/' . trim($path, '/');

// API: allow other websites (frontend apps) to call it (CORS)
if (str_starts_with($path, '/api/')) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { // browser "may I?" check before the real request
        http_response_code(204);
        exit;
    }
}

$pdo          = Database::getConnection();
$productRepo  = new ProductRepository($pdo);
$txRepo       = new TransactionRepository($pdo);
$userRepo     = new UserRepository($pdo);

// web pages
$products = new ProductsController($productRepo, $txRepo);
$auth     = new AuthController($userRepo);
$history  = new TransactionsController($txRepo);

// REST API
$productsApi = new ProductsApiController($productRepo, $txRepo);
$authApi     = new AuthApiController($userRepo);
$historyApi  = new TransactionsApiController($txRepo);

$router = new Router();
require __DIR__ . '/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
