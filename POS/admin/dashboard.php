<?php
session_start();
require '../config/database.php';

// Restrict access to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch total sales
$stmt = $pdo->query("SELECT SUM(total_amount) AS total_sales FROM sales");
$total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total_sales'] ?? 0;

// Fetch daily sales
$stmt = $pdo->query("SELECT DATE(created_at) AS sale_date, SUM(total_amount) AS daily_total 
                     FROM sales GROUP BY sale_date ORDER BY sale_date DESC LIMIT 7");
$daily_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch top-selling products
$stmt = $pdo->query("SELECT products.name, SUM(sale_items.quantity) AS total_sold 
                     FROM sale_items JOIN products ON sale_items.product_id = products.id 
                     GROUP BY sale_items.product_id ORDER BY total_sold DESC LIMIT 5");
$top_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch stock alerts (low stock)
$stmt = $pdo->query("SELECT name, stock FROM products WHERE stock <= 5 ORDER BY stock ASC");
$low_stock = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-container">
    <h2>Admin Dashboard</h2>

    <a href="../dashboard.php" class="back-button">⬅ Back</a>

    <h3>Total Sales: P<?= number_format($total_sales, 2) ?></h3>

    <h3>Daily Sales (Last 7 Days)</h3>
    <table>
        <tr><th>Date</th><th>Total Sales</th></tr>
        <?php foreach ($daily_sales as $sale): ?>
        <tr><td><?= $sale['sale_date'] ?></td><td>P<?= number_format($sale['daily_total'], 2) ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h3>Top-Selling Products</h3>
    <table>
        <tr><th>Product</th><th>Units Sold</th></tr>
        <?php foreach ($top_products as $product): ?>
        <tr><td><?= $product['name'] ?></td><td><?= $product['total_sold'] ?></td></tr>
        <?php endforeach; ?>
    </table>

    <h3>Stock Alerts (Low Inventory)</h3>
    <table>
        <tr><th>Product</th><th>Stock Left</th></tr>
        <?php foreach ($low_stock as $product): ?>
        <tr class="low-stock">
            <td><?= $product['name'] ?></td>
            <td><?= $product['stock'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
