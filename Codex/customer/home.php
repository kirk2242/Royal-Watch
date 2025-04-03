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

// Fetch products based on selected category or search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
if ($searchQuery) {
    $productsStmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ?");
    $productsStmt->execute(['%' . $searchQuery . '%']);
} elseif ($selectedCategory) {
    $productsStmt = $pdo->prepare("SELECT * FROM products WHERE category = ?");
    $productsStmt->execute([$selectedCategory]);
} else {
    $productsStmt = $pdo->query("SELECT * FROM products");
}
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
    <div class="shop-logo">
        <img src="../assets/images/49-492180_bell-times-clock-icon-clock-icon-png-yellow.png" alt="Watch Logo">
        <span>Time Emporium</span>
    </div>
    <div class="nav-container">
        <form method="GET" action="home.php" style="display: inline;">
            <input type="text" id="search-bar" name="search" placeholder="Search for watches..." value="<?= htmlspecialchars($searchQuery) ?>">
            <button type="submit">Search</button>
        </form>
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
            <li><a href="home.php" <?= !$selectedCategory ? 'class="active"' : '' ?>>All</a></li>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="?category=<?= urlencode($category['category']) ?>" <?= $selectedCategory === $category['category'] ? 'class="active"' : '' ?>>
                        <?= htmlspecialchars($category['category']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Center: Product Display -->
    <main class="product-display">
        <h2>Available Watches</h2>
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card" data-id="<?= $product['id'] ?>">
                        <div class="product-image">
                            <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <p>Price:  ₱<?= number_format($product['price'], 2) ?> | Stock: <?= htmlspecialchars($product['stock']) ?></p>
                        <button class="add-to-cart" data-id="<?= $product['id'] ?>">Add to Cart</button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No products found.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<script src="../assets/js/customer_dashboard.js"></script>
</body>
</html>
