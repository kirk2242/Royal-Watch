<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$role = $_SESSION['role'];
?>

<nav class="navbar">
    <div class="logo">Time Emporium</div>
    <ul class="nav-links">
        <?php if ($role === 'superadmin'): ?>
            <li><a href="../superadmin/dashboard.php">Dashboard</a></li>
            <li><a href="../superadmin/manage_users.php">Manage Users</a></li>
            <li><a href="../superadmin/manage_products.php">Manage Products</a></li>
            <li><a href="../superadmin/sales_reports.php">Sales Reports</a></li>
        <?php elseif ($role === 'admin'): ?>
            <li><a href="../admin/dashboard.php">Dashboard</a></li>
            <li><a href="../admin/manage_products.php">Manage Products</a></li>
            <li><a href="../admin/sales_reports.php">Sales Reports</a></li>
        <?php elseif ($role === 'cashier'): ?>
            <li><a href="../cashier/dashboard.php">Dashboard</a></li>
            <li><a href="../cashier/pos.php">POS System</a></li>
        <?php elseif ($role === 'customer'): ?>
            <li><a href="../customer/dashboard.php">Dashboard</a></li>
            <li><a href="../customer/shop.php">Shop</a></li>
            <li><a href="../customer/cart.php">Cart</a></li>
        <?php endif; ?>
        <li><a href="../auth/logout.php" class="logout-btn">Logout</a></li>
    </ul>
</nav>
