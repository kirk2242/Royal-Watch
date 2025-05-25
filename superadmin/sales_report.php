
<?php
session_start();
require '../config/database.php';

// Ensure only superadmins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Pagination settings
$rows_per_page = 15; // Changed from 10 to 15
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $rows_per_page;

// Initialize filter variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$filter_applied = !empty($start_date) || !empty($end_date);

// Base query
$query_base = "FROM sales JOIN users ON sales.user_id = users.id";
$conditions = [];
$params = [];

// Add date filters if provided
if (!empty($start_date)) {
    $conditions[] = "sales.created_at >= :start_date";
    $params[':start_date'] = $start_date . ' 00:00:00';
}
if (!empty($end_date)) {
    $conditions[] = "sales.created_at <= :end_date";
    $params[':end_date'] = $end_date . ' 23:59:59';
}

$where_clause = '';
if (!empty($conditions)) {
    $where_clause = " WHERE " . implode(" AND ", $conditions);
}

// Get total sales count for pagination
$count_query = "SELECT COUNT(*) as total " . $query_base . $where_clause;
$count_stmt = $pdo->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_sales = (int)$count_stmt->fetchColumn();
$total_pages = max(1, ceil($total_sales / $rows_per_page));

// Fetch paginated sales data
$query = "SELECT sales.id, sales.created_at, sales.total, users.username " . $query_base . $where_clause . " ORDER BY sales.created_at DESC LIMIT :limit OFFSET :offset";
$sales_stmt = $pdo->prepare($query);
foreach ($params as $key => $value) {
    $sales_stmt->bindValue($key, $value);
}
$sales_stmt->bindValue(':limit', $rows_per_page, PDO::PARAM_INT);
$sales_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$sales_stmt->execute();
$sales = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total revenue and average sale for the filtered period (not just current page)
$revenue_query = "SELECT SUM(sales.total) as total_revenue FROM sales " . ($where_clause ? $where_clause : "");
$revenue_stmt = $pdo->prepare($revenue_query);
foreach ($params as $key => $value) {
    $revenue_stmt->bindValue($key, $value);
}
$revenue_stmt->execute();
$total_revenue = (float)$revenue_stmt->fetchColumn();

$avg_sale = $total_sales > 0 ? $total_revenue / $total_sales : 0;

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

// Helper for pagination links (preserve filters)
function build_query($params, $overrides = []) {
    $merged = array_merge($params, $overrides);
    return http_build_query(array_filter($merged, function($v) { return $v !== ''; }));
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
    <style>
        .pagination {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            gap: 5px;
        }
        .pagination a, .pagination span {
            padding: 6px 12px;
            border: 1px solid #ddd;
            color: #3498db;
            text-decoration: none;
            border-radius: 4px;
            background: #fff;
        }
        .pagination .active {
            background: #3498db;
            color: #fff;
            border-color: #3498db;
        }
        .pagination .disabled {
            color: #ccc;
            pointer-events: none;
            background: #f9f9f9;
        }
    </style>
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
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                
                <label for="end_date">To:</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                
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

        <?php if ($total_sales > 0): ?>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr>
                            <td><?= htmlspecialchars($sale['id']) ?></td>
                            <td><?= date("M j, Y, g:i a", strtotime($sale['created_at'])) ?></td>
                            <td>₱<?= number_format($sale['total'], 2) ?></td>
                            <td><?= htmlspecialchars($sale['username']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No sales data available for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php
            $query_params = [
                'start_date' => $start_date,
                'end_date' => $end_date
            ];
            // Previous button
            if ($page > 1) {
                echo '<a href="sales_report.php?' . build_query($query_params, ['page' => $page - 1]) . '">&laquo; Prev</a>';
            } else {
                echo '<span class="disabled">&laquo; Prev</span>';
            }

            // Page numbers (show up to 5 pages around current)
            $start_page = max(1, $page - 2);
            $end_page = min($total_pages, $page + 2);
            if ($start_page > 1) {
                echo '<a href="sales_report.php?' . build_query($query_params, ['page' => 1]) . '">1</a>';
                if ($start_page > 2) echo '<span>...</span>';
            }
            for ($i = $start_page; $i <= $end_page; $i++) {
                if ($i == $page) {
                    echo '<span class="active">' . $i . '</span>';
                } else {
                    echo '<a href="sales_report.php?' . build_query($query_params, ['page' => $i]) . '">' . $i . '</a>';
                }
            }
            if ($end_page < $total_pages) {
                if ($end_page < $total_pages - 1) echo '<span>...</span>';
                echo '<a href="sales_report.php?' . build_query($query_params, ['page' => $total_pages]) . '">' . $total_pages . '</a>';
            }

            // Next button
            if ($page < $total_pages) {
                echo '<a href="sales_report.php?' . build_query($query_params, ['page' => $page + 1]) . '">Next &raquo;</a>';
            } else {
                echo '<span class="disabled">Next &raquo;</span>';
            }
            ?>
        </div>
        <?php endif; ?>

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

    <?php if ($total_sales > 0): ?>
    <script>
        // Prepare data for chart
        const dates = [];
        const amounts = [];
        <?php
        // For the chart, show sales distribution for the filtered period (not just current page)
        $chart_query = "SELECT DATE(sales.created_at) as sale_date, SUM(sales.total) as total_amount
                        FROM sales
                        " . ($where_clause ? $where_clause : "") . "
                        GROUP BY sale_date
                        ORDER BY sale_date DESC
                        LIMIT 7";
        $chart_stmt = $pdo->prepare($chart_query);
        foreach ($params as $key => $value) {
            $chart_stmt->bindValue($key, $value);
        }
        $chart_stmt->execute();
        $chart_data = $chart_stmt->fetchAll(PDO::FETCH_ASSOC);

        $chart_data = array_reverse($chart_data); // Show oldest to newest
        foreach ($chart_data as $row) {
            $label = date("M j", strtotime($row['sale_date']));
            echo "dates.push('$label');\n";
            echo "amounts.push(" . (float)$row['total_amount'] . ");\n";
        }
        ?>
        // Create the chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Sales Amount (₱)',
                    data: amounts,
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
                for (let j = 0; j < cols.length; j++) {
                    let text = cols[j].innerText.replace(/,/g, ' ');
                    row.push('"' + text + '"');
                }
                csv.push(row.join(","));
            }
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
