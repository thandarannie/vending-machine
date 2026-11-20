<?php

class ProductsController extends Controller
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
        $perPage    = 5;
        $total      = $this->products->count();
        $totalPages = max(1, (int) ceil($total / $perPage));

        $page = (int) ($_GET['page'] ?? 1);
        $page = max(1, min($page, $totalPages));

        $sort = $_GET['sort'] ?? 'id';
        $sort = in_array($sort, ProductRepository::SORTABLE, true) ? $sort : 'id';
        $dir  = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $this->render('products/index', [
            'title'      => 'Products',
            'products'   => $this->products->paginate($page, $perPage, $sort, $dir),
            'page'       => $page,
            'totalPages' => $totalPages,
            'total'      => $total,
            'sort'       => $sort,
            'dir'        => $dir,
        ]);
    }
    public function create(): void
    {
        $this->showForm('Add Product', '/products', $this->emptyProduct(), []);
    }

    public function store(): void
    {
        $data   = $this->getInput();
        $errors = $this->validate($data);

        if ($errors) {
            $this->showForm('Add Product', '/products', $data, $errors);
            return;
        }

        $this->products->create($data);
        $this->flash('Product added.');
        $this->redirect('/products');
    }

    public function edit(int $id): void
    {
        $product = $this->findOrFail($id);
        $this->showForm('Edit Product', "/products/$id", $product, []);
    }

    public function update(int $id): void
    {
        $this->findOrFail($id);

        $data   = $this->getInput();
        $errors = $this->validate($data);

        if ($errors) {
            $this->showForm('Edit Product', "/products/$id", $data, $errors);
            return;
        }

        $this->products->update($id, $data);
        $this->flash('Product updated.');
        $this->redirect('/products');
    }

    public function delete(int $id): void
    {
        $product = $this->findOrFail($id);

        try {
            $this->products->delete($id);
            $this->flash('Product deleted.');
        } catch (PDOException $e) {
            $this->flash("Can't delete {$product['name']}: it has purchase history.");
        }

        $this->redirect('/products');
    }

    #[Route('GET', '/products/{id}/{slug}/purchase', access: 'login')]
    #[Route('GET', '/products/{id}/purchase', access: 'login')]
    public function showPurchase(int $id, string $slug = ''): void
    {
        $product = $this->findOrFail($id);

        if ($slug !== slugify($product['name'])) {
            $this->redirect(purchase_path($product), 301);
            return;
        }

        $this->showPurchaseForm($product, 1, []);
    }

    #[Route('POST', '/products/{id}/{slug}/purchase', access: 'login')]
    public function purchase(int $id, string $slug = ''): void
    {
        $product  = $this->findOrFail($id);
        $quantity = trim($_POST['quantity'] ?? '');

        $result = $this->purchases->purchase($product, $quantity, (int) Auth::id());

        if (isset($result['error'])) {
            $this->showPurchaseForm($product, $quantity, ['quantity' => $result['error']]);
            return;
        }

        $this->flash("You bought {$result['quantity']} × {$product['name']} for \${$result['total']}.");
        $this->redirect('/products');
    }

    private function showPurchaseForm(array $product, $quantity, array $errors): void
    {
        $this->render('products/purchase', [
            'title'    => 'Buy ' . $product['name'],
            'product'  => $product,
            'quantity' => $quantity,
            'errors'   => $errors,
        ]);
    }

    private function getInput(): array
    {
        return ProductValidator::clean($_POST);
    }

    private function validate(array $data): array
    {
        return ProductValidator::validate($data);
    }

    private function findOrFail(int $id): array
    {
        $product = $this->products->find($id);

        if (!$product) {
            $this->notFound('Product not found.');
        }

        return $product;
    }

    private function emptyProduct(): array
    {
        return ['name' => '', 'price' => '', 'quantity_available' => ''];
    }

    private function showForm(string $title, string $action, array $product, array $errors): void
    {
        $this->render('products/form', compact('title', 'action', 'product', 'errors'));
    }

}
