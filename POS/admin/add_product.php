<?php
session_start();
require '../config/database.php';

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Check for duplicate product name
    $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ?");
    $stmt->execute([$name]);

    if ($stmt->rowCount() > 0) {
        $error = "Product name already exists!";
    } else {
        // Insert new product
        $stmt = $pdo->prepare("INSERT INTO products (name, price, stock, image) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $price, $stock, $image])) {
            header("Location: manage_products.php?success=Product added");
            exit();
        } else {
            echo "Error adding product.";
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
    <link rel="stylesheet" href="addP_style.css">
</head>
<body>

<div class="container">
    <!-- Back Button -->
    <a href="manage_products.php" class="back-btn">⬅ Back to Manage Products</a>

    <h2>🛍 Add Product</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <label>Product Name:</label>
        <input type="text" name="name" required>

        <label>Price:</label>
        <input type="number" name="price" step="0.01" required>

        <label>Stock:</label>
        <input type="number" name="stock" required>

        <label>Product Image:</label>
        <input type="file" name="image" accept="image/*" required>

        <button type="submit">➕ Add Product</button>
    </form>
</div>

</body>
</html>
