<?php
session_start();
require '../config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="../assets/css/cart.css">
</head>
<body>

<div class="cart-container">
    <h2>Your Shopping Cart</h2>

    <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
        <div style="overflow-x: auto; width: 100%;">
            <table>
                <tr>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>

                <?php
                $totalAmount = 0;
                foreach ($_SESSION['cart'] as $productId => $item): 
                    if (!is_array($item)) continue;

                    // Retrieve product details
                    $productName = htmlspecialchars($item['name'] ?? 'Unknown Product');
                    $productImage = htmlspecialchars($item['image'] ?? 'default.jpg'); // Default image if missing
                    $quantity = intval($item['quantity']);
                    $price = floatval($item['price']);
                    $totalPrice = $price * $quantity;
                    $totalAmount += $totalPrice;
                ?>

                <tr>
                    <td><?= $productName ?></td>
                    <td>
                        <img src="../uploads/<?= $productImage ?>" alt="<?= $productName ?>" width="50">
                    </td>
                    <td><?= $quantity ?></td>
                    <td> ₱<?= number_format($price, 2) ?></td>
                    <td> ₱<?= number_format($totalPrice, 2) ?></td>
                    <td>
                        <a href="remove_item.php?id=<?= $productId ?>">Remove</a>
                    </td>
                </tr>

                <?php endforeach; ?>
            </table>
        </div>

        <h3>Total Amount:  ₱<?= number_format($totalAmount, 2) ?></h3>
        <button onclick="window.location.href='checkout.php'">Proceed to Checkout</button>
    <?php else: ?>
        <p>Your cart is currently empty.</p>
    <?php endif; ?>

    <button onclick="window.location.href='home.php'">Back to Home</button>
</div>

</body>
</html>
