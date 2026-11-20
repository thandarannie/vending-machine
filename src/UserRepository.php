<?php

class UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // READ all (without passwords)
    public function getAll(): array
    {
        return $this->pdo->query('SELECT id, username, role, created_at FROM users ORDER BY id')->fetchAll();
    }

    // READ one by id (returns null if not found)
    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    // READ one by username (used by login)
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    // CREATE: the plain password is hashed here, so it is never saved as plain text
    public function create(string $username, string $password, string $role = 'user'): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
        return (int) $this->pdo->lastInsertId();
    }

    // UPDATE username and role
    public function update(int $id, string $username, string $role): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET username = ?, role = ? WHERE id = ?');
        $stmt->execute([$username, $role, $id]);
    }

    // UPDATE password (hashed)
    public function changePassword(int $id, string $password): void
    {
        $stmt = $this->pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    // DELETE (the database blocks this if the user has transactions)
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
    }
}
