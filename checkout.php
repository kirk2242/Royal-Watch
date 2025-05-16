<?php
// ...existing code...

if ($checkoutSuccess) {
    $cartItems = $_SESSION['cart']; // Assuming cart items are stored in session
    $userId = $_SESSION['user_id']; // Assuming user ID is stored in session

    foreach ($cartItems as $item) {
        $productId = $item['product_id'];
        $quantity = $item['quantity'];

        // Update product stock
        $updateStockQuery = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stmt = $conn->prepare($updateStockQuery);
        $stmt->bind_param("ii", $quantity, $productId);
        $stmt->execute();

        // Record the sale
        $insertSaleQuery = "INSERT INTO sales (user_id, product_id, quantity, sale_date) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertSaleQuery);
        $stmt->bind_param("iii", $userId, $productId, $quantity);
        $stmt->execute();
    }

    // Clear the cart after successful checkout
    unset($_SESSION['cart']);
}

// ...existing code...
?>
