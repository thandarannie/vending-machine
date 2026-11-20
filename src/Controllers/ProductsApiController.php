<?php

class ProductsApiController extends ApiController
{
    private ProductRepository $products;
    private PurchaseService $purchases;

    public function __construct(ProductRepository $products, TransactionRepository $transactions)
    {
        $this->products  = $products;
        $this->purchases = new PurchaseService($products, $transactions);
    }

    public function index(): void
    {
        $perPage    = max(1, min(50, (int) ($_GET['per_page'] ?? 10)));
        $total      = $this->products->count();
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min((int) ($_GET['page'] ?? 1), $totalPages));

        $sort = $_GET['sort'] ?? 'id';
        $sort = in_array($sort, ProductRepository::SORTABLE, true) ? $sort : 'id';
        $dir  = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $rows = $this->products->paginate($page, $perPage, $sort, $dir);

        $this->json([
            'data' => array_map(fn ($p) => $this->productJson($p), $rows),
            'meta' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => $totalPages,
                'sort'        => $sort,
                'dir'         => $dir,
            ],
        ]);
    }

    public function show(int $id): void
    {
        $this->json($this->productJson($this->findOr404($id)));
    }

    public function store(): void
    {
        $this->requireUser('admin');

        $data   = ProductValidator::clean($this->input());
        $errors = ProductValidator::validate($data);
        if ($errors) {
            $this->error('Validation failed.', 422, ['errors' => $errors]);
        }

        $id = $this->products->create($data);

        $this->json($this->productJson($this->products->find($id)), 201);
    }

    public function update(int $id): void
    {
        $this->requireUser('admin');
        $this->findOr404($id);

        $data   = ProductValidator::clean($this->input());
        $errors = ProductValidator::validate($data);
        if ($errors) {
            $this->error('Validation failed.', 422, ['errors' => $errors]);
        }

        $this->products->update($id, $data);

        $this->json($this->productJson($this->products->find($id)));
    }

    public function destroy(int $id): void
    {
        $this->requireUser('admin');
        $product = $this->findOr404($id);

        try {
            $this->products->delete($id);
        } catch (PDOException $e) {
            $this->error("Can't delete {$product['name']}: it has purchase history.", 409);
        }

        http_response_code(204);
        exit;
    }

    public function purchase(int $id): void
    {
        $user    = $this->requireUser();
        $product = $this->findOr404($id);

        $quantity = (string) ($this->input()['quantity'] ?? '');
        $result   = $this->purchases->purchase($product, $quantity, (int) $user['sub']);

        if (isset($result['error'])) {
            $this->error($result['error'], 422, ['errors' => ['quantity' => $result['error']]]);
        }

        $this->json([
            'message'        => "You bought {$result['quantity']} × {$product['name']}.",
            'transaction_id' => $result['transaction_id'],
            'product'        => $this->productJson(['quantity_available' => $result['remaining']] + $product),
            'quantity'       => $result['quantity'],
            'total'          => (float) $result['total'],
        ], 201);
    }

    private function findOr404(int $id): array
    {
        $product = $this->products->find($id);
        if (!$product) {
            $this->error('Product not found.', 404);
        }
        return $product;
    }
}
