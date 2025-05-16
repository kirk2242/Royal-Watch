<?php
session_start();
require '../config/database.php';

// Ensure only Admin or Superadmin can access
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Check if product ID is provided
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    try {
        // Delete product from database
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);

        header("Location: manage_products.php?success=Product deleted successfully");
        exit();
    } catch (PDOException $e) {
        header("Location: manage_products.php?error=Failed to delete product");
        exit();
    }
} else {
    header("Location: manage_products.php?error=Invalid product ID");
    exit();
}
?>
