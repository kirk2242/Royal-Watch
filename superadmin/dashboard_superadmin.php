
<?php
session_start();
require '../config/database.php';

// Restrict access to superadmin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get current month and year for filtering
$currentMonth = date('m');
$currentYear = date('Y');

// Fetch dashboard statistics
$stats = [
    'users' => 0,
    'products' => 0,
    'sales' => 0,
    'revenue' => 0,
    'recent_users' => [],
    'top_products' => []
];

try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $stats['users'] = $stmt->fetchColumn();
    
    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $stats['products'] = $stmt->fetchColumn();
    
    // Total sales (quantity)
    $stmt = $pdo->query("SELECT SUM(quantity) FROM sales_items");
    $stats['sales'] = $stmt->fetchColumn() ?: 0;
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(quantity * price) FROM sales_items");
    $stats['revenue'] = $stmt->fetchColumn() ?: 0;
    
    // Monthly revenue
    $stmt = $pdo->prepare("SELECT SUM(si.quantity * si.price) 
                          FROM sales_items si
                          JOIN sales s ON si.sale_id = s.id
                          WHERE MONTH(s.sale_date) = ? AND YEAR(s.sale_date) = ?");
    $stmt->execute([$currentMonth, $currentYear]);
    $stats['monthly_revenue'] = $stmt->fetchColumn() ?: 0;
    
    // Recent users (5 most recent)
    $stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
    $stats['recent_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top selling products
    $stmt = $pdo->query("SELECT p.id, p.name, p.price, SUM(si.quantity) as total_sold
                         FROM products p
                         JOIN sales_items si ON p.id = si.product_id
                         GROUP BY p.id
                         ORDER BY total_sold DESC
                         LIMIT 5");
    $stats['top_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Log error instead of displaying it
    error_log('Database error: ' . $e->getMessage());
    $error_message = "We're experiencing technical difficulties. Please try again later.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>Superadmin Panel</h2>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
            <nav class="sidebar-menu">
                <a href="dashboard_superadmin.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="manage_users.php" class="menu-item">
                    <i class="fas fa-users"></i> User Management
                </a>
                <a href="manage_products.php" class="menu-item">
                    <i class="fas fa-box"></i> Inventory Management
                </a>
                <a href="sales_report.php" class="menu-item">
                    <i class="fas fa-chart-line"></i> Sales Reports
                </a>
                <a href="../auth/logout.php" class="menu-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="header">
                <button class="toggle-sidebar" id="toggleSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-title">
                    <h1>Dashboard Overview</h1>
                    <p>Welcome back! Here's what's happening with your store today.</p>
                </div>
                <div class="user-info">
                    <div>
                        <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                        <small>Super Admin</small>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['users']); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['products']); ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon sales">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['sales']); ?></h3>
                        <p>Items Sold</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>P<?php echo number_format($stats['revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-container">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Sales Overview</h3>
                        <div class="chart-actions">
                            <select id="salesChartPeriod">
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>
                    <canvas id="salesChart"></canvas>
                </div>
                
                <div class="chart-card">
                    <div class="chart-header">
                        <h3 class="chart-title">Top Selling Products</h3>
                        <div class="chart-actions">
                            <select id="productChartType">
                                <option value="pie" selected>Pie Chart</option>
                                <option value="bar">Bar Chart</option>
                            </select>
                        </div>
                    </div>
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Fetch sales data for charts
        async function fetchData() {
            try {
                const response = await fetch('sales_data.php');
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return await response.json();
            } catch (error) {
                console.error('Error fetching data:', error);
                return { labels: [], values: [] };
            }
        }

        // Initialize charts
        async function initCharts() {
            const data = await fetchData();
            
            // Sales Chart
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const salesChart = new Chart(salesCtx, {
                type: 'line',
                data: {
                    labels: data.months || ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Sales Revenue',
                        data: data.revenue || [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        borderColor: '#4a6cf7',
                        backgroundColor: 'rgba(74, 108, 247, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return `Revenue: $${context.raw.toLocaleString()}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            
            // Products Chart
            const productsCtx = document.getElementById('productsChart').getContext('2d');
            const productsChart = new Chart(productsCtx, {
                type: 'pie',
                data: {
                    labels: data.labels || ['No Data'],
                    datasets: [{
                        label: 'Units Sold',
                        data: data.values || [0],
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
                            position: 'right',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw.toLocaleString()} units`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Handle chart type change
            document.getElementById('productChartType').addEventListener('change', function(e) {
                productsChart.destroy();
                const newType = e.target.value;
                
                const newChart = new Chart(productsCtx, {
                    type: newType,
                    data: {
                        labels: data.labels || ['No Data'],
                        datasets: [{
                            label: 'Units Sold',
                            data: data.values || [0],
                            backgroundColor: newType === 'pie' ? 
                                ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'] :
                                'rgba(74, 108, 247, 0.7)',
                            borderColor: newType === 'pie' ? undefined : '#4a6cf7',
                            borderWidth: newType === 'pie' ? undefined : 1,
                            hoverOffset: newType === 'pie' ? 4 : undefined
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: newType === 'pie' ? 'right' : 'top',
                                display: newType === 'pie'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.label}: ${context.raw.toLocaleString()} units`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                display: newType !== 'pie'
                            },
                            x: {
                                display: newType !== 'pie'
                            }
                        }
                    }
                });
            });
            
            // Handle sales chart period change
            document.getElementById('salesChartPeriod').addEventListener('change', function(e) {
                const period = e.target.value;
                // In a real application, you would fetch new data based on the period
                // For this example, we'll just simulate different data
                
                let newLabels, newData;
                
                switch(period) {
                    case 'weekly':
                        newLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        newData = [1200, 1900, 1500, 2000, 2400, 3000, 2500];
                        break;
                    case 'yearly':
                        newLabels = ['2018', '2019', '2020', '2021', '2022', '2023'];
                        newData = [25000, 35000, 30000, 45000, 55000, 60000];
                        break;
                    default: // monthly
                        newLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        newData = [5000, 6000, 4500, 5500, 7000, 8000, 9000, 8500, 7500, 10000, 11000, 12000];
                }
                
                salesChart.data.labels = newLabels;
                salesChart.data.datasets[0].data = newData;
                salesChart.update();
            });
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
        });
    </script>
</body>
</html>
</qodoArtifact>