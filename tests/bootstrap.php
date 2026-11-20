<?php
// Runs once before the tests.

require __DIR__ . '/../vendor/autoload.php';   // loads every class in src/ + helpers.php
require __DIR__ . '/TestProductsController.php';

define('BASE_URL', ''); // index.php normally sets this; tests use no folder prefix

$_SESSION = [];
