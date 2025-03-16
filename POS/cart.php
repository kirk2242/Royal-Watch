<?php
session_start();
require 'config/database.php';

// Restrict access to cashiers only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'cashier') {
    header("Location: auth/login.php");
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity;
    } else {
        $_SESSION['cart'][$product_id] = $quantity;
    }
}

// Remove item from cart
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
}

// Fetch cart details
$cart_items = [];
$total = 0;

if (!empty($_SESSION['cart'])) {
    $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute(array_keys($_SESSION['cart']));
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<h2>Shopping Cart</h2>
<a href="pos.php">Back to Products</a> | <a href="cart.php?clear=true">Clear Cart</a>

<table border="1">
    <tr>
        <th>Name</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Subtotal</th>
        <th>Action</th>
    </tr>
    <?php foreach ($cart_items as $item): 
        $subtotal = $item['price'] * $_SESSION['cart'][$item['id']];
        $total += $subtotal;
    ?>
    <tr>
        <td><?= $item['name'] ?></td>
        <td><?= $item['price'] ?></td>
        <td><?= $_SESSION['cart'][$item['id']] ?></td>
        <td><?= number_format($subtotal, 2) ?></td>
        <td><a href="cart.php?remove=<?= $item['id'] ?>">Remove</a></td>
    </tr>
    <?php endforeach; ?>
</table>

<h3>Total: <?= number_format($total, 2) ?></h3>
<a href="checkout.php">Proceed to Checkout</a>
