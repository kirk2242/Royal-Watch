<?php
session_start();
require '../config/database.php';

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch sales data
$stmt = $pdo->query("SELECT SUM(total) AS total FROM sales");
$total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Fetch top-selling products
$top_products_stmt = $pdo->query("SELECT name, SUM(quantity) AS total_sold FROM sales_items 
                                  JOIN products ON sales_items.product_id = products.id 
                                  GROUP BY product_id ORDER BY total_sold DESC LIMIT 5");
$top_products = $top_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch low-stock products
$low_stock_stmt = $pdo->query("SELECT name, stock FROM products WHERE stock < 10 ORDER BY stock ASC");
$low_stock = $low_stock_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>

    <div class="admin-container">
        <h2>Admin Dashboard</h2>

        <div class="stats">
            <div class="card">
                <h3>Total Sales</h3>
                <p>₱<?php echo number_format($total_sales, 2); ?></p>
            </div>

            <div class="card">
                <h3>Top Selling Products</h3>
                <ul>
                    <?php foreach ($top_products as $product): ?>
                        <li><?php echo $product['name'] . " - " . $product['total_sold'] . " sold"; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h3>Low Stock Alerts</h3>
                <ul>
                    <?php foreach ($low_stock as $product): ?>
                        <li><?php echo $product['name'] . " - " . $product['stock'] . " left"; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="actions">
            <a href="manage_products.php" class="btn">Manage Products</a>
            <a href="sales_report.php" class="btn">View Sales Report</a>
            <a href="../auth/logout.php" class="btn logout">Logout</a>
        </div>
    </div>

</body>
</html>
