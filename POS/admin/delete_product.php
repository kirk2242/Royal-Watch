<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = $_GET['id'];

// Delete the product
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
if ($stmt->execute([$product_id])) {
    header("Location: manage_products.php");
    exit();
} else {
    echo "Error deleting product!";
}
?>
