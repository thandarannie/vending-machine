<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for ProductsController (Tasks 14 + 15).
 *
 * Dependency injection: the controller gets its repositories in the constructor,
 * so here we give it MOCKS (fake repositories) instead of real ones.
 * -> no database is used, every test is fast and independent.
 *
 * A mock can:
 *   - return what we want:      $mock->method('find')->willReturn([...])
 *   - check it was called:      $mock->expects($this->once())->method('create')->with([...])
 *   - check it was NOT called:  $mock->expects($this->never())->method('create')
 */
class ProductsControllerTest extends TestCase
{
    private const COKE  = ['id' => 1, 'name' => 'Coke',  'price' => '3.990', 'quantity_available' => 10];
    private const PEPSI = ['id' => 2, 'name' => 'Pepsi', 'price' => '6.885', 'quantity_available' => 10];

    private ProductRepository&MockObject $products;
    private TransactionRepository&MockObject $transactions;
    private TestProductsController $controller;

    // runs before EVERY test: fresh mocks, empty request
    protected function setUp(): void
    {
        $this->products     = $this->createMock(ProductRepository::class);
        $this->transactions = $this->createMock(TransactionRepository::class);
        $this->controller   = new TestProductsController($this->products, $this->transactions);

        $_GET = [];
        $_POST = [];
        $_SESSION = [];
    }

    // ================================================================
    // index (list + pagination + sorting)
    // ================================================================

    public function test_index_shows_first_page_sorted_by_id(): void
    {
        $this->products->method('count')->willReturn(12);
        $this->products->expects($this->once())
            ->method('paginate')
            ->with(1, 5, 'id', 'asc')
            ->willReturn([self::COKE]);

        $this->controller->index();

        $this->assertSame('products/index', $this->controller->rendered['view']);
        $this->assertSame([self::COKE], $this->controller->rendered['data']['products']);
        $this->assertSame(3, $this->controller->rendered['data']['totalPages']); // 12 products / 5 = 3 pages
    }

    public function test_index_page_too_big_goes_to_last_page(): void
    {
        $_GET['page'] = '99';
        $this->products->method('count')->willReturn(12);
        $this->products->expects($this->once())->method('paginate')->with(3, 5, 'id', 'asc')->willReturn([]);

        $this->controller->index();

        $this->assertSame(3, $this->controller->rendered['data']['page']);
    }

    public function test_index_page_below_one_goes_to_first_page(): void
    {
        $_GET['page'] = '-5';
        $this->products->method('count')->willReturn(12);
        $this->products->expects($this->once())->method('paginate')->with(1, 5, 'id', 'asc')->willReturn([]);

        $this->controller->index();

        $this->assertSame(1, $this->controller->rendered['data']['page']);
    }

    public function test_index_with_no_products_still_has_one_page(): void
    {
        $this->products->method('count')->willReturn(0);
        $this->products->method('paginate')->willReturn([]);

        $this->controller->index();

        $this->assertSame(1, $this->controller->rendered['data']['totalPages']);
    }

    public function test_index_sorts_by_price_descending(): void
    {
        $_GET = ['sort' => 'price', 'dir' => 'desc'];
        $this->products->method('count')->willReturn(3);
        $this->products->expects($this->once())->method('paginate')->with(1, 5, 'price', 'desc')->willReturn([]);

        $this->controller->index();

        $this->assertSame('price', $this->controller->rendered['data']['sort']);
        $this->assertSame('desc', $this->controller->rendered['data']['dir']);
    }

    public function test_index_ignores_unknown_sort_column_and_direction(): void
    {
        $_GET = ['sort' => 'id; DROP TABLE products', 'dir' => 'sideways'];
        $this->products->method('count')->willReturn(3);
        $this->products->expects($this->once())->method('paginate')->with(1, 5, 'id', 'asc')->willReturn([]);

        $this->controller->index();
    }

    // ================================================================
    // create + store
    // ================================================================

    public function test_create_shows_empty_form(): void
    {
        $this->controller->create();

        $this->assertSame('products/form', $this->controller->rendered['view']);
        $this->assertSame('', $this->controller->rendered['data']['product']['name']);
        $this->assertSame([], $this->controller->rendered['data']['errors']);
    }

