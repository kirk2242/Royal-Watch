<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch total sales
$stmt = $pdo->query("SELECT SUM(total) AS total FROM sales");
$total_sales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report</title>
    <link rel="stylesheet" href="../assets/css/cashier_dashboard.css">
</head>
<body>

    <div class="cashier-container">
        <h2>Total Sales: ₱<?php echo number_format($total_sales, 2); ?></h2>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>

</body>
</html>
