<?php
session_start();
require 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit();
}

// Set role-based title
$dashboard_title = $_SESSION['role'] === 'admin' ? "Admin Dashboard" : "Cashier Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $dashboard_title ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard-container">
    <h2><?= $dashboard_title ?></h2>

    <div class="nav-buttons">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin/manage_products.php">📦 Manage Products</a>
            <a href="admin/dashboard.php">📊 View Sales Reports</a>
        <?php elseif ($_SESSION['role'] === 'cashier'): ?>
            <a href="pos.php">🛒 Start POS System</a>
        <?php endif; ?>
        <a href="auth/logout.php" class="logout-btn">🚪 Logout</a>
    </div>
</div>

</body>
</html>
