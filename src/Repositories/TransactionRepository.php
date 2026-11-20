<?php

class TransactionRepository
{
    private PDO $pdo;

    // the same SELECT for every "read": JOIN adds the username and product name,
    // so we can show "user bought 2 × Coke" instead of "user 2 bought 2 × product 1"
    private const SELECT = '
        SELECT t.*, u.username, p.name AS product_name
        FROM transactions t
        JOIN users u    ON u.id = t.user_id
        JOIN products p ON p.id = t.product_id';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // READ all (newest first)
    public function getAll(): array
    {
        return $this->pdo->query(self::SELECT . ' ORDER BY t.id DESC')->fetchAll();
    }

    // READ one (returns null if not found)
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE t.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // READ all purchases of one user (newest first)
    public function findByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(self::SELECT . ' WHERE t.user_id = ? ORDER BY t.id DESC');
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    // CREATE: save one purchase
    public function create(int $userId, int $productId, int $quantity, string $totalPrice): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO transactions (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $productId, $quantity, $totalPrice]);
        return (int) $this->pdo->lastInsertId();
    }

    // UPDATE quantity and total (e.g. an admin correcting a wrong record)
    public function update(int $id, int $quantity, string $totalPrice): void
    {
        $stmt = $this->pdo->prepare('UPDATE transactions SET quantity = ?, total_price = ? WHERE id = ?');
        $stmt->execute([$quantity, $totalPrice, $id]);
    }

    // DELETE
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM transactions WHERE id = ?');
        $stmt->execute([$id]);
    }
}
