<?php
session_start();
require 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $id = $_POST['product_id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (!isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];
        } else {
            $_SESSION['cart'][$id]['quantity']++;
        }
    }
}

header("Location: pos.php");
exit();
