<?php
session_start();
require 'config/database.php';

// Restrict access to cashiers only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cashier') {
    header("Location: auth/login.php");
    exit();
}

// Ensure cart is not empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Fetch cart details
$cart_items = [];
$total = 0;

$placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
$stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
$stmt->execute(array_keys($_SESSION['cart']));
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
foreach ($cart_items as $item) {
    $total += $item['price'] * $_SESSION['cart'][$item['id']];
}

// Process Payment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $pdo->beginTransaction();

    try {
        // Insert into sales table
        $stmt = $pdo->prepare("INSERT INTO sales (user_id, total_amount) VALUES (?, ?)");
        $stmt->execute([$user_id, $total]);
        $sale_id = $pdo->lastInsertId();

        // Insert sale items and update stock
        $stmt = $pdo->prepare("INSERT INTO sale_items (sale_id, product_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
        $updateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($cart_items as $item) {
            $product_id = $item['id'];
            $quantity = $_SESSION['cart'][$product_id];
            $subtotal = $item['price'] * $quantity;

            $stmt->execute([$sale_id, $product_id, $quantity, $subtotal]);
            $updateStock->execute([$quantity, $product_id]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];
        header("Location: receipt.php?sale_id=$sale_id");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Transaction failed!";
    }
}
?>

<h2>Checkout</h2>
<form method="POST">
    <h3>Total: <?= number_format($total, 2) ?></h3>
    <button type="submit">Confirm Payment</button>
</form>
<a href="cart.php">Back to Cart</a>
