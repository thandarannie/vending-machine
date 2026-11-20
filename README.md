# Vending Machine (PHP + MySQL)

A small vending machine **web app + REST API** in plain PHP 8 (no framework). Products, inventory, purchases, session login with roles, and JWT login for the API.

- **Demo accounts:** `admin / admin123` (Admin) · `user / user123` (User)

## Setup

Requirements: PHP 8.1+ (`pdo_mysql`), MySQL, Composer, Apache with `mod_rewrite` (or PHP's built-in server).

```bash
composer install
mysql -u root -p < database/schema.sql
php database/seed_users.php
```

## Database

MySQL database `vm_db`, three tables:

- **products** — `id, name, price DECIMAL(10,3), quantity_available`. Price uses 3 decimals (Pepsi is 6.885) and `DECIMAL` for exact money.
- **users** — `id, username (unique), password (bcrypt hash), role (admin|user)`. Passwords are never stored in plain text.
- **transactions** — `id, user_id FK, product_id FK, quantity, total_price, created_at`. One row per purchase; `total_price` is stored at purchase time so price changes don't rewrite history.

Foreign keys block deleting a product/user that has purchase history (web shows a message, API returns `409`).

## Project structure

```
index.php      front controller — every request starts here
routes.php     web + API routes
config/        DB + JWT settings
database/      schema.sql + seed_users.php
src/
  Controllers/  web + API controllers (+ base classes)
  Repositories/ Database (PDO) + CRUD per table
  Services/     PurchaseService, ProductValidator (shared by web + API)
  Core/         Router, Route, Auth, JWT, helpers
views/         HTML templates
tests/         PHPUnit tests
```

**Layers:** Controller → Service/Validator → Repository → Database. Each layer only talks to the one below, so each is testable on its own.

## REST API

Base URL `/api`, JSON in/out, CORS enabled. Log in for a JWT, then send it as `Authorization: Bearer <token>`.

```bash
curl -X POST .../api/login -H "Content-Type: application/json" \
     -d '{"username":"admin","password":"admin123"}'
```

| Method | URL | Auth |
|---|---|---|
| POST | `/api/login` | – |
| GET | `/api/products` | – |
| GET | `/api/products/{id}` | – |
| POST / PUT / DELETE | `/api/products[/{id}]` | admin |
| POST | `/api/products/{id}/purchase` | token |
| GET | `/api/transactions` | token |

Errors are JSON (`{"error": "..."}`). Status codes: `401` auth, `403` not admin, `404` not found, `409` delete with history, `422` validation / out of stock.

## Tests

```bash
vendor/bin/phpunit    # 42 tests, 134 assertions
```

Controllers get their repositories via the constructor (dependency injection), so tests use **mocks** — no database needed, runs in ~0.02s.

## Security

- Prepared statements everywhere; sort column checked against a whitelist (no SQL injection)
- Output escaped with `htmlspecialchars()` (no XSS)
- Passwords hashed with bcrypt; same error message for wrong user/password
- `session_regenerate_id()` on login/logout (session fixation)
- JWT: HMAC-SHA256, `hash_equals()`, only `HS256` accepted, expiry checked
- Overselling blocked by a conditional `UPDATE ... WHERE quantity_available >= ?`
- `.htaccess` returns 403 for code folders

> Before going live: set a new `jwt_secret` and real DB credentials in `config/config.php`.

## Task checklist (17)

| # | Task | Where |
|---|---|---|
| 1 | Database design | `database/schema.sql` |
| 2 | PDO connection + CRUD | `src/Repositories/` |
| 3 | Sessions + hashing, roles | `src/Core/Auth.php`, `AuthController` |
| 4 | Role-based access | `Router::checkAccess()` |
| 5 | `ProductsController` CRUD | `src/Controllers/ProductsController.php` |
| 6 | Purchase: stock + log | `src/Services/PurchaseService.php` |
| 7 | CRUD views (admin-only) | `views/products/` |
| 8 | List + pagination + sorting | `views/products/index.php` |
| 9 | Purchase view | `views/products/purchase.php` |
| 10 | Routing | `routes.php`, `src/Core/Router.php` |
| 11 | Attribute routing + SEO URLs | `#[Route]` in `ProductsController` |
| 12 | Validation rules | `src/Services/ProductValidator.php` |
| 13 | Server + client validation | Validator + JS in `views/products/form.php` |
| 14 | PHPUnit tests | `tests/ProductsControllerTest.php` |
| 15 | Dependency injection + mocking | constructor injection + `createMock()` |
| 16 | REST API | `src/Controllers/*ApiController.php` |
| 17 | JWT authentication | `src/Core/JWT.php` |
