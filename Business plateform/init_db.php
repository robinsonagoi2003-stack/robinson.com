<?php
require_once __DIR__ . '/config.php';

$createSql = <<<SQL
CREATE DATABASE IF NOT EXISTS business_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SQL;

try {
    $pdoRoot = new PDO('mysql:host=localhost;charset=utf8mb4', $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdoRoot->exec($createSql);
    $pdoRoot = null;

    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(150) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (id INT AUTO_INCREMENT PRIMARY KEY, email VARCHAR(150) NOT NULL, token VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL, created_at DATETIME NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS customers (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, email VARCHAR(150), phone VARCHAR(50), created_at DATETIME NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, customer_id INT NOT NULL, amount DECIMAL(10,2) NOT NULL, status ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending', created_at DATETIME NOT NULL, FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity (id INT AUTO_INCREMENT PRIMARY KEY, message VARCHAR(255) NOT NULL, type VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS sales (id INT AUTO_INCREMENT PRIMARY KEY, amount DECIMAL(10,2) NOT NULL, created_at DATETIME NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS expenses (id INT AUTO_INCREMENT PRIMARY KEY, amount DECIMAL(10,2) NOT NULL, description VARCHAR(255), created_at DATETIME NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS projects (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(150) NOT NULL, status ENUM('active','completed') NOT NULL DEFAULT 'active', created_at DATETIME NOT NULL)");
    $pdo->exec("CREATE TABLE IF NOT EXISTS tasks (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, notes TEXT, status ENUM('pending','in progress','completed') NOT NULL DEFAULT 'pending', created_at DATETIME NOT NULL)");

    $pdo->exec("INSERT INTO customers (name, email, phone, created_at) VALUES ('Acme Corp', 'contact@acme.com', '555-0191', DATE_SUB(NOW(), INTERVAL 32 DAY)), ('Bright Media', 'hello@brightmedia.com', '555-0222', DATE_SUB(NOW(), INTERVAL 24 DAY)), ('Cedar Consulting', 'team@cedarconsulting.com', '555-0345', DATE_SUB(NOW(), INTERVAL 14 DAY))");
    $pdo->exec("INSERT INTO orders (customer_id, amount, status, created_at) VALUES (1, 4500.00, 'completed', DATE_SUB(NOW(), INTERVAL 12 DAY)), (2, 3200.00, 'completed', DATE_SUB(NOW(), INTERVAL 8 DAY)), (3, 2800.00, 'pending', DATE_SUB(NOW(), INTERVAL 3 DAY))");
    $pdo->exec("INSERT INTO activity (message, type, created_at) VALUES ('Created new customer account for Acme Corp', 'customer', DATE_SUB(NOW(), INTERVAL 30 DAY)), ('Order #102 completed for Bright Media', 'order', DATE_SUB(NOW(), INTERVAL 8 DAY)), ('Task created: Follow up on pending quotes', 'task', DATE_SUB(NOW(), INTERVAL 2 DAY))");
    $pdo->exec("INSERT INTO sales (amount, created_at) VALUES (7200.00, NOW()), (5100.00, DATE_SUB(NOW(), INTERVAL 10 DAY)), (4900.00, DATE_SUB(NOW(), INTERVAL 25 DAY))");
    $pdo->exec("INSERT INTO expenses (amount, description, created_at) VALUES (1920.00, 'Office rent', DATE_SUB(NOW(), INTERVAL 5 DAY)), (840.00, 'Marketing', DATE_SUB(NOW(), INTERVAL 8 DAY)), (620.00, 'Software', DATE_SUB(NOW(), INTERVAL 15 DAY))");
    $pdo->exec("INSERT INTO projects (name, status, created_at) VALUES ('Project Alpha', 'active', DATE_SUB(NOW(), INTERVAL 20 DAY)), ('Customer onboarding', 'active', DATE_SUB(NOW(), INTERVAL 30 DAY)), ('Marketing launch', 'completed', DATE_SUB(NOW(), INTERVAL 45 DAY))");
    $pdo->exec("INSERT INTO tasks (title, notes, status, created_at) VALUES ('Invoice review', 'Confirm all invoices before the end of the week', 'pending', DATE_SUB(NOW(), INTERVAL 3 DAY)), ('Client follow-up', 'Reach out to Bright Media for next quarter work', 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY)), ('Quarterly sales analysis', 'Prepare a report for management', 'in progress', DATE_SUB(NOW(), INTERVAL 6 DAY))");

    echo "Initialization complete. Database tables created and sample data inserted.\n";
} catch (PDOException $e) {
    echo "Database initialization failed: " . $e->getMessage();
}
