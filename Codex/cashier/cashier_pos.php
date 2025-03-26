<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../login.php");
    exit();
}

// Fetch categories
$stmt = $pdo->query("SELECT DISTINCT category FROM products");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products
$productStmt = $pdo->query("SELECT * FROM products");
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier POS</title>
    <link rel="stylesheet" href="../assets/css/cashier_pos.css">
</head>
<body>

<!-- Top Bar with Dashboard Button -->
<div class="top-bar">
    <button class="dashboard-btn" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
</div>

<div class="pos-container">
    <!-- Top Section: Barcode & Quantity -->
    <div class="top-section">
        <input type="text" id="barcode" placeholder="Enter Barcode">
        <input type="number" id="quantity" placeholder="Quantity" min="1" value="1">
        <button onclick="addToCartByBarcode()">Add</button>
    </div>

    <div class="main-section">
        <!-- Left: Categories -->
        <div class="category-section">
            <h3>Categories</h3>
            <ul>
                <li onclick="filterProducts('all')">All</li>
                <?php foreach ($categories as $category): ?>
                    <li onclick="filterProducts('<?= $category['category'] ?>')"><?= $category['category'] ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Center: Products -->
        <div class="product-section">
            <?php foreach ($products as $product): ?>
                <div class="product-card" data-category="<?= $product['category'] ?>">
                    <img src="../uploads/<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                    <h4><?= $product['name'] ?></h4>
                    <p>₱<?= number_format($product['price'], 2) ?></p>
                    <button onclick="addToCart(<?= $product['id'] ?>, '<?= $product['name'] ?>', <?= $product['price'] ?>, '<?= $product['image'] ?>')">Add to Cart</button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Right: Cart -->
        <div class="cart-section">
            <h3>Shopping Cart</h3>
            <ul id="cart-items"></ul>
            <p><strong>Total:</strong> ₱<span id="total-price">0.00</span></p>
            <button onclick="checkout()">Checkout</button>
            <button onclick="clearCart()">Clear Cart</button>
        </div>
    </div>
</div>

<script src="../assets/js/cashier_pos.js"></script>
</body>
</html>
