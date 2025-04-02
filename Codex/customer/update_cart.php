<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'], $_POST['quantity'])) {
    $productId = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);

    if (isset($_SESSION['cart'][$productId]) && $quantity > 0) {
        $_SESSION['cart'][$productId]['quantity'] = $quantity;
    }

    header("Location: checkout.php");
    exit();
}
?>
