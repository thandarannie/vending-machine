<?php

class ProductsController extends Controller
{
    private ProductRepository $products;
    private PurchaseService $purchases;

    // the repositories are passed in (dependency injection) so tests can use fake ones
    public function __construct(ProductRepository $products, TransactionRepository $transactions)
    {
        $this->products  = $products;
        $this->purchases = new PurchaseService($products, $transactions); // buy logic, shared with the API
    }

    // list products, 5 per page, sortable
    // URL example: /products?sort=price&dir=desc&page=2
    public function index(): void
    {
        $perPage    = 5;
        $total      = $this->products->count();
        $totalPages = max(1, (int) ceil($total / $perPage)); // at least 1 page, even with 0 products

        // page from the URL (?page=2), kept between 1 and the last page
        $page = (int) ($_GET['page'] ?? 1);
        $page = max(1, min($page, $totalPages));

        // sort from the URL (?sort=price&dir=desc), only allowed values
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

    // ---------- Task 11: attribute routing for purchase ----------
    // The #[Route] lines give these methods their URLs (the Router reads them).
    // SEO-friendly URL with the product name in it:  /products/1/coke/purchase

    // show the "buy" page
    #[Route('GET', '/products/{id}/{slug}/purchase', access: 'login')] // main URL
    #[Route('GET', '/products/{id}/purchase', access: 'login')]        // short URL -> redirects to main URL
    public function showPurchase(int $id, string $slug = ''): void
    {
        $product = $this->findOrFail($id);

        // wrong or missing name in the URL (e.g. product was renamed) -> 301 redirect to the correct URL,
        // so search engines only ever see ONE url per product
        if ($slug !== slugify($product['name'])) {
            $this->redirect(purchase_path($product), 301);
            return;
        }

        $this->showPurchaseForm($product, 1, []);
    }

    // handle the purchase: check quantity, take stock, save transaction
    #[Route('POST', '/products/{id}/{slug}/purchase', access: 'login')]
    public function purchase(int $id, string $slug = ''): void
    {
        $product  = $this->findOrFail($id);
        $quantity = trim($_POST['quantity'] ?? '');

        // check quantity, take stock, log transaction (see PurchaseService.php)
        $result = $this->purchases->purchase($product, $quantity, (int) Auth::id());

        if (isset($result['error'])) {
            $this->showPurchaseForm($product, $quantity, ['quantity' => $result['error']]);
            return;
        }

        $this->flash("You bought {$result['quantity']} × {$product['name']} for \${$result['total']}.");
        $this->redirect('/products');
    }

    // ---------- helpers ----------

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

    // server-side validation: all required, price > 0, quantity >= 0 (see ProductValidator.php)
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

    // render(), flash() and redirect() are in Controller.php
}
