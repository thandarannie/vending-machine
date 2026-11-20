<?php

// The "buy" logic (Task 6), shared by the web page AND the API:
// 1. check the quantity  2. take it out of stock  3. log the transaction
class PurchaseService
{
    private ProductRepository $products;
    private TransactionRepository $transactions;

    public function __construct(ProductRepository $products, TransactionRepository $transactions)
    {
        $this->products     = $products;
        $this->transactions = $transactions;
    }

    // returns ['error' => 'message'] when it fails,
    // or ['transaction_id', 'quantity', 'total', 'remaining'] when it works
    public function purchase(array $product, string $quantity, int $userId): array
    {
        $stock    = (int) $product['quantity_available'];
        $quantity = trim($quantity);

        // 1. check the quantity
        if (filter_var($quantity, FILTER_VALIDATE_INT) === false || $quantity < 1) {
            return ['error' => 'Quantity must be a whole number, 1 or more.'];
        }
        if ($quantity > $stock) {
            return ['error' => $stock === 0 ? 'Sorry, this product is sold out.' : "Only $stock left in stock."];
        }

        $quantity = (int) $quantity;

        // 2. take items out of stock (fails if someone else bought them first)
        if (!$this->products->decreaseQuantity((int) $product['id'], $quantity)) {
            return ['error' => 'Not enough stock.'];
        }

        // 3. log the transaction
        $total = number_format($product['price'] * $quantity, 3, '.', '');
        $transactionId = $this->transactions->create($userId, (int) $product['id'], $quantity, $total);

        return [
            'transaction_id' => $transactionId,
            'quantity'       => $quantity,
            'total'          => $total,
            'remaining'      => $stock - $quantity,
        ];
    }
}
