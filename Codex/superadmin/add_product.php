<?php
session_start();
require '../config/database.php';

// Ensure only admin/superadmin can access
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barcode = $_POST['barcode'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $image = 'default.png';

    // Handle Image Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageName = time() . '_' . $_FILES['image']['name'];
        $targetDir = "../uploads/";
        $targetFile = $targetDir . basename($imageName);

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = $imageName;
        }
    }

    // Check for duplicate barcode or name
    $stmt = $pdo->prepare("SELECT id FROM products WHERE barcode = ? OR name = ?");
    $stmt->execute([$barcode, $name]);

    if ($stmt->rowCount() > 0) {
        $error = "Product barcode or name already exists!";
    } else {
        // Insert new product
        $stmt = $pdo->prepare("INSERT INTO products (barcode, name, price, stock, image) VALUES (?, ?, ?, ?, ?)");
        if ($stmt->execute([$barcode, $name, $price, $stock, $image])) {
            header("Location: manage_products.php?success=Product added");
            exit();
        } else {
            $error = "Error adding product!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="../assets/css/product.css">
</head>
<body>

<div class="container">
    <h2>Add Product</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <label>Barcode:</label>
        <input type="text" name="barcode" required>

        <label>Product Name:</label>
        <input type="text" name="name" required>

        <label>Price:</label>
        <input type="number" name="price" step="0.01" required>

        <label>Stock:</label>
        <input type="number" name="stock" required>

        <label>Product Image:</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit">Add Product</button>
    </form>

    <a href="manage_products.php" class="back-btn">Back to Manage Products</a>
</div>

</body>
</html>
