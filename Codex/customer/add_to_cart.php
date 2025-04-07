<?php
session_start();
require '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $productId = intval($_POST['product_id']);

    // Fetch product details from database
    $stmt = $pdo->prepare("SELECT id, name, image, price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Ensure cart session exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // If product already exists in cart, increase quantity
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId]['quantity'] += 1;
        } else {
            // Add new product to cart
            $_SESSION['cart'][$productId] = [
                'name' => $product['name'],
                'image' => $product['image'] ?? 'default.jpg', // Default if no image
                'quantity' => 1,
                'price' => $product['price']
            ];
        }

        // Calculate total price
        $totalPrice = 0;
        foreach ($_SESSION['cart'] as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        echo json_encode(['success' => true, 'totalPrice' => $totalPrice]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
} else {
    echo json_encode([]);
}
?>
