<?php
require '../config/database.php';

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(null);
    }
}
?>
