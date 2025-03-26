<?php
session_start();
require '../config/database.php';

// Check if user is logged in and is Admin or Superadmin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'superadmin')) {
    header("Location: ../login.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_products.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: manage_products.php");
    exit();
}

// Update Product Logic
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barcode = $_POST['barcode'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Image Upload Handling
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_folder = "../uploads/" . $image;
        move_uploaded_file($image_tmp, $image_folder);
    } else {
        $image = $product['image']; // Keep existing image if none is uploaded
    }

    // Update query
    $update_stmt = $pdo->prepare("UPDATE products SET barcode=?, name=?, category=?, price=?, stock=?, image=?, updated_at=NOW() WHERE id=?");
    if ($update_stmt->execute([$barcode, $name, $category, $price, $stock, $image, $product_id])) {
        header("Location: manage_products.php?success=Product updated successfully!");
        exit();
    } else {
        $error = "Failed to update product!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../assets/css/manage_products.css">
</head>
<body>

<div class="container">
    <h2>Edit Product</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Barcode:</label>
        <input type="text" name="barcode" value="<?= $product['barcode'] ?>" required>

        <label>Product Name:</label>
        <input type="text" name="name" value="<?= $product['name'] ?>" required>

        <label>Category:</label>
        <input type="text" name="category" value="<?= $product['category'] ?>" required>

        <label>Price:</label>
        <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required>

        <label>Stock:</label>
        <input type="number" name="stock" value="<?= $product['stock'] ?>" required>

        <label>Product Image:</label>
        <input type="file" name="image">
        <?php if ($product['image']) : ?>
            <img src="../uploads/<?= $product['image'] ?>" width="100">
        <?php endif; ?>

        <button type="submit">Update Product</button>
        <a href="manage_products.php" class="back-btn">Back to Manage Products</a>
    </form>
</div>

</body>
</html>
