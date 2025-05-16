<?php
require '../config/database.php';

if (isset($_GET['barcode'])) {
    $barcode = trim($_GET['barcode']);

    // Debugging: Log the barcode being searched
    error_log("Fetching product for barcode: $barcode");

    // Fetch product details by barcode
    $stmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE barcode = ?");
    $stmt->execute([$barcode]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Debugging: Log the product details
        error_log("Product found: " . json_encode($product));
        echo json_encode($product);
    } else {
        // Debugging: Log when no product is found
        error_log("No product found for barcode: $barcode");
        echo json_encode(["error" => "Product not found"]);
    }
} else {
    // Debugging: Log when no barcode is provided
    error_log("No barcode provided in request");
    echo json_encode(["error" => "No barcode provided"]);
}
?>
