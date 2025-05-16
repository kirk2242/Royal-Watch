<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barcode = trim($_POST['barcode']);
    $quantity = intval($_POST['quantity']);

    // Fetch product by barcode
    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        die("Product not found.");
    }

    // Store product in session (cart)
    $_SESSION['cart'][] = [
        'barcode' => $product['barcode'],
        'name' => $product['name'],
        'price' => $product['price'],
        'quantity' => $quantity,
        'total' => $product['price'] * $quantity
    ];

    header("Location: pos.php");
    exit();
}
?>
