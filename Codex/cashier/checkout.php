<?php
session_start();
require '../config/database.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["message" => "Invalid data"]);
    exit;
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO sales (cashier_id, total) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], array_sum(array_column($data, 'price'))]);
    $sale_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($data as $item) {
        $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['price']]);
    }

    $pdo->commit();
    echo json_encode(["message" => "Checkout successful"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["message" => "Checkout failed"]);
}
?>