    public function test_store_saves_valid_product_and_redirects(): void
    {
        $_POST = ['name' => '  Water ', 'price' => '0.5', 'quantity_available' => '20'];

        $this->products->expects($this->once())
            ->method('create')
            ->with(['name' => 'Water', 'price' => '0.5', 'quantity_available' => '20']); // name is trimmed

        $this->controller->store();

        $this->assertSame(['path' => '/products', 'status' => 302], $this->controller->redirected);
        $this->assertSame('Product added.', $_SESSION['flash']);
    }

    public function test_store_allows_zero_quantity(): void
    {
        $_POST = ['name' => 'Juice', 'price' => '1', 'quantity_available' => '0'];
        $this->products->expects($this->once())->method('create');

        $this->controller->store();

        $this->assertNotNull($this->controller->redirected);
    }

    public function test_store_with_empty_fields_shows_all_errors_and_saves_nothing(): void
    {
        $_POST = ['name' => '', 'price' => '', 'quantity_available' => ''];
        $this->products->expects($this->never())->method('create');

        $this->controller->store();

        $errors = $this->controller->rendered['data']['errors'];
        $this->assertSame('Name is required.', $errors['name']);
        $this->assertSame('Price is required.', $errors['price']);
        $this->assertSame('Quantity is required.', $errors['quantity_available']);
        $this->assertNull($this->controller->redirected);
    }

    public function test_store_with_missing_post_fields_shows_errors(): void
    {
        $_POST = []; // nothing sent at all
        $this->products->expects($this->never())->method('create');

        $this->controller->store();

        $this->assertCount(3, $this->controller->rendered['data']['errors']);
    }

    public static function invalidPrices(): array
    {
        return [
            'zero'     => ['0'],
            'negative' => ['-1'],
            'text'     => ['abc'],
        ];
    }

    #[DataProvider('invalidPrices')]
    public function test_store_rejects_invalid_price(string $price): void
    {
        $_POST = ['name' => 'Coke', 'price' => $price, 'quantity_available' => '5'];
        $this->products->expects($this->never())->method('create');

        $this->controller->store();

        $this->assertSame('Price must be a positive number.', $this->controller->rendered['data']['errors']['price']);
    }

    public static function invalidStockQuantities(): array
    {
        return [
            'negative' => ['-1'],
            'decimal'  => ['1.5'],
            'text'     => ['ten'],
        ];
    }

    #[DataProvider('invalidStockQuantities')]
    public function test_store_rejects_invalid_quantity(string $quantity): void
    {
        $_POST = ['name' => 'Coke', 'price' => '1', 'quantity_available' => $quantity];
        $this->products->expects($this->never())->method('create');

        $this->controller->store();

        $this->assertSame(
            'Quantity must be a whole number, 0 or more.',
            $this->controller->rendered['data']['errors']['quantity_available']
        );
    }

    public function test_store_keeps_typed_values_when_invalid(): void
    {
        $_POST = ['name' => 'Coke', 'price' => '-1', 'quantity_available' => '5'];

        $this->controller->store();

        // the form shows what the user typed, so they don't have to type it again
        $this->assertSame('Coke', $this->controller->rendered['data']['product']['name']);
    }

    // ================================================================
    // edit + update
    // ================================================================

    public function test_edit_shows_form_with_product(): void
    {
        $this->products->method('find')->with(1)->willReturn(self::COKE);

        $this->controller->edit(1);

        $this->assertSame('products/form', $this->controller->rendered['view']);
        $this->assertSame(self::COKE, $this->controller->rendered['data']['product']);
        $this->assertSame('/products/1', $this->controller->rendered['data']['action']);
    }

    public function test_edit_unknown_product_is_404(): void
    {
        $this->products->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->controller->edit(999);
    }

