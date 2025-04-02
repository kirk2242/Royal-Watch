<?php
session_start();
require '../config/database.php';

// Ensure only Admin or Superadmin can access
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../login.php");
    exit();
}

$message = "";

// Handle product addition
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);
    $barcode = trim($_POST['barcode']);
    
    // Image upload handling
    $imagePath = "";
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "../uploads/";
        $imageName = basename($_FILES["image"]["name"]);
        $imagePath = $targetDir . time() . "_" . $imageName;

        // Move uploaded file
        if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            $message = "Failed to upload image.";
        }
    }

    // Insert product into database
    $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, barcode, image) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $category, $price, $stock, $barcode, $imagePath])) {
        $message = "Product added successfully!";
    } else {
        $message = "Failed to add product.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="../assets/css/manage_products.css">
</head>
<body>

<div class="container">
    <h2>Add Product</h2>
    <?php if ($message): ?>
        <p style="color: green;"><?= $message ?></p>
    <?php endif; ?>

    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <label>Product Name:</label>
        <input type="text" name="name" required>

        <label>Category:</label>
        <input type="text" name="category" required>

        <label>Price:</label>
        <input type="number" name="price" step="0.01" required>

        <label>Stock:</label>
        <input type="number" name="stock" required>

        <label>Barcode:</label>
        <input type="text" name="barcode" required>

        <label>Product Image:</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Add Product</button>
    </form>

    <a href="manage_products.php" class="back-btn">Back to Manage Products</a>
</div>

</body>
</html>
