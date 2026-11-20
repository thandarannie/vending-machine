<?php
require __DIR__ . '/../src/Database.php';

$pdo = Database::getConnection();

$users = [
    ['admin', 'admin123', 'admin'],
    ['user',  'user123',  'user'],
];

$stmt = $pdo->prepare('INSERT IGNORE INTO users (username, password, role) VALUES (?, ?, ?)');

foreach ($users as [$username, $password, $role]) {
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role]);
    echo "$username / $password ($role)\n";
}