    public function test_update_saves_valid_changes(): void
    {
        $_POST = ['name' => 'Coke Zero', 'price' => '4.5', 'quantity_available' => '7'];
        $this->products->method('find')->willReturn(self::COKE);
        $this->products->expects($this->once())
            ->method('update')
            ->with(1, ['name' => 'Coke Zero', 'price' => '4.5', 'quantity_available' => '7']);

        $this->controller->update(1);

        $this->assertSame('/products', $this->controller->redirected['path']);
        $this->assertSame('Product updated.', $_SESSION['flash']);
    }

    public function test_update_with_invalid_data_saves_nothing(): void
    {
        $_POST = ['name' => '', 'price' => '0', 'quantity_available' => '-3'];
        $this->products->method('find')->willReturn(self::COKE);
        $this->products->expects($this->never())->method('update');

        $this->controller->update(1);

        $this->assertCount(3, $this->controller->rendered['data']['errors']);
    }

    public function test_update_unknown_product_is_404_and_saves_nothing(): void
    {
        $_POST = ['name' => 'X', 'price' => '1', 'quantity_available' => '1'];
        $this->products->method('find')->willReturn(null);
        $this->products->expects($this->never())->method('update');

        $this->expectException(NotFoundException::class);
        $this->controller->update(999);
    }

    // ================================================================
    // delete
    // ================================================================

    public function test_delete_removes_product(): void
    {
        $this->products->method('find')->willReturn(self::COKE);
        $this->products->expects($this->once())->method('delete')->with(1);

        $this->controller->delete(1);

        $this->assertSame('Product deleted.', $_SESSION['flash']);
        $this->assertSame('/products', $this->controller->redirected['path']);
    }

    public function test_delete_product_with_purchases_shows_message_instead_of_crashing(): void
    {
        $this->products->method('find')->willReturn(self::COKE);
        // pretend the database blocks it (foreign key)
        $this->products->method('delete')->willThrowException(new PDOException('foreign key'));

        $this->controller->delete(1);

        $this->assertSame("Can't delete Coke: it has purchase history.", $_SESSION['flash']);
        $this->assertSame('/products', $this->controller->redirected['path']);
    }

    public function test_delete_unknown_product_is_404(): void
    {
        $this->products->method('find')->willReturn(null);
        $this->products->expects($this->never())->method('delete');

        $this->expectException(NotFoundException::class);
        $this->controller->delete(999);
    }

    // ================================================================
    // showPurchase (SEO url)
    // ================================================================

    public function test_show_purchase_with_correct_url_shows_buy_page(): void
    {
        $this->products->method('find')->willReturn(self::COKE);

        $this->controller->showPurchase(1, 'coke');

        $this->assertSame('products/purchase', $this->controller->rendered['view']);
        $this->assertSame(1, $this->controller->rendered['data']['quantity']); // default quantity
    }

    public function test_show_purchase_with_wrong_name_redirects_301_to_correct_url(): void
    {
        $this->products->method('find')->willReturn(self::COKE);

        $this->controller->showPurchase(1, 'pepsi');

        $this->assertSame(['path' => '/products/1/coke/purchase', 'status' => 301], $this->controller->redirected);
        $this->assertNull($this->controller->rendered);
    }

    public function test_show_purchase_short_url_redirects_301(): void
    {
        $this->products->method('find')->willReturn(self::COKE);

        $this->controller->showPurchase(1); // /products/1/purchase (no name)

        $this->assertSame(301, $this->controller->redirected['status']);
    }

    public function test_show_purchase_unknown_product_is_404(): void
    {
        $this->products->method('find')->willReturn(null);

        $this->expectException(NotFoundException::class);
        $this->controller->showPurchase(999, 'coke');
    }

    // ================================================================
    // purchase (Task 6: update stock + log transaction)
    // ================================================================

    private function loginAs(int $userId): void
    {
        $_SESSION['user'] = ['id' => $userId, 'username' => 'user', 'role' => 'user'];
    }

