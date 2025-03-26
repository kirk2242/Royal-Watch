
<?php
session_start();
require '../config/database.php';

// Ensure only cashiers can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../auth/login.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="../assets/css/cashier_dashboard.css">
</head>
<body>

    <div class="cashier-container">
        <h2>Welcome, Cashier!</h2>

        <div class="actions">
            <a href="cashier_pos.php" class="btn">Process Sale</a>
            <a href="sales_report.php" class="btn">View Sales</a>
            <a href="../auth/logout.php" class="btn logout">Logout</a>
        </div>

    </div>

</body>
</html>
