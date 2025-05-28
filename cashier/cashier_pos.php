<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'cashier') {
    header("Location: ../login.php");
    exit();
}

// Get cashier username
$username = $_SESSION['username'] ?? 'Cashier';

// Fetch categories
$stmt = $pdo->query("SELECT DISTINCT category FROM products");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch products
$productStmt = $pdo->query("SELECT * FROM products");
$products = $productStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier POS</title>
    <link rel="stylesheet" href="../assets/css/cashier_pos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="pos-container">
    <!-- Header with Logout Button -->
    <div class="header">
        <h2>Point of Sale System</h2>
        <div class="header-actions">
            <div class="cashier-info">
                <i class="fas fa-user"></i>
                <span><?= htmlspecialchars($username) ?></span>
            </div>
            <a href="../auth/logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </div>
    </div>

    <div class="top-section">
        <h3><i class="fas fa-barcode"></i> Scan Product</h3>
        <div class="scanner-container">
            <input type="text" id="barcode" placeholder="Scan barcode or enter manually" autofocus>
            <input type="number" id="quantity" placeholder="Qty" min="1" value="1">
            <button onclick="addToCartByBarcode()"><i class="fas fa-plus"></i> Add</button>
        </div>
        <div id="barcode-feedback" class="feedback-message"></div>
    </div>

    <div class="main-section">
        <div class="category-section">
            <h3>Categories</h3>
            <ul>
                <li onclick="filterProducts('all')" class="active">All Products</li>
                <?php foreach ($categories as $category): ?>
                    <li onclick="filterProducts('<?= htmlspecialchars($category['category']) ?>')"><?= htmlspecialchars($category['category']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="product-section">
            <?php foreach ($products as $product): ?>
                <div class="product-card" data-category="<?= htmlspecialchars($product['category']) ?>">
                    <img src="<?= !empty($product['image']) ? '../uploads/' . $product['image'] : '../assets/images/no-image.jpg' ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                    <h4><?= htmlspecialchars($product['name']) ?></h4>
                    <p>₱<?= number_format($product['price'], 2) ?> | Stock: <?= $product['stock'] ?></p>
                    <p class="barcode-display">Barcode: <?= htmlspecialchars($product['barcode'] ?? 'N/A') ?></p>
                    <button 
                        onclick="addToCart(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name']) ?>', <?= $product['price'] ?>, '<?= !empty($product['image']) ? '../uploads/' . $product['image'] : '../assets/images/no-image.jpg' ?>', <?= $product['stock'] ?>)"
                        <?= $product['stock'] <= 0 ? 'disabled class="out-of-stock"' : '' ?>
                    >
                        <?= $product['stock'] > 0 ? '<i class="fas fa-cart-plus"></i> Add to Cart' : '<i class="fas fa-ban"></i> Out of Stock' ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-section">
            <h3><i class="fas fa-shopping-cart"></i> Shopping Cart</h3>
            <ul id="cart-items"></ul>
            <div class="cart-total">
                <span>Total:</span>
                <span>₱<span id="total-price">0.00</span></span>
            </div>
            <div class="cart-actions">
                <button onclick="checkout()" class="checkout-btn"><i class="fas fa-cash-register"></i> Checkout</button>
                <button onclick="clearCart()" class="clear-btn"><i class="fas fa-trash"></i> Clear</button>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="payment-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Select Payment Method</h3>
            <div class="payment-options">
                <div class="payment-option" onclick="selectPaymentMethod('cash')">
                    <i class="fas fa-money-bill-wave"></i>
                    <h4>Cash</h4>
                </div>
                <div class="payment-option" onclick="selectPaymentMethod('gcash')">
                    <i class="fas fa-mobile-alt"></i>
                    <h4>GCash</h4>
                </div>
                <div class="payment-option" onclick="selectPaymentMethod('card')">
                    <i class="fas fa-credit-card"></i>
                    <h4>Card</h4>
                </div>
            </div>
            <div id="payment-amount-section" style="display: none;">
    <div id="total-price" style="font-weight: bold; margin-bottom: 1rem;">Total to Pay: ₱<span id="total-price">0.00</span></div>
    <h4>Enter Payment Amount</h4>
    <input type="number" id="payment-amount" placeholder="Enter amount" min="0" step="0.01">
    <button onclick="confirmPayment()">Confirm Payment</button>
</div>
            <div class="modal-actions">
                <button class="cancel-btn" onclick="closePaymentModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div id="receipt-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Receipt</h3>
            <div id="receipt-content"></div>
            <div class="modal-actions">
                <button class="confirm-btn" onclick="printReceipt()"><i class="fas fa-print"></i> Print</button>
                <button class="cancel-btn" onclick="closeReceipt()">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Pass PHP products array to JS -->
<script>
    const products = <?= json_encode($products) ?>;
</script>
<script src="../assets/js/cashier_pos.js"></script>
</body>
</html>