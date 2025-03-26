<?php
require '../config/database.php';

if (isset($_GET['barcode'])) {
    $barcode = trim($_GET['barcode']);

    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode($product);
    } else {
        echo json_encode(["error" => "Product not found"]);
    }
}
?>
