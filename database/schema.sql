CREATE DATABASE IF NOT EXISTS vm_db;
USE vm_db;

-- 1. users
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin','user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. products
CREATE TABLE IF NOT EXISTS products (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(100)  NOT NULL,
    price              DECIMAL(10,3) NOT NULL,
    quantity_available INT NOT NULL DEFAULT 0
);

-- 3. transactions
CREATE TABLE IF NOT EXISTS transactions (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL,
    total_price DECIMAL(10,3) NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)    REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- insert products
INSERT INTO products (name, price, quantity_available) VALUES
('Coke',  3.990, 10),
('Pepsi', 6.885, 10),
('Water', 0.500, 10);
