<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: checkout.php");
    exit();
}

try {
    $pdo->beginTransaction();

    $customer_id = $_SESSION['user_id'];
    $totalAmount = 0;

    // Insert into sales table
    $stmt = $pdo->prepare("INSERT INTO sales (customer_id, created_at) VALUES (?, NOW())");
    $stmt->execute([$customer_id]);
    $sale_id = $pdo->lastInsertId();

    // Insert into sales_items table
    foreach ($_SESSION['cart'] as $productId => $item) {
        $quantity = $item['quantity'];
        $totalPrice = $item['price'] * $quantity;
        $totalAmount += $totalPrice;

        $stmt = $pdo->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sale_id, $productId, $quantity, $totalPrice]);
    }

    $pdo->commit();
    
    // Clear cart after checkout
    unset($_SESSION['cart']);

    header("Location: success.php");
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Checkout failed: " . $e->getMessage();
}
?>