    public function test_purchase_takes_stock_and_logs_transaction(): void
    {
        $this->loginAs(2);
        $_POST['quantity'] = '2';
        $this->products->method('find')->willReturn(self::COKE);

        $this->products->expects($this->once())->method('decreaseQuantity')->with(1, 2)->willReturn(true);
        $this->transactions->expects($this->once())->method('create')->with(2, 1, 2, '7.980'); // user 2, Coke, 2 × 3.990

        $this->controller->purchase(1, 'coke');

        $this->assertSame('You bought 2 × Coke for $7.980.', $_SESSION['flash']);
        $this->assertSame('/products', $this->controller->redirected['path']);
    }

    public function test_purchase_total_keeps_three_decimals(): void
    {
        $this->loginAs(2);
        $_POST['quantity'] = '3';
        $this->products->method('find')->willReturn(self::PEPSI);
        $this->products->method('decreaseQuantity')->willReturn(true);

        $this->transactions->expects($this->once())->method('create')->with(2, 2, 3, '20.655'); // 3 × 6.885

        $this->controller->purchase(2, 'pepsi');
    }

    public function test_purchase_whole_stock_is_allowed(): void
    {
        $this->loginAs(2);
        $_POST['quantity'] = '10'; // exactly what is in stock
        $this->products->method('find')->willReturn(self::COKE);
        $this->products->expects($this->once())->method('decreaseQuantity')->with(1, 10)->willReturn(true);

        $this->controller->purchase(1, 'coke');

        $this->assertNotNull($this->controller->redirected);
    }

    public static function invalidPurchaseQuantities(): array
    {
        return [
            'empty'    => [''],
            'zero'     => ['0'],
            'negative' => ['-1'],
            'decimal'  => ['1.5'],
            'text'     => ['abc'],
        ];
    }

    #[DataProvider('invalidPurchaseQuantities')]
    public function test_purchase_rejects_invalid_quantity(string $quantity): void
    {
        $this->loginAs(2);
        $_POST['quantity'] = $quantity;
        $this->products->method('find')->willReturn(self::COKE);

        // nothing may change in the database
        $this->products->expects($this->never())->method('decreaseQuantity');
        $this->transactions->expects($this->never())->method('create');

        $this->controller->purchase(1, 'coke');

        $this->assertSame('Quantity must be a whole number, 1 or more.', $this->controller->rendered['data']['errors']['quantity']);
    }

    public function test_purchase_more_than_stock_is_rejected(): void
    {
        $this->loginAs(2);
        $_POST['quantity'] = '11';
        $this->products->method('find')->willReturn(self::COKE); // stock 10
        $this->products->expects($this->never())->method('decreaseQuantity');
        $this->transactions->expects($this->never())->method('create');

        $this->controller->purchase(1, 'coke');

        $this->assertSame('Only 10 left in stock.', $this->controller->rendered['data']['errors']['quantity']);
    }

    public function test_purchase_sold_out_product_is_rejected(): void
    {
        $this->loginAs(2);
        $_POST['quantity'] = '1';
        $this->products->method('find')->willReturn(['quantity_available' => 0] + self::COKE);
        $this->products->expects($this->never())->method('decreaseQuantity');

        $this->controller->purchase(1, 'coke');

        $this->assertSame('Sorry, this product is sold out.', $this->controller->rendered['data']['errors']['quantity']);
    }

    public function test_purchase_when_someone_else_bought_it_first_logs_nothing(): void
    {
        // the page said 10 in stock, but another buyer took them a moment ago,
        // so the database update fails (decreaseQuantity returns false)
        $this->loginAs(2);
        $_POST['quantity'] = '5';
        $this->products->method('find')->willReturn(self::COKE);
        $this->products->method('decreaseQuantity')->willReturn(false);

        $this->transactions->expects($this->never())->method('create'); // no transaction for a failed purchase

        $this->controller->purchase(1, 'coke');

        $this->assertSame('Not enough stock.', $this->controller->rendered['data']['errors']['quantity']);
        $this->assertNull($this->controller->redirected);
    }

    public function test_purchase_unknown_product_is_404(): void
    {
        $this->loginAs(2);
        $_POST['quantity'] = '1';
        $this->products->method('find')->willReturn(null);
        $this->transactions->expects($this->never())->method('create');

        $this->expectException(NotFoundException::class);
        $this->controller->purchase(999, 'coke');
    }
}
