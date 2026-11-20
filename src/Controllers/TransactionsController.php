<?php

class TransactionsController extends Controller
{
    private TransactionRepository $transactions;

    public function __construct(TransactionRepository $transactions)
    {
        $this->transactions = $transactions;
    }

    public function index(): void
    {
        if (Auth::isAdmin()) {
            $title        = 'All Transactions';
            $transactions = $this->transactions->getAll();
        } else {
            $title        = 'My Purchases';
            $transactions = $this->transactions->findByUser(Auth::id());
        }

        $this->render('transactions/index', [
            'title'        => $title,
            'transactions' => $transactions,
        ]);
    }
}
