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

    // how many products in total (to know how many pages there are)
    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    }

    // columns the list can be sorted by
    public const SORTABLE = ['id', 'name', 'price', 'quantity_available'];

    // get one page of products, sorted
    // page 1 = rows 1-5, page 2 = rows 6-10 ... (when $perPage = 5)
    public function paginate(int $page, int $perPage, string $sort = 'id', string $dir = 'asc'): array
    {
        $offset = ($page - 1) * $perPage; // how many rows to skip

        // column names can't use "?" placeholders, so we only allow names from the list above.
        // anything else (like "id; DROP TABLE products") becomes 'id' -> no SQL injection
        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'id';
        $dir  = $dir === 'desc' ? 'DESC' : 'ASC';

        // ", id" = if two rows have the same price, keep them in a fixed order across pages
        $stmt = $this->pdo->prepare("SELECT * FROM products ORDER BY $sort $dir, id LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT); // LIMIT/OFFSET must be numbers, not strings
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

    // take items out of stock.
    // "AND quantity_available >= ?" means it only works if there is enough stock,
    // so two people can never buy the last item at the same time.
    // returns true if it worked, false if not enough stock
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
