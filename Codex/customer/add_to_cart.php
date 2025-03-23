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
        
        echo json_encode(['status' => 'success', 'message' => 'Product added to cart.']);
        exit();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        exit();
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
exit();
?>
