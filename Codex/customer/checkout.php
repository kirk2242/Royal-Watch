<?php
session_start();
require '../config/database.php';

// Check if the cart exists
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty. <a href='home.php'>Continue Shopping</a></p>";
    exit();
}

// Initialize total amount
$totalAmount = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../assets/css/checkout.css">
</head>
<body>

<div class="checkout-container">
    <h2>Checkout</h2>

    <table>
        <tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Price</th>
            <th>Total</th>
            <th>Action</th>
        </tr>

        <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
            <?php if (!is_array($item)) continue; ?>

            <?php
            $productName = isset($item['name']) ? htmlspecialchars($item['name']) : 'Unknown Product';
            $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
            $price = isset($item['price']) ? floatval($item['price']) : 0;
            $totalPrice = $price * $quantity;
            $totalAmount += $totalPrice;
            ?>

            <tr>
                <td><?= $productName ?></td>
                <td><?= $quantity ?></td>
                <td>$<?= number_format($price, 2) ?></td>
                <td>$<?= number_format($totalPrice, 2) ?></td>
                <td>
                    <a href="remove_item.php?id=<?= $productId ?>">Remove</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <h3>Total Amount: $<?= number_format($totalAmount, 2) ?></h3>

    <form action="process_checkout.php" method="POST">
        <button type="submit">Proceed to Payment</button>
    </form>

    <button onclick="window.location.href='home.php'">Back to Home</button>
</div>

</body>
</html>
