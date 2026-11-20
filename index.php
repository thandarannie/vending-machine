<?php
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

session_start();

require __DIR__ . '/src/helpers.php';
require __DIR__ . '/src/Database.php';
require __DIR__ . '/src/Auth.php';
require __DIR__ . '/src/JWT.php';
require __DIR__ . '/src/Route.php';
require __DIR__ . '/src/Router.php';
require __DIR__ . '/src/Controller.php';
require __DIR__ . '/src/ApiController.php';
require __DIR__ . '/src/ProductRepository.php';
require __DIR__ . '/src/TransactionRepository.php';
require __DIR__ . '/src/UserRepository.php';
require __DIR__ . '/src/ProductValidator.php';
require __DIR__ . '/src/PurchaseService.php';
require __DIR__ . '/src/ProductsController.php';
require __DIR__ . '/src/AuthController.php';
require __DIR__ . '/src/TransactionsController.php';
require __DIR__ . '/src/ProductsApiController.php';
require __DIR__ . '/src/AuthApiController.php';
require __DIR__ . '/src/TransactionsApiController.php';

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
