<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['id'];
    $quantity = intval($_POST['quantity']);

    if ($quantity > 0) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
}

header("Location: cart.php");
exit();
