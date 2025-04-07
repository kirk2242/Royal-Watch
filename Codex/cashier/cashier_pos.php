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
        <input type="text" id="barcode" placeholder="Enter Barcode" autofocus>
        <input type="number" id="quantity" placeholder="Quantity" min="1" value="1">
        <button onclick="addToCartByBarcode()"><i class="fas fa-plus"></i> Add</button>
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
                <div class="payment-option" onclick="processPayment('cash')">
                    <i class="fas fa-money-bill-wave"></i>
                    <h4>Cash</h4>
                </div>
                <div class="payment-option" onclick="processPayment('gcash')">
                    <i class="fas fa-mobile-alt"></i>
                    <h4>GCash</h4>
                </div>
                <div class="payment-option" onclick="processPayment('card')">
                    <i class="fas fa-credit-card"></i>
                    <h4>Card</h4>
                </div>
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

// Add product to cart by barcode
function addToCartByBarcode() {
    const barcode = document.getElementById('barcode').value;
    const quantity = parseInt(document.getElementById('quantity').value);
    
    if (!barcode) {
        alert('Please enter a barcode');
        return;
    }
    
    // Find product by barcode
    <?php echo "const products = " . json_encode($products) . ";"; ?>
    
    const product = products.find(p => p.barcode === barcode);
    
    if (!product) {
        alert('Product not found');
        return;
    }
    
    if (product.stock <= 0) {
        alert('Product is out of stock');
        return;
    }
    
    addToCart(product.id, product.name, product.price, product.image ? '../uploads/' + product.image : '../assets/images/no-image.jpg', product.stock, quantity);
    
    // Clear barcode input and focus
    document.getElementById('barcode').value = '';
    document.getElementById('barcode').focus();
}

// Add product to cart
function addToCart(id, name, price, image, stock, qty = 1) {
    // Convert price to a number
    price = parseFloat(price);

    // Check if quantity is valid
    if (qty <= 0) {
        alert('Quantity must be greater than 0');
        return;
    }
    
    // Check if product is already in cart
    const existingItem = cart.find(item => item.id === id);
    
    if (existingItem) {
        // Check if adding more would exceed stock
        if (existingItem.quantity + qty > stock) {
            alert('Not enough stock available');
            return;
        }
        
        existingItem.quantity += qty;
    } else {
        // Check if quantity exceeds stock
        if (qty > stock) {
            alert('Not enough stock available');
            return;
        }
        
        cart.push({
            id: id,
            name: name,
            price: price, // Ensure price is a number
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
        alert('Not enough stock available');
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
        alert('Cart is empty');
        return;
    }
    
    document.getElementById('payment-modal').style.display = 'flex';
}

// Close payment modal
function closePaymentModal() {
    document.getElementById('payment-modal').style.display = 'none';
}

// Process payment with confirmation
function processPayment(method) {
    // Confirm payment method
    if (!confirm(`Are you sure you want to proceed with ${method.toUpperCase()} payment?`)) {
        return;
    }

    // Send cart data to the server
    fetch('../cashier/process_checkout.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            cart: cart,
            payment_method: method
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show receipt
            const receiptContent = document.getElementById('receipt-content');
            const date = new Date().toLocaleString();
            
            let receiptHTML = `
                <div class="receipt-header">
                    <h4>Store Name</h4>
                    <p>123 Main Street, City</p>
                    <p>${date}</p>
                </div>
                <div class="receipt-items">
            `;
            
            cart.forEach(item => {
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
                    <span>₱${total.toFixed(2)}</span>
                </div>
                <div class="receipt-footer">
                    <p>Payment Method: ${method.toUpperCase()}</p>
                    <p>Thank you for your purchase!</p>
                </div>
            `;
            
            receiptContent.innerHTML = receiptHTML;
            
            // Close payment modal and show receipt
            document.getElementById('payment-modal').style.display = 'none';
            document.getElementById('receipt-modal').style.display = 'flex';
            
            // Clear cart after successful payment
            cart = [];
            updateCart();
        } else {
            alert(data.message || 'Checkout failed.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during checkout.');
    });
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
    alert('Checkout successful!');
    closeReceipt();
}

// Close receipt modal
function closeReceipt() {
    document.getElementById('receipt-modal').style.display = 'none';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    // Set focus to barcode input
    document.getElementById('barcode').focus();
    
    // Initialize with all products
    filterProducts('all');
});
</script>

</body>
</html>