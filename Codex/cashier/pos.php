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
    <title>Point of Sale</title>
    <link rel="stylesheet" href="../assets/css/pos.css">
</head>
<body>

    <div class="pos-container">
        <h2>Point of Sale System</h2>

        <form method="POST" action="process_sale.php">
            <label>Scan Barcode:</label>
            <input type="text" name="barcode" required autofocus>

            <label>Quantity:</label>
            <input type="number" name="quantity" min="1" required>

            <button type="submit">Add to Cart</button>
        </form>

        <a href="dashboard.php" class="btn">Back to Dashboard</a>
    </div>

</body>
</html>
