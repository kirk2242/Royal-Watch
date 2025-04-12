<?php
require '../config/database.php';

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cart']) || !isset($data['payment_method']) || !isset($data['payment_amount'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$cart = $data['cart'];
$payment_method = $data['payment_method'];
$payment_amount = floatval($data['payment_amount']);
$cashier_id = $_SESSION['user_id'];
$total = 0;

// Calculate total and validate products
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

if ($payment_amount < $total) {
    echo json_encode(['success' => false, 'message' => 'Payment amount is less than the total amount.']);
    exit();
}

$change = $payment_amount - $total;

try {
    $pdo->beginTransaction();
    
    // Create sale record
    $stmt = $pdo->prepare("INSERT INTO sales (user_id, total, payment_method, payment_amount, change_amount, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$cashier_id, $total, $payment_method, $payment_amount, $change]);
    $sale_id = $pdo->lastInsertId();
    
    // Create sale items and update stock
    foreach ($cart as $item) {
        // Add sale item
        $stmt = $pdo->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $stmt->execute([$sale_id, $item['id'], $item['quantity'], $item['price']]);
        
        // Update product stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['id']]);
    }
    
    $pdo->commit();

    // Prepare receipt details
    $receipt = [
        'sale_id' => $sale_id,
        'date' => date('Y-m-d H:i:s'),
        'payment_method' => $payment_method,
        'payment_amount' => $payment_amount,
        'change' => $change,
        'items' => $cart,
        'total' => $total
    ];

    echo json_encode(['success' => true, 'message' => 'Sale completed successfully', 'receipt' => $receipt]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}