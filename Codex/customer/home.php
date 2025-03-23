<?php
session_start();
require '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch product categories
$categoriesStmt = $pdo->query("SELECT DISTINCT category FROM products");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all products
$productsStmt = $pdo->query("SELECT * FROM products");
$products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="../assets/css/customer_dashboard.css">
</head>
<body>

<!-- Top Navigation -->
<header>
    <div class="nav-container">
        <input type="text" id="search-bar" placeholder="Search for watches...">
        <div class="nav-links">
            <a href="cart.php" class="cart-icon">🛒 Cart</a>
            <a href="account.php">👤 My Account</a>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <!-- Left Sidebar: Categories -->
    <aside class="sidebar">
        <h3>Watch Categories</h3>
        <ul>
            <li><a href="customer_dashboard.php">All</a></li>
            <?php foreach ($categories as $category): ?>
                <li><a href="?category=<?= urlencode($category['category']) ?>"><?= htmlspecialchars($category['category']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Center: Product Display -->
    <main class="product-display">
        <h2>Available Watches</h2>
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p>Price: $<?= number_format($product['price'], 2) ?></p>
                        <button class="add-to-cart" data-id="<?= $product['id'] ?>">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products available.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        let productId = this.getAttribute('data-id');
        
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + productId
        })
        .then(response => response.text())
        .then(data => alert(data));
    });
});
</script>

</body>
</html>
