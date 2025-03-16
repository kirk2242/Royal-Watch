<?php
session_start();
require 'config/database.php';

// Restrict access to cashiers only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cashier') {
    header("Location: auth/login.php");
    exit();
}

// Ensure a sale ID is provided
if (!isset($_GET['sale_id'])) {
    header("Location: pos.php");
    exit();
}

$sale_id = $_GET['sale_id'];

// Fetch sale details
$stmt = $pdo->prepare("SELECT sales.*, users.username FROM sales 
                       JOIN users ON sales.user_id = users.id 
                       WHERE sales.id = ?");
$stmt->execute([$sale_id]);
$sale = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch sale items
$stmt = $pdo->prepare("SELECT sale_items.*, products.name, products.price 
                       FROM sale_items 
                       JOIN products ON sale_items.product_id = products.id 
                       WHERE sale_items.sale_id = ?");
$stmt->execute([$sale_id]);
$sale_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt #<?= $sale_id ?></title>
    <script>
        function printReceipt() {
            window.print();
        }
    </script>
</head>
<body>
    <h2>Time Emporium - Receipt</h2>
    <p><strong>Sale ID:</strong> <?= $sale['id'] ?></p>
    <p><strong>Cashier:</strong> <?= $sale['username'] ?></p>
    <p><strong>Date:</strong> <?= $sale['created_at'] ?></p>

    <table border="1">
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($sale_items as $item): ?>
        <tr>
            <td><?= $item['name'] ?></td>
            <td><?= number_format($item['price'], 2) ?></td>
            <td><?= $item['quantity'] ?></td>
            <td><?= number_format($item['subtotal'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Total: <?= number_format($sale['total_amount'], 2) ?></h3>

    <button onclick="printReceipt()">Print Receipt</button>
    <a href="pos.php">Back to POS</a>
</body>
</html>
