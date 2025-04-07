
<?php
session_start();
require '../config/database.php';

// Ensure only admin/superadmin can access
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Pagination
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Build query with filters
$query = "SELECT * FROM products WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR barcode LIKE ? OR brand LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($category_filter)) {
    $query .= " AND category = ?";
    $params[] = $category_filter;
}

// Count total for pagination
$count_stmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $query));
if (!empty($params)) {
    $count_stmt->execute($params);
} else {
    $count_stmt->execute();
}
$total_items = $count_stmt->fetchColumn();
$total_pages = ceil($total_items / $items_per_page);

// Get products with pagination
$query .= " ORDER BY name ASC LIMIT $offset, $items_per_page";
$stmt = $pdo->prepare($query);
if (!empty($params)) {
    $stmt->execute($params);
} else {
    $stmt->execute();
}
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter dropdown
$category_stmt = $pdo->query("SELECT DISTINCT category FROM products ORDER BY category");
$categories = $category_stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../assets/css/manage_products.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <h2>Manage Products</h2>
    
    <div class="filter-section">
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Search products..." 
                   value="<?= htmlspecialchars($search) ?>">
        </div>
        
        <select class="filter-dropdown" id="categoryFilter">
            <option value="">All Categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>" 
                        <?= $category_filter === $category ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category) ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <a href="add_product.php" class="add-btn"><i class="fas fa-plus"></i> Add New Product</a>
    </div>

    <table>
    <thead>
        <tr>
            <th>Image</th>
            <th>Barcode</th>
            <th>Name</th>
            <th>Category</th>
            <th>Brand</th>
            <th>Gender</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($products as $product): ?>
        <tr>
            <td>
                <?php if (!empty($product['image'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($product['image']); ?>" alt="<?= htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <span>No Image</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($product['barcode']); ?></td>
            <td><?= htmlspecialchars($product['name']); ?></td>
            <td><?= htmlspecialchars($product['category']); ?></td>
            <td><?= htmlspecialchars($product['brand']); ?></td>
            <td><?= htmlspecialchars($product['gender']); ?></td>
            <td>$<?= number_format($product['price'], 2); ?></td>
            <td>
                <?php 
                $stock = (int)$product['stock'];
                if ($stock > 10) {
                    echo '<span class="stock-status in-stock">' . $stock . '</span>';
                } elseif ($stock > 0) {
                    echo '<span class="stock-status low-stock">' . $stock . '</span>';
                } else {
                    echo '<span class="stock-status out-of-stock">Out of stock</span>';
                }
                ?>
            </td>
            <td>
                <a href="edit_product.php?id=<?= $product['id']; ?>"><i class="fas fa-edit"></i></a> 
                <a href="delete_product.php?id=<?= $product['id']; ?>" 
                   onclick="return confirm('Are you sure you want to delete this product?')">
                   <i class="fas fa-trash-alt"></i>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    
    <?php if (empty($products)): ?>
        <tr>
            <td colspan="9" style="text-align: center; padding: 2rem;">No products found</td>
        </tr>
    <?php endif; ?>
    </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>">
                <i class="fas fa-chevron-left"></i>
            </a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>" 
               class="<?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&category=<?= urlencode($category_filter) ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <a href="../admin/dashboard.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    
    function applyFilters() {
        const searchValue = searchInput.value.trim();
        const categoryValue = categoryFilter.value;
        
        window.location.href = `?search=${encodeURIComponent(searchValue)}&category=${encodeURIComponent(categoryValue)}`;
    }
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            applyFilters();
        }
    });
    
    categoryFilter.addEventListener('change', applyFilters);
});
</script>

</body>
</html>
</qodoArtifact>