<?php

class TransactionsApiController extends ApiController
{
    private TransactionRepository $transactions;

    public function __construct(TransactionRepository $transactions)
    {
        $this->transactions = $transactions;
    }

    // GET /api/transactions   (logged in)
    // admin = all purchases, user = only their own
    public function index(): void
    {
        $user = $this->requireUser();

        $rows = $user['role'] === 'admin'
            ? $this->transactions->getAll()
            : $this->transactions->findByUser((int) $user['sub']);

        $this->json([
            'data' => array_map(fn ($t) => [
                'id'          => (int) $t['id'],
                'user'        => $t['username'],
                'product_id'  => (int) $t['product_id'],
                'product'     => $t['product_name'],
                'quantity'    => (int) $t['quantity'],
                'total_price' => (float) $t['total_price'],
                'created_at'  => $t['created_at'],
            ], $rows),
        ]);
    }
}
