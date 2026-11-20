<?php

// The #[Route(...)] attribute (Task 11: attribute routing).
// Put it above a controller method to give that method a URL:
//
//   #[Route('GET', '/products/{id}/purchase', access: 'login')]
//   public function showPurchase(int $id) { ... }
//
// The Router reads these attributes and registers the routes.
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public string $method,          // 'GET' or 'POST'
        public string $path,            // e.g. '/products/{id}/purchase'
        public ?string $access = null,  // null = everyone, 'login' = logged in, 'admin' = admins only
    ) {
    }
}
