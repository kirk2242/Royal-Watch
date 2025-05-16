<?php
session_start();
require '../config/database.php';

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Initialize filter variables
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build the query based on filters
$query = "SELECT sales.id, sales.created_at, sales.total, users.username 
          FROM sales 
          JOIN users ON sales.user_id = users.id";

$where_clauses = [];
$params = [];

if (!empty($start_date)) {
    $where_clauses[] = "sales.created_at >= ?";
    $params[] = $start_date . " 00:00:00";
}

if (!empty($end_date)) {
    $where_clauses[] = "sales.created_at <= ?";
    $params[] = $end_date . " 23:59:59";
}

if (!empty($search)) {
    $where_clauses[] = "(users.username LIKE ? OR sales.id LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

$query .= " ORDER BY sales.created_at DESC";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Get total count for pagination
$count_stmt = $pdo->prepare(str_replace("sales.id, sales.created_at, sales.total, users.username", "COUNT(*) as count", $query));
$count_stmt->execute($params);
$total_count = $count_stmt->fetch(PDO::FETCH_ASSOC)['count'];
$total_pages = ceil($total_count / $per_page);

// Add pagination to the query
$query .= " LIMIT $per_page OFFSET $offset";

// Fetch sales data
$sales_stmt = $pdo->prepare($query);
$sales_stmt->execute($params);
$sales = $sales_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate sales summary
$summary_query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(total) as total_sales,
                    AVG(total) as avg_sale
                  FROM sales";

if (!empty($where_clauses)) {
    $summary_query .= " WHERE " . implode(" AND ", $where_clauses);
}

$summary_stmt = $pdo->prepare($summary_query);
$summary_stmt->execute($params);
$summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Sales</title>
    <link rel="stylesheet" href="../assets/css/view_sales.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <div class="admin-container">
        <h2>Sales Transactions</h2>

        <!-- Sales Summary -->
        <div class="sales-summary">
            <div class="summary-card total-sales-card">
                <h4>Total Sales</h4>
                <p>₱<?= number_format($summary['total_sales'] ?? 0, 2) ?></p>
            </div>
            <div class="summary-card avg-sales-card">
                <h4>Average Sale</h4>
                <p>₱<?= number_format($summary['avg_sale'] ?? 0, 2) ?></p>
            </div>
            <div class="summary-card transactions-card">
                <h4>Transactions</h4>
                <p><?= number_format($summary['total_transactions'] ?? 0) ?></p>
            </div>
        </div>

        <!-- Filter Section -->
        <form method="GET" class="filter-section">
            <div class="search-box">
                <input type="text" name="search" placeholder="Search by ID or username" value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="date-filter">
                <label for="start_date">From:</label>
                <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                <label for="end_date">To:</label>
                <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
            </div>
            <button type="submit" class="filter-btn">Filter</button>
            <a href="view_sales.php" class="reset-btn">Reset</a>
        </form>

        <!-- Sales Table -->
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
                            <td><?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?></td>
                            <td>₱<?= number_format($sale['total'], 2) ?></td>
                            <td><?= htmlspecialchars($sale['username']) ?></td>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($sales)): ?>
                        <tr>
                            <td colspan="5" class="empty-state">No sales data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>">
                        &laquo; Previous
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>" 
                       class="<?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>">
                        Next &raquo;
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Button Group -->
        <div class="btn-group">
            <a href="dashboard.php" class="btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <button class="btn export-btn" onclick="printSalesReport()">
                <i class="fas fa-print"></i> Print Report
            </button>
        </div>
    </div>

    <!-- Sale Details Modal -->
    <div id="saleDetailsModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeSaleDetailsModal()">&times;</span>
            <h3 class="modal-title">Sale Details</h3>
            <div id="saleDetailsContent">
                <!-- Sale details will be loaded here via AJAX -->
                <p>Loading...</p>
            </div>
        </div>
    </div>

    <script>
        
        // Function to close the modal
        function closeSaleDetailsModal() {
            document.getElementById('saleDetailsModal').style.display = 'none';
        }
        
        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('saleDetailsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Function to print the sales report
        function printSalesReport() {
            window.print();
        }
    </script>

</body>
</html></qodoArtifact>