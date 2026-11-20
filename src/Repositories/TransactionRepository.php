<?php

class TransactionRepository
{
    private PDO $pdo;

    private const SELECT = '
        SELECT t.*, u.username, p.name AS product_name
        FROM transactions t
        JOIN users u    ON u.id = t.user_id
        JOIN products p ON p.id = t.product_id';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        return $this->pdo->query(self::SELECT . ' ORDER BY t.id DESC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE t.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE t.user_id = ? ORDER BY t.id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, int $productId, int $quantity, string $totalPrice): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO transactions (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $productId, $quantity, $totalPrice]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $quantity, string $totalPrice): void
    {
        $stmt = $this->pdo->prepare('UPDATE transactions SET quantity = ?, total_price = ? WHERE id = ?');
        $stmt->execute([$quantity, $totalPrice, $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM transactions WHERE id = ?');
        $stmt->execute([$id]);
    }
}
