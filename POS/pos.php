<?php
session_start();
require 'config/database.php';

// Restrict access to cashiers only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cashier') {
    header("Location: auth/login.php");
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products WHERE stock > 0 ORDER BY name ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS System</title>
    <link rel="stylesheet" href="pos_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="pos-container">
    <h2>🛒 POS System - Product List</h2>
    
    <div class="nav-links">
        <a href="cart.php">🛍 View Cart</a>
        <a href="auth/logout.php">🚪 Logout</a>
    </div>

    <div class="grid-container">
        <?php foreach ($products as $product): ?>
        <div class="product-card">
            <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <h3><?= htmlspecialchars($product['name']) ?></h3>
            <p><strong>Price:</strong> P<?= number_format($product['price'], 2) ?></p>
            <p><strong>Stock:</strong> <?= htmlspecialchars($product['stock']) ?></p>
            <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                <button type="submit">➕ Add to Cart</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
