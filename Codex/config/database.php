<?php
/*CREATE DATABASE Time_Emporium_DB;
USE Time_Emporium_DB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'admin', 'cashier', 'customer') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE, -- Links to `users` table
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    age INT,
    sex ENUM('Male', 'Female', 'Other'),
    contact_number VARCHAR(20),
    address TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barcode VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,  -- Category column
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT, -- Links to `users` table (customer)
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE sales_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT, -- Links to `sales`
    product_id INT, -- Links to `products`
    category VARCHAR(100) NOT NULL, -- Product category (Linked from `products`)
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

*/

// Database connection settings
$host = "localhost"; // Change if necessary
$dbname = "Time_Emporium_DB"; // Ensure this matches your actual database name
$username = "root"; // Default XAMPP/MAMP username
$password = ""; // Default XAMPP password (empty)

// Establish PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>