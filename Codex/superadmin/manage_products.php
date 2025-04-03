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
    <link rel="stylesheet" href="../assets/css/manage_products.css">
</head>
<body>

<div class="container">
    <h2>Manage Products</h2>
    <a href="add_product.php" class="add-btn">Add New Product</a>

    <table border="1">
    <tr>
        <th>Image</th>
        <th>Barcode</th>
        <th>Name</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Actions</th>
    </tr>

    <?php
    require '../config/database.php';
    $stmt = $pdo->query("SELECT * FROM products");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    ?>
        <tr>
            <td>
                <?php if (!empty($row['image'])) : ?>
                    <img src="../uploads/<?= htmlspecialchars($row['image']); ?>" width="50">
                <?php else : ?>
                    <span>No Image</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['barcode']); ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['category']); ?></td>
            <td><?= htmlspecialchars($row['price']); ?></td>
            <td><?= htmlspecialchars($row['stock']); ?></td>
            <td>
                <a href="edit_product.php?id=<?= $row['id']; ?>">Edit</a> | 
                <a href="delete_product.php?id=<?= $row['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
    <?php
    }
    ?>
</table>


    <a href="../superadmin/dashboard_superadmin.php" class="back-btn">Back to Dashboard</a>
</div>

</body>
</html>
