<?php
session_start();
require '../config/database.php';

// Restrict access to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch product details
$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Product not found!");
}

// Handle update request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $price = trim($_POST['price']);
    $stock = trim($_POST['stock']);

    // Check for duplicate product name
    $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);

    if ($stmt->rowCount() > 0) {
        $error = "Product name already exists!";
    } else {
        // Update product details
        $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ?");
        if ($stmt->execute([$name, $price, $stock, $id])) {
            header("Location: manage_products.php");
            exit();
        } else {
            $error = "Failed to update product!";
        }
    }
}
?>

<h2>Edit Product</h2>
<?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

<form method="POST">
    <label>Name:</label>
    <input type="text" name="name" value="<?= $product['name'] ?>" required>
    <br>
    <label>Price:</label>
    <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required>
    <br>
    <label>Stock:</label>
    <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
    <br>
    <button type="submit">Update Product</button>
</form>
<a href="manage_products.php">Back to Products</a>
