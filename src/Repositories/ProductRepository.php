<?php

class ProductRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        return $this->pdo->query('SELECT * FROM products ORDER BY id')->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }

    public const SORTABLE = ['id', 'name', 'price', 'quantity_available'];

    public function paginate(int $page, int $perPage, string $sort = 'id', string $dir = 'asc'): array
    {
        $offset = ($page - 1) * $perPage;

        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'id';
        $dir  = $dir === 'desc' ? 'DESC' : 'ASC';

        $stmt = $this->pdo->prepare("SELECT * FROM products ORDER BY $sort $dir, id LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM products WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO products (name, price, quantity_available) VALUES (?, ?, ?)');
        $stmt->execute([$data['name'], $data['price'], $data['quantity_available']]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->pdo->prepare('UPDATE products SET name = ?, price = ?, quantity_available = ? WHERE id = ?');
        $stmt->execute([$data['name'], $data['price'], $data['quantity_available'], $id]);
    }

    public function decreaseQuantity(int $id, int $quantity): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE products SET quantity_available = quantity_available - ?
             WHERE id = ? AND quantity_available >= ?'
        );
        $stmt->execute([$quantity, $id, $quantity]);
        return $stmt->rowCount() === 1;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM products WHERE id = ?');
        $stmt->execute([$id]);
    }
}
