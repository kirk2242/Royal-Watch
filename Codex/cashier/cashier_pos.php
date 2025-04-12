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

<script>
// Cart functionality
let cart = [];
let total = 0;
let selectedPaymentMethod = null;

// Filter products by category
function filterProducts(category) {
    const productCards = document.querySelectorAll('.product-card');
    const categoryItems = document.querySelectorAll('.category-section li');
    
    // Update active category
    categoryItems.forEach(item => {
        if (item.textContent.includes(category) || (category === 'all' && item.textContent.includes('All'))) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
    
    productCards.forEach(card => {
        if (category === 'all' || card.dataset.category === category) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// Barcode scanner functionality
let barcodeInput = document.getElementById('barcode');
let barcodeTimeout;

// Detect barcode scanner input (rapid entry with Enter key)
barcodeInput.addEventListener('keydown', function(e) {
    // Clear any previous timeout
    clearTimeout(barcodeTimeout);
    
    // If Enter key is pressed (typical barcode scanner behavior)
    if (e.key === 'Enter') {
        e.preventDefault();
        addToCartByBarcode();
    } else {
        // Set timeout to detect manual input vs scanner input
        barcodeTimeout = setTimeout(() => {
            // If user is typing manually, don't auto-submit
        }, 100);
    }
});

// Enhanced add to cart by barcode function
function addToCartByBarcode() {
    const barcode = barcodeInput.value.trim();
    const quantityInput = document.getElementById('quantity');
    let quantity = parseInt(quantityInput.value) || 1;
    const feedbackElement = document.getElementById('barcode-feedback');
    
    if (!barcode) {
        showFeedback('Please enter a barcode', 'error');
        barcodeInput.focus();
        return;
    }
    
    // First check local products (from PHP array)
    <?php echo "const products = " . json_encode($products) . ";"; ?>
    const product = products.find(p => p.barcode === barcode);
    
    if (product) {
        // Product found in local data
        if (product.stock <= 0) {
            showFeedback('Product is out of stock', 'error');
            return;
        }
        
        addToCart(product.id, product.name, product.price, 
                 product.image ? '../uploads/' + product.image : '../assets/images/no-image.jpg', 
                 product.stock, quantity);
        
        // Clear and focus for next scan
        resetScannerInput();
        showFeedback(`${product.name} added to cart`, 'success');
    } else {
        // Product not found in local data - check via AJAX
        checkBarcodeViaAJAX(barcode, quantity);
    }
}

// Check barcode via AJAX if not found locally
function checkBarcodeViaAJAX(barcode, quantity) {
    const feedbackElement = document.getElementById('barcode-feedback');
    showFeedback('Checking product...', 'info');
    
    $.ajax({
        url: '../cashier/check_barcode.php',
        method: 'POST',
        data: { barcode: barcode },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.product) {
                const product = response.product;
                
                if (product.stock <= 0) {
                    showFeedback('Product is out of stock', 'error');
                    return;
                }
                
                addToCart(product.id, product.name, product.price, 
                         product.image ? '../uploads/' + product.image : '../assets/images/no-image.jpg', 
                         product.stock, quantity);
                
                resetScannerInput();
                showFeedback(`${product.name} added to cart`, 'success');
            } else {
                showFeedback(response.message || 'Product not found', 'error');
                barcodeInput.select();
            }
        },
        error: function() {
            showFeedback('Error checking product', 'error');
        }
    });
}

// Add product to cart
function addToCart(id, name, price, image, stock, qty = 1) {
    // Convert price to a number
    price = parseFloat(price);

    // Check if quantity is valid
    if (qty <= 0) {
        showFeedback('Quantity must be greater than 0', 'error');
        return;
    }
    
    // Check if product is already in cart
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        // Check if adding more would exceed stock
        if (existingItem.quantity + qty > stock) {
            showFeedback('Not enough stock available', 'error');
            return;
        }
        
        existingItem.quantity += qty;
    } else {
        // Check if quantity exceeds stock
        if (qty > stock) {
            showFeedback('Not enough stock available', 'error');
            return;
        }
        
        cart.push({
            id: id,
            name: name,
            price: price,
            image: image,
            quantity: qty
        });
    }
    
    updateCart();
}

// Update cart display
function updateCart() {
    const cartItems = document.getElementById('cart-items');
    cartItems.innerHTML = '';
    
    total = 0;
    
    cart.forEach((item, index) => {
        const li = document.createElement('li');
        li.className = 'cart-item';
        
        const itemTotal = item.price * item.quantity;
        total += itemTotal;
        
        li.innerHTML = `
            <img src="${item.image}" alt="${item.name}" class="cart-item-image">
            <div class="cart-item-details">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">₱${item.price.toFixed(2)} x ${item.quantity}</div>
                <div class="cart-item-quantity">
                    <button class="quantity-btn" onclick="updateItemQuantity(${index}, -1)">-</button>
                    <span class="quantity-value">${item.quantity}</span>
                    <button class="quantity-btn" onclick="updateItemQuantity(${index}, 1)">+</button>
                    <button class="cart-item-remove" onclick="removeItem(${index})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        `;
        
        cartItems.appendChild(li);
    });
    
    document.getElementById('total-price').textContent = total.toFixed(2);
}

// Update item quantity
function updateItemQuantity(index, change) {
    const item = cart[index];
    const newQuantity = item.quantity + change;
    
    // Find product to check stock
    <?php echo "const products = " . json_encode($products) . ";"; ?>
    const product = products.find(p => p.id === item.id);
    
    if (newQuantity <= 0) {
        removeItem(index);
        return;
    }
    
    if (newQuantity > product.stock) {
        showFeedback('Not enough stock available', 'error');
        return;
    }
    
    item.quantity = newQuantity;
    updateCart();
}

// Remove item from cart
function removeItem(index) {
    cart.splice(index, 1);
    updateCart();
}

// Clear cart
function clearCart() {
    if (cart.length === 0) return;
    
    if (confirm('Are you sure you want to clear the cart?')) {
        cart = [];
        updateCart();
    }
}

// Checkout process
function checkout() {
    if (cart.length === 0) {
        showFeedback('Cart is empty', 'error');
        return;
    }
    
    document.getElementById('payment-modal').style.display = 'flex';
}

// Close payment modal
function closePaymentModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

// Process payment with confirmation
function processPayment(method, paymentAmount) {
    fetch('../cashier/process_checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            cart: cart,
            payment_method: method,
            payment_amount: paymentAmount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showReceipt(data.receipt);
            cart = [];
            updateCart();
            closePaymentModal();
        } else {
            alert(data.message || 'Payment failed.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during payment.');
    });
}

function showReceipt(receipt) {
    const receiptContent = document.getElementById('receipt-content');
    const date = new Date(receipt.date).toLocaleString();

    let receiptHTML = `
        <div class="receipt-header">
            <h4>Store Name</h4>
            <p>123 Main Street, City</p>
            <p>${date}</p>
        </div>
        <div class="receipt-items">
    `;

    receipt.items.forEach(item => {
        const itemTotal = item.price * item.quantity;
        receiptHTML += `
            <div class="receipt-item">
                <span>${item.name} x${item.quantity}</span>
                <span>₱${itemTotal.toFixed(2)}</span>
            </div>
        `;
    });

    receiptHTML += `
        </div>
        <div class="receipt-total">
            <span>Total:</span>
            <span>₱${receipt.total.toFixed(2)}</span>
        </div>
        <div class="receipt-footer">
            <p>Payment Method: ${receipt.payment_method.toUpperCase()}</p>
            <p>Payment Amount: ₱${receipt.payment_amount.toFixed(2)}</p>
            <p>Change: ₱${receipt.change.toFixed(2)}</p>
            <p>Thank you for your purchase!</p>
        </div>
    `;

    receiptContent.innerHTML = receiptHTML;
    document.getElementById('receipt-modal').style.display = 'flex';
}

function printReceipt() {
    const receiptContent = document.getElementById('receipt-content').innerHTML;
    const printWindow = window.open('', '_blank');

    printWindow.document.write(`
        <html>
            <head>
                <title>Receipt</title>
                <style>
                    body {
                        font-family: 'Courier New', Courier, monospace;
                        padding: 20px;
                        max-width: 300px;
                        margin: 0 auto;
                    }
                    .receipt-header {
                        text-align: center;
                        margin-bottom: 15px;
                    }
                    .receipt-items {
                        margin: 15px 0;
                    }
                    .receipt-item {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 5px;
                    }
                    .receipt-total {
                        border-top: 1px dashed #000;
                        padding-top: 10px;
                        margin-top: 10px;
                        font-weight: bold;
                        display: flex;
                        justify-content: space-between;
                    }
                    .receipt-footer {
                        text-align: center;
                        margin-top: 15px;
                        font-size: 14px;
                    }
                </style>
            </head>
            <body>
                ${receiptContent}
            </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();
}

function closeReceipt() {
    document.getElementById('receipt-modal').style.display = 'none';
}

// Select payment method
function selectPaymentMethod(method) {
    selectedPaymentMethod = method;
    document.getElementById('payment-amount-section').style.display = 'block';
}

// Confirm payment
function confirmPayment() {
    const paymentAmount = parseFloat(document.getElementById('payment-amount').value);
    const totalAmount = parseFloat(document.getElementById('total-price').textContent);

    if (isNaN(paymentAmount) || paymentAmount < totalAmount) {
        alert('Payment amount must be greater than or equal to the total amount.');
        return;
    }

    processPayment(selectedPaymentMethod, paymentAmount);
}

// Print receipt
function printReceipt() {
    const receiptContent = document.getElementById('receipt-content').innerHTML;
    const printWindow = window.open('', '_blank');
    
    printWindow.document.write(`
        <html>
            <head>
                <title>Receipt</title>
                <style>
                    body {
                        font-family: 'Courier New', Courier, monospace;
                        padding: 20px;
                        max-width: 300px;
                        margin: 0 auto;
                    }
                    .receipt-header {
                        text-align: center;
                        margin-bottom: 15px;
                    }
                    .receipt-items {
                        margin: 15px 0;
                    }
                    .receipt-item {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 5px;
                    }
                    .receipt-total {
                        border-top: 1px dashed #000;
                        padding-top: 10px;
                        margin-top: 10px;
                        font-weight: bold;
                        display: flex;
                        justify-content: space-between;
                    }
                    .receipt-footer {
                        text-align: center;
                        margin-top: 15px;
                        font-size: 14px;
                    }
                </style>
            </head>
            <body>
                ${receiptContent}
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
    printWindow.close();

    // Show success message and redirect back to POS
    showFeedback('Checkout successful!', 'success');
    closeReceipt();
}

// Close receipt modal
function closeReceipt() {
    document.getElementById('receipt-modal').style.display = 'none';
}

// Helper function to show feedback messages
function showFeedback(message, type) {
    const feedbackElement = document.getElementById('barcode-feedback');
    feedbackElement.textContent = message;
    feedbackElement.className = `feedback-message ${type}`;
    
    // Auto-hide success messages after 2 seconds
    if (type === 'success') {
        setTimeout(() => {
            feedbackElement.textContent = '';
            feedbackElement.className = 'feedback-message';
        }, 2000);
    }
}

// Reset scanner input after successful scan
function resetScannerInput() {
    barcodeInput.value = '';
    document.getElementById('quantity').value = '1';
    barcodeInput.focus();
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set focus to barcode input
    barcodeInput.focus();
    
    // Initialize with all products
    filterProducts('all');
});
</script>

<style>
/* Add these styles to your CSS file */
.scanner-container {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
}

.scanner-container input {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.scanner-container #barcode {
    flex: 1;
}

.scanner-container #quantity {
    width: 80px;
}

