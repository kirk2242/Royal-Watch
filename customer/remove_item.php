<?php
session_start();

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
}

// Redirect back to cart page
header("Location: cart.php");
exit();
?>
