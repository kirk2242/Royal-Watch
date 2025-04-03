<?php
session_start();
require '../config/database.php';

// Restrict access to superadmin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Sales Chart -->
</head>
<body>

    <header>
        <h2>Superadmin Dashboard</h2>
        <nav>
            <a href="manage_users.php">User Management</a>
            <a href="manage_products.php">Product Management</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>
    </header>

    <main class="dashboard-container">
        <section class="dashboard-card">
            <h3>Total Users</h3>
            <p>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                echo $stmt->fetchColumn();
                ?>
            </p>
        </section>

        <section class="dashboard-card">
            <h3>Total Products</h3>
            <p>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM products");
                echo $stmt->fetchColumn();
                ?>
            </p>
        </section>

        <section class="dashboard-card">
            <h3>Total Sales</h3>
            <p>
                <?php
$stmt = $pdo->query("SELECT SUM(quantity * price) FROM sales_items");
echo number_format($stmt->fetchColumn(), 2);
                ?>
            </p>
        </section>

        <section class="chart-container">
            <h3>Sales Report</h3>
            <canvas id="salesChart"></canvas>
        </section>
    </main>

    <script>
        fetch('sales_data.php')
            .then(response => response.json())
            .then(data => {
                const ctx = document.getElementById('salesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'pie', // Changed to pie chart
                    data: {
                        labels: data.labels, // Item names
                        datasets: [{
                            label: 'Items Sold',
                            data: data.values, // Quantities sold
                            backgroundColor: [
                                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                            ],
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(tooltipItem) {
                                        return `${tooltipItem.label}: ${tooltipItem.raw} units`;
                                    }
                                }
                            }
                        }
                    }
                });
            });
    </script>

</body>
</html>
