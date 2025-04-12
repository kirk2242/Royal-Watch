<?php
session_start();

if (isset($_GET['id'])) {
    $productId = $_GET['id'];
    unset($_SESSION['cart'][$productId]);
}

header("Location: checkout.php");
exit();
?>
