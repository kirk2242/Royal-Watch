<?php
session_start();
require '../config/database.php';

// Restrict access to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Fetch the image file to delete
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete the image file if it's not the default image
    if ($product && $product['image'] !== 'default.png') {
        $imagePath = "../uploads/" . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete product from database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: manage_products.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<a href="dashboard.php" class="back-button">⬅ Back to Admin Dashboard</a>

<h2>Manage Products</h2>
<a href="add_product.php" class="add-button">+ Add Product</a>

<table>
    <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Name</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Action</th>
    </tr>
    <?php foreach ($products as $product): ?>
    <tr>
        <td><?= $product['id'] ?></td>
        <td><img src="../uploads/<?= htmlspecialchars($product['image']) ?>" onerror="this.onerror=null; this.src='../uploads/default.png';" alt="Product Image"></td>
        <td><?= $product['name'] ?></td>
        <td>$<?= number_format($product['price'], 2) ?></td>
        <td><?= $product['stock'] ?></td>
        <td>
            <a href="edit_product.php?id=<?= $product['id'] ?>">✏ Edit</a> |
            <a href="manage_products.php?delete=<?= $product['id'] ?>" onclick="return confirm('Are you sure?')">🗑 Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
