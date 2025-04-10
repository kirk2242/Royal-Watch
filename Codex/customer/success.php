<?php
session_start();
require_once '../config/database.php';

// Get the latest sale for this user
$sale_id = 0;
$sale_details = [];
$sale_items = [];
$payment_method = '';
$sale_date = '';
$total_amount = 0;

if (isset($_SESSION['user_id'])) {
    // Get the latest sale
    $stmt = $pdo->prepare("SELECT id, payment_method, total, created_at FROM sales 
                          WHERE user_id = ? 
                          ORDER BY created_at DESC 
                          LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $sale = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($sale) {
        $sale_id = $sale['id'];
        $payment_method = $sale['payment_method'];
        $total_amount = $sale['total'];
        $sale_date = date('F j, Y g:i A', strtotime($sale['created_at']));
        
        // Get sale items
        $stmt = $pdo->prepare("SELECT si.quantity, si.price, p.name, p.image 
                              FROM sales_items si 
                              JOIN products p ON si.product_id = p.id 
                              WHERE si.sale_id = ?");
        $stmt->execute([$sale_id]);
        $sale_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get user details from the users table
        $stmt = $pdo->prepare("SELECT u.username, c.firstname, c.lastname, c.address, c.contact_number 
                              FROM users u
                              LEFT JOIN customers c ON u.id = c.user_id
                              WHERE u.id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $sale_details = $user;
        }
    }
}

// Format payment method for display
$payment_method_formatted = ucfirst(str_replace('_', ' ', $payment_method));

// Calculate subtotal and tax
$subtotal = $total_amount / 1.12; // Assuming 12% tax
$tax = $total_amount - $subtotal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - Time Emporium</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/success.css">
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 0a12 12 0 1012 12A12.014 12.014 0 0012 0zm6.927 8.2l-6.845 9.289a1.011 1.011 0 01-1.43.188l-4.888-3.908a1 1 0 111.25-1.562l4.076 3.261 6.227-8.451a1 1 0 111.61 1.183z"></path>
            </svg>
        </div>
        
        <h2>Order Placed Successfully!</h2>
        <p>Thank you for your purchase. Your order has been confirmed.</p>
        
        <?php if ($sale_id > 0): ?>
        <div class="receipt">
            <div class="receipt-header">
                <h3>Time Emporium</h3>
                <p>Luxury Watches & Timepieces</p>
                <p>123 Elegance Avenue, Luxury Lane</p>
                <p>Order #<?= str_pad($sale_id, 6, '0', STR_PAD_LEFT) ?></p>
            </div>
            
            <div class="receipt-details">
                <div>
                    <p><strong>Date:</strong> <?= $sale_date ?></p>
                    <p><strong>Payment Method:</strong> <?= $payment_method_formatted ?></p>
                </div>
                <div>
                    <?php if (!empty($sale_details)): ?>
                    <p><strong>Customer:</strong> 
                        <?php if (!empty($sale_details['firstname']) && !empty($sale_details['lastname'])): ?>
                            <?= htmlspecialchars($sale_details['firstname'] . ' ' . $sale_details['lastname']) ?>
                        <?php else: ?>
                            <?= htmlspecialchars($sale_details['username']) ?>
                        <?php endif; ?>
                    </p>
                    <p><strong>Contact:</strong> <?= htmlspecialchars($sale_details['contact_number'] ?? 'N/A') ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($sale_details['address'] ?? 'N/A') ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="receipt-items">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sale_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td class="item-price">₱<?= number_format($item['price'] / $item['quantity'], 2) ?></td>
                        <td class="item-quantity"><?= $item['quantity'] ?></td>
                        <td class="item-total">₱<?= number_format($item['price'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="receipt-summary">
                <div>
                    <span>Subtotal:</span>
                    <span>₱<?= number_format($subtotal, 2) ?></span>
                </div>
                <div>
                    <span>Tax (12%):</span>
                    <span>₱<?= number_format($tax, 2) ?></span>
                </div>
                <div class="total">
                    <span>Total:</span>
                    <span>₱<?= number_format($total_amount, 2) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="thank-you">
            Thank you for shopping with Time Emporium!
        </div>
        
        <div class="button-container">
            <button onclick="window.location.href='home.php'">Continue Shopping</button>
            <button class="print-btn" onclick="window.print()">Print Receipt</button>
        </div>
    </div>
    
    <script>
        // Add animation to receipt items
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.receipt-items tbody tr');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(10px)';
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, 500 + (index * 100));
            });
        });
    </script>
</body>
</html></qodoArtifact>