.feedback-message {
    padding: 8px;
    border-radius: 4px;
    margin-top: 5px;
    display: none;
}

.feedback-message.error {
    display: block;
    background-color: #ffebee;
    color: #c62828;
    border: 1px solid #ef9a9a;
}

.feedback-message.success {
    display: block;
    background-color: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #a5d6a7;
}

.feedback-message.info {
    display: block;
    background-color: #e3f2fd;
    color: #1565c0;
    border: 1px solid #90caf9;
}

.barcode-display {
    font-size: 12px;
    color: #666;
    margin: 5px 0;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: white;
    padding: 20px;
    border-radius: 8px;
    width: 80%;
    max-width: 500px;
}

.payment-options {
    display: flex;
    justify-content: space-around;
    margin: 20px 0;
}

.payment-option {
    text-align: center;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.payment-option:hover {
    background-color: #f5f5f5;
    transform: scale(1.05);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.cart-item {
    display: flex;
    padding: 10px;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.cart-item-image {
    width: 50px;
    height: 50px;
    object-fit: cover;
    margin-right: 10px;
}

.cart-item-details {
    flex: 1;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 5px;
}

.quantity-btn {
    width: 25px;
    height: 25px;
    border: 1px solid #ddd;
    background-color: #f5f5f5;
    cursor: pointer;
}

.out-of-stock {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

</body>
</html>