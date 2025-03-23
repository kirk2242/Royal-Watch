<?php
session_start();
require '../config/database.php';

// Ensure only admin/superadmin can access
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY name ASC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../assets/css/product.css">
</head>
<body>

<div class="container">
    <h2>Manage Products</h2>
    <a href="add_product.php" class="add-btn">Add New Product</a>

    <table>
        <tr>
            <th>Image</th>
            <th>Barcode</th>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($products as $product): ?>
        <tr>
            <td><img src="../uploads/<?= $product['image'] ?>" alt="Product Image" class="product-img"></td>
            <td><?= $product['barcode'] ?></td>
            <td><?= $product['name'] ?></td>
            <td>$<?= $product['price'] ?></td>
            <td><?= $product['stock'] ?></td>
            <td>
                <a href="edit_product.php?id=<?= $product['id'] ?>" class="edit-btn">Edit</a>
                <a href="delete_product.php?id=<?= $product['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <a href="../superadmin/dashboard_superadmin.php" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>
