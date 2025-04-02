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

// Fetch sales distribution by product
$sales_distribution_stmt = $pdo->query("SELECT products.name, SUM(sales_items.quantity) AS total_sold 
                                        FROM sales_items 
                                        JOIN products ON sales_items.product_id = products.id 
                                        GROUP BY products.name");
$sales_distribution = $sales_distribution_stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <h2>Admin Dashboard</h2>

        <div class="stats">
            <div class="card">
                <h3><i class="fas fa-chart-line"></i> Total Sales</h3>
                <p>₱<?php echo number_format($total_sales, 2); ?></p>
                <canvas id="salesPieChart" width="400" height="400"></canvas>
            </div>

            <div class="card">
                <h3><i class="fas fa-star"></i> Top Selling Products</h3>
                <ul>
                    <?php foreach ($top_products as $product): ?>
                        <li><?php echo $product['name'] . " - " . $product['total_sold'] . " sold"; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="card">
                <h3><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</h3>
                <ul>
                    <?php foreach ($low_stock as $product): ?>
                        <li><?php echo $product['name'] . " - " . $product['stock'] . " left"; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="actions">
            <a href="manage_products.php" class="btn"><i class="fas fa-box"></i> Manage Products</a>
            <a href="../auth/logout.php" class="btn logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('salesPieChart').getContext('2d');
        const salesData = {
            labels: <?php echo json_encode(array_column($sales_distribution, 'name')); ?>,
            datasets: [{
                label: 'Sales Distribution',
                data: <?php echo json_encode(array_column($sales_distribution, 'total_sold')); ?>,
                backgroundColor: [
                    '#4facfe', '#00f2fe', '#ff5e62', '#ff9966', '#4caf50',
                    '#ffa500', '#2196f3', '#e53935', '#9c27b0', '#3f51b5'
                ],
                borderWidth: 1
            }]
        };

        new Chart(ctx, {
            type: 'pie',
            data: salesData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw + ' sold';
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
