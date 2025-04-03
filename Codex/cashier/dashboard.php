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
        <header class="dashboard-header">
            <h2>Welcome, Cashier!</h2>
            <p>Manage your sales and transactions efficiently.</p>
        </header>

        <div class="actions dashboard-grid">
            <div class="card dashboard-card">
                <a href="cashier_pos.php" class="btn">Process Sale</a>
            </div>
            <div class="card dashboard-card">
                <a href="../auth/logout.php" class="btn logout">Logout</a>
            </div>
        </div>
    </div>

</body>
</html>
