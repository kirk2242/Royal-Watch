
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

// Fetch brands based on selected category
$selectedCategory = isset($_GET['category']) ? $_GET['category'] : null;
$brands = [];
if ($selectedCategory) {
    $brandsStmt = $pdo->prepare("SELECT DISTINCT brand FROM products WHERE category = ?");
    $brandsStmt->execute([$selectedCategory]);
    $brands = $brandsStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch products based on selected category, brand, search query, or gender
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : null;
$selectedBrand = isset($_GET['brand']) ? $_GET['brand'] : null;
$selectedGender = isset($_GET['gender']) ? $_GET['gender'] : 'All';

$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($searchQuery) {
    $query .= " AND name LIKE ?";
    $params[] = '%' . $searchQuery . '%';
}
if ($selectedBrand) {
    $query .= " AND brand = ?";
    $params[] = $selectedBrand;
}
if ($selectedCategory) {
    $query .= " AND category = ?";
    $params[] = $selectedCategory;
}
if ($selectedGender !== 'All') {
    $query .= " AND gender = ?";
    $params[] = $selectedGender;
}

$productsStmt = $pdo->prepare($query);
$productsStmt->execute($params);
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
            <a href="cart.php" class="cart-icon"> Cart</a>
            <a href="account.php">👤 My Account</a>
            <a href="../auth/logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <!-- Left Sidebar: Categories and Brands -->
    <aside class="sidebar">
        <h3>Watch Categories</h3>
        <ul>
            <li><a href="home.php" <?= !$selectedCategory ? 'class="active"' : '' ?>>All</a></li>
            <?php foreach ($categories as $category): ?>
                <li>
                    <a href="?category=<?= urlencode($category['category']) ?>" <?= $selectedCategory === $category['category'] ? 'class="active"' : '' ?>>
                        <?= htmlspecialchars($category['category']) ?>
                    </a>
                    <?php if ($selectedCategory === $category['category'] && !empty($brands)): ?>
                        <ul>
                            <?php foreach ($brands as $brand): ?>
                                <li>
                                    <a href="?category=<?= urlencode($selectedCategory) ?>&brand=<?= urlencode($brand['brand']) ?>" <?= $selectedBrand === $brand['brand'] ? 'class="active"' : '' ?>>
                                        <?= htmlspecialchars($brand['brand']) ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <!-- Center: Product Display -->
    <main class="product-display">
        <h2>Available Watches</h2>
        <!-- Gender Filter Dropdown -->
        <form method="GET" action="home.php" style="margin-bottom: 20px;">
            <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
            <input type="hidden" name="brand" value="<?= htmlspecialchars($selectedBrand) ?>">
            <select name="gender" onchange="this.form.submit()">
                <option value="All" <?= $selectedGender === 'All' ? 'selected' : '' ?>>All</option>
                <option value="Men" <?= $selectedGender === 'Men' ? 'selected' : '' ?>>Men</option>
                <option value="Women" <?= $selectedGender === 'Women' ? 'selected' : '' ?>>Women</option>
            </select>
        </form>
        <div class="product-grid">
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <div class="product-card" data-id="<?= $product['id'] ?>">
                <div class="product-image">
                    <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                <p>Price:  ₱<?= number_format($product['price'], 2) ?> | Stock: <?= htmlspecialchars($product['stock']) ?></p>
                <?php if ($product['stock'] > 0): ?>
                    <button class="add-to-cart" data-id="<?= $product['id'] ?>">Add to Cart</button>
                <?php else: ?>
                    <button class="add-to-cart" data-id="<?= $product['id'] ?>" disabled>Out of Stock</button>
                <?php endif; ?>
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
