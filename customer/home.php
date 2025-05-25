
<?php
session_start();
require '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../auth/login.php");
    exit();
}

// Helper function to truncate description to 10 words
function truncate_words($text, $limit = 10) {
    $words = preg_split('/\s+/', strip_tags($text));
    if (count($words) <= $limit) {
        return htmlspecialchars($text);
    }
    $truncated = array_slice($words, 0, $limit);
    return htmlspecialchars(implode(' ', $truncated)) . '...';
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
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: #fff;
            border-radius: 10px;
            max-width: 90vw;
            width: 400px;
            padding: 2rem;
            position: relative;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            text-align: center;
            animation: fadeIn 0.2s;
        }
        .modal-content img {
            max-width: 100%;
            max-height: 250px;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .modal-close {
            position: absolute;
            top: 10px; right: 15px;
            font-size: 1.5rem;
            color: #888;
            cursor: pointer;
            background: none;
            border: none;
        }
        .modal-content .product-name {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .modal-content .product-description {
            margin-bottom: 1rem;
            color: #555;
        }
        .modal-content .product-price-stock {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        @media (max-width: 500px) {
            .modal-content {
                width: 95vw;
                padding: 1rem;
            }
        }
        /* Add to cart button disabled style */
        .add-to-cart:disabled,
        .add-to-cart[disabled] {
            background: #ccc !important;
            color: #fff !important;
            cursor: not-allowed !important;
            opacity: 0.7;
        }
    </style>
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
            <div class="product-card"
                data-id="<?= $product['id'] ?>"
                data-name="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>"
                data-image="../uploads/<?= htmlspecialchars($product['image'], ENT_QUOTES) ?>"
                data-description="<?= htmlspecialchars($product['description'], ENT_QUOTES) ?>"
                data-price="<?= number_format($product['price'], 2) ?>"
                data-stock="<?= htmlspecialchars($product['stock']) ?>"
                tabindex="0"
                style="cursor:pointer"
            >
                <div class="product-image">
                    <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p class="product-description"><?= truncate_words($product['description'], 10) ?></p>
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

<!-- Modal for Product Details -->
<div class="modal" id="productModal" tabindex="-1">
    <div class="modal-content" id="modalContent">
        <button class="modal-close" id="modalCloseBtn" aria-label="Close">&times;</button>
        <img src="" alt="Product Image" id="modalImage">
        <div class="product-name" id="modalName"></div>
        <div class="product-description" id="modalDescription"></div>
        <div class="product-price-stock" id="modalPriceStock"></div>
    </div>
</div>

<script src="../assets/js/customer_dashboard.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const modal = document.getElementById('productModal');
    const modalImage = document.getElementById('modalImage');
    const modalName = document.getElementById('modalName');
    const modalDescription = document.getElementById('modalDescription');
    const modalPriceStock = document.getElementById('modalPriceStock');
    const modalCloseBtn = document.getElementById('modalCloseBtn');

    // Open modal on product card click
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Prevent click on add-to-cart button from opening modal
            if (e.target.classList.contains('add-to-cart')) return;

            modalImage.src = this.getAttribute('data-image');
            modalImage.alt = this.getAttribute('data-name');
            modalName.textContent = this.getAttribute('data-name');
            modalDescription.textContent = this.getAttribute('data-description');
            modalPriceStock.innerHTML = 
                '<strong>Price:</strong> ₱' + this.getAttribute('data-price') +
                '<br><strong>Stock:</strong> ' + this.getAttribute('data-stock');
            modal.classList.add('active');
        });
        // Allow keyboard accessibility
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Close modal on close button or background click
    modalCloseBtn.addEventListener('click', function() {
        modal.classList.remove('active');
    });
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
    // Optional: Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            modal.classList.remove('active');
        }
    });
});
</script>
</body>
</html>
