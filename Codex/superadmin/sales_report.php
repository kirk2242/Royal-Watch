
<?php
session_start();
require '../config/database.php';

// Ensure only superadmins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Initialize filter variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_applied = !empty($start_date) || !empty($end_date);

// Base query
$query = "SELECT sales.id, sales.created_at, sales.total, users.username 
          FROM sales 
          JOIN users ON sales.user_id = users.id";

// Add date filters if provided
if ($filter_applied) {
    $conditions = [];
    
    if (!empty($start_date)) {
        $conditions[] = "sales.created_at >= '$start_date 00:00:00'";
    }
    
    if (!empty($end_date)) {
        $conditions[] = "sales.created_at <= '$end_date 23:59:59'";
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
}

$query .= " ORDER BY sales.created_at DESC";

// Fetch sales data
$sales_stmt = $pdo->query($query);
$sales = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total revenue and total sales
$total_revenue = 0;
$total_sales = count($sales);
$avg_sale = 0;

foreach ($sales as $sale) {
    $total_revenue += $sale['total'];
}

// Calculate average sale if there are sales
if ($total_sales > 0) {
    $avg_sale = $total_revenue / $total_sales;
}

// Get current date for report header
$current_date = date("F j, Y");

// Get date range for subtitle
$date_range = "All Time";
if ($filter_applied) {
    if (!empty($start_date) && !empty($end_date)) {
        $date_range = date("M j, Y", strtotime($start_date)) . " - " . date("M j, Y", strtotime($end_date));
    } elseif (!empty($start_date)) {
        $date_range = "From " . date("M j, Y", strtotime($start_date));
    } elseif (!empty($end_date)) {
        $date_range = "Until " . date("M j, Y", strtotime($end_date));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="../assets/css/sales_report.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <div class="admin-container">
        <div class="report-header">
            <div class="report-title">
                <h2>Sales Report</h2>
                <span class="report-subtitle"><?= $date_range ?></span>
            </div>
            <div class="report-date">
                <i class="far fa-calendar-alt"></i> Generated on: <?= $current_date ?>
            </div>
        </div>

        <div class="filter-section">
            <form method="GET" action="" class="date-filter">
                <label for="start_date">From:</label>
                <input type="date" id="start_date" name="start_date" value="<?= $start_date ?>">
                
                <label for="end_date">To:</label>
                <input type="date" id="end_date" name="end_date" value="<?= $end_date ?>">
                
                <button type="submit" class="filter-btn">Apply Filter</button>
                <?php if ($filter_applied): ?>
                    <a href="sales_report.php" class="btn" style="background-color: #e74c3c;">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="sales-summary">
            <div class="summary-card total-sales-card">
                <h4>Total Revenue</h4>
                <p>₱<?= number_format($total_revenue, 2) ?></p>
            </div>
            <div class="summary-card transactions-card">
                <h4>Total Transactions</h4>
                <p><?= number_format($total_sales) ?></p>
            </div>
            <div class="summary-card">
                <h4>Average Sale</h4>
                <p>₱<?= number_format($avg_sale, 2) ?></p>
            </div>
        </div>

        <?php if (!empty($sales)): ?>
        <div class="chart-section">
            <h3>Sales Distribution</h3>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <?php endif; ?>

        <div class="sales-table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>User</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['id']) ?></td>
                            <td><?= date("M j, Y, g:i a", strtotime($sale['created_at'])) ?></td>
                            <td>₱<?= number_format($sale['total'], 2) ?></td>
                            <td><?= htmlspecialchars($sale['username']) ?></td>
                            <td>
                                <a href="view_sale_details.php?id=<?= $sale['id'] ?>" class="view-details-btn">
                                    <i class="fas fa-eye"></i> Details
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No sales data available for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="btn-group">
            <button class="btn export-btn" onclick="exportTableToCSV('sales_report_<?= date('Y-m-d') ?>.csv')">
                <i class="fas fa-file-csv"></i> Export to CSV
            </button>
            <button class="btn" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
            </button>
            <a href="dashboard_superadmin.php" class="btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (!empty($sales)): ?>
    <script>
        // Prepare data for chart
        const dates = [];
        const amounts = [];
        
        <?php
        // Group sales by date for the chart
        $chart_data = [];
        foreach ($sales as $sale) {
            $date = date("M j", strtotime($sale['created_at']));
            if (!isset($chart_data[$date])) {
                $chart_data[$date] = 0;
            }
            $chart_data[$date] += $sale['total'];
        }
        
        // Get the last 7 days or all days if less than 7
        $days_to_show = array_slice(array_keys($chart_data), 0, 7);
        foreach ($days_to_show as $day) {
            echo "dates.push('$day');\n";
            echo "amounts.push(" . $chart_data[$day] . ");\n";
        }
        ?>
        
        // Create the chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dates.reverse(),
                datasets: [{
                    label: 'Sales Amount (₱)',
                    data: amounts.reverse(),
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Export table to CSV
        function exportTableToCSV(filename) {
            const csv = [];
            const rows = document.querySelectorAll("table tr");
            
            for (let i = 0; i < rows.length; i++) {
                const row = [];
                const cols = rows[i].querySelectorAll("td, th");
                
                for (let j = 0; j < cols.length - 1; j++) { // Skip the Actions column
                    // Clean the text (remove commas to avoid CSV issues)
                    let text = cols[j].innerText.replace(/,/g, ' ');
                    row.push('"' + text + '"');
                }
                
                csv.push(row.join(","));        
            }

            // Download CSV
            const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
            const downloadLink = document.createElement("a");
            downloadLink.download = filename;
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = "none";
            document.body.appendChild(downloadLink);
            downloadLink.click();
        }
    </script>
    <?php endif; ?>

</body>
</html>
</qodoArtifact>