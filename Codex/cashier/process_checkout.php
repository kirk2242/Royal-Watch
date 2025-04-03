<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!isset($input['cart']) || empty($input['cart'])) {
        echo json_encode(["success" => false, "message" => "Cart is empty."]);
        exit();
    }

    $cashier_id = $_SESSION['user_id'];
    $customer_id = $input['customer_id'] ?? null;
    $payment_method = $input['payment_method'] ?? null;

    if (!$customer_id || !$payment_method) {
        echo json_encode(["success" => false, "message" => "Customer ID and payment method are required."]);
        exit();
    }

    try {
        $pdo->beginTransaction();

        $totalAmount = 0;

        // Insert into sales table
        $stmt = $pdo->prepare("INSERT INTO sales (user_id, customer_id, total, payment_method, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$cashier_id, $customer_id, $totalAmount, $payment_method]);
        $sale_id = $pdo->lastInsertId();

        // Insert into sales_items table and update product stock
        $stmt = $pdo->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $updateStockStmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");
        foreach ($input['cart'] as $item) {
            $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['price']]);
            $updateStockStmt->execute([$item['quantity'], $item['id'], $item['quantity']]);

            if ($updateStockStmt->rowCount() === 0) {
                throw new Exception("Insufficient stock for product ID: " . $item['id']);
            }

            $totalAmount += $item['quantity'] * $item['price'];
        }

        // Update total amount in sales table
        $updateStmt = $pdo->prepare("UPDATE sales SET total = ? WHERE id = ?");
        $updateStmt->execute([$totalAmount, $sale_id]);

        $pdo->commit();

        // Generate receipt
        $receipt = [
            "id" => $sale_id,
            "date" => date("Y-m-d H:i:s"),
            "customer_id" => $customer_id,
            "payment_method" => $payment_method,
            "total" => $totalAmount,
            "items" => $input['cart']
        ];

        echo json_encode(["success" => true, "message" => "Checkout successful.", "receipt" => $receipt]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["success" => false, "message" => "Checkout failed: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}
?>
