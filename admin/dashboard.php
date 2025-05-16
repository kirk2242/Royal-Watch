<?php
session_start();
require '../config/database.php';

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get admin username
$username = $_SESSION['username'] ?? 'Admin';

// Fetch sales data
$stmt = $pdo->query("SELECT SUM(total) AS total FROM sales");
$total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Fetch total number of products
$products_stmt = $pdo->query("SELECT COUNT(*) AS count FROM products");
$total_products = $products_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Fetch total number of orders
$orders_stmt = $pdo->query("SELECT COUNT(*) AS count FROM sales");
$total_orders = $orders_stmt->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;

// Fetch top-selling products
$top_products_stmt = $pdo->query("SELECT name, SUM(quantity) AS total_sold FROM sales_items 
                                  JOIN products ON sales_items.product_id = products.id 
                                  GROUP BY product_id ORDER BY total_sold DESC LIMIT 5");
$top_products = $top_products_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch low-stock products with stock below 5
$low_stock_stmt = $pdo->query("SELECT name, stock FROM products WHERE stock < 6 ORDER BY stock ASC LIMIT 5");
$low_stock = $low_stock_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch sales distribution by product
$sales_distribution_stmt = $pdo->query("SELECT products.name, SUM(sales_items.quantity) AS total_sold 
                                        FROM sales_items 
                                        JOIN products ON sales_items.product_id = products.id 
                                        GROUP BY products.name ORDER BY total_sold DESC LIMIT 10");
$sales_distribution = $sales_distribution_stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate random colors for chart
function generateRandomColors($count) {
    $colors = [
        '#4facfe', '#00f2fe', '#ff5e62', '#ff9966', '#4caf50',
        '#ffa500', '#2196f3', '#e53935', '#9c27b0', '#3f51b5',
        '#00bcd4', '#009688', '#8bc34a', '#cddc39', '#ffeb3b'
    ];
    
    // If we need more colors than in our array, we'll repeat them
    $result = [];
    for ($i = 0; $i < $count; $i++) {
        $result[] = $colors[$i % count($colors)];
    }
    
    return $result;
}

$chart_colors = generateRandomColors(count($sales_distribution));

// Get current date
$current_date = date("F j, Y");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div class="admin-container">
        <div class="dashboard-header">
            <div class="greeting">
                <h1>Welcome back, <?= htmlspecialchars($username) ?>!</h1>
                <p>Here's what's happening with your store today.</p>
            </div>
            <div class="dashboard-date">
                <i class="far fa-calendar-alt"></i> <?= $current_date ?>
            </div>
        </div>

        <div class="dashboard-summary">
            <div class="summary-card total-sales">
                <i class="fas fa-chart-line"></i>
                <h4>Total Sales</h4>
                <p>₱<?= number_format($total_sales, 2) ?></p>
            </div>
            
            <div class="summary-card">
                <i class="fas fa-shopping-bag"></i>
                <h4>Total Products</h4>
                <p><?= number_format($total_products) ?></p>
            </div>
            
            <div class="summary-card">
                <i class="fas fa-shopping-cart"></i>
                <h4>Total Orders</h4>
                <p><?= number_format($total_orders) ?></p>
            </div>
            
            <div class="summary-card">
                <i class="fas fa-exclamation-triangle"></i>
                <h4>Low Stock Items</h4>
                <p><?= count($low_stock) ?></p>
            </div>
        </div>

        <div class="stats">
            <div class="card">
                <h3><i class="fas fa-chart-pie"></i> Sales Distribution</h3>
                <div class="chart-container">
                    <canvas id="salesPieChart"></canvas>
                </div>
            </div>

            <div class="card">
                <h3><i class="fas fa-star"></i> Top Selling Products</h3>
                <ul>
                    <?php $rank = 1; foreach ($top_products as $product): ?>
                        <li class="top-product">
                            <div class="top-product-rank"><?= $rank ?></div>
                            <span class="product-name"><?= htmlspecialchars($product['name']) ?></span>
                            <span class="product-count"><?= $product['total_sold'] ?> sold</span>
                        </li>
                    <?php $rank++; endforeach; ?>
                    
                    <?php if (empty($top_products)): ?>
                        <li>No sales data available yet.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="card">
                <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</h3>
                <ul>
                    <?php foreach ($low_stock as $product): ?>
                        <li class="alert-item">
                            <i class="fas fa-exclamation-circle"></i>
                            <span class="product-name"><?= htmlspecialchars($product['name']) ?></span>
                            <span class="stock-count"><?= $product['stock'] ?> left</span>
                        </li>
                    <?php endforeach; ?>
                    
                    <?php if (empty($low_stock)): ?>
                        <li>No low stock items at the moment.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="actions">
            <a href="../admin/manage_products.php" class="btn"><i class="fas fa-box"></i> Manage Products</a>
            <a href="../admin/view_sales.php" class="btn"><i class="fas fa-chart-bar"></i> View Sales</a>
            <a href="../auth/logout.php" class="btn logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <script>
        // Sales Distribution Pie Chart
        const ctx = document.getElementById('salesPieChart').getContext('2d');
        const salesData = {
            labels: <?= json_encode(array_column($sales_distribution, 'name')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($sales_distribution, 'total_sold')) ?>,
                backgroundColor: <?= json_encode($chart_colors) ?>,
                borderWidth: 1,
                borderColor: '#ffffff'
            }]
        };

        new Chart(ctx, {
            type: 'doughnut',
            data: salesData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' sold';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    </script>

</body>
</html>