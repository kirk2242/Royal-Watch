<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: checkout.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $paymentMethod = $_POST['payment_method'];

    try {
        $pdo->beginTransaction();

        // Validate and retrieve customer_id from customers table
        $user_id = $_SESSION['user_id']; // Ensure this is set correctly
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $customer_id = $stmt->fetchColumn();

        if (!$customer_id) {
            throw new Exception("Invalid customer ID. Please ensure your account is properly set up.");
        }

        $totalAmount = 0;

        // Insert into sales table (use correct column name for foreign key)
        $stmt = $pdo->prepare("INSERT INTO sales (customer_id, payment_method, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$customer_id, $paymentMethod]);
        $sale_id = $pdo->lastInsertId();

        // Insert into sales_items table and check stock
        foreach ($_SESSION['cart'] as $productId => $item) {
            $quantity = $item['quantity'];
            $totalPrice = $item['price'] * $quantity;
            $totalAmount += $totalPrice;

            // Check stock availability
            $stockCheckStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
            $stockCheckStmt->execute([$productId]);
            $stock = $stockCheckStmt->fetchColumn();

            if ($stock < $quantity) {
                throw new Exception("Insufficient stock for product ID: $productId");
            }

            // Deduct stock and insert sales item
            $updateStockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $updateStockStmt->execute([$quantity, $productId]);

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
}
?>
