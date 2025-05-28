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
    clearTimeout(barcodeTimeout);
    if (e.key === 'Enter') {
        e.preventDefault();
        addToCartByBarcode();
    } else {
        barcodeTimeout = setTimeout(() => {}, 100);
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
    const product = products.find(p => p.barcode === barcode);
    
    if (product) {
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
        checkBarcodeViaAJAX(barcode, quantity);
    }
}

// Check barcode via AJAX if not found locally
function checkBarcodeViaAJAX(barcode, quantity) {
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
    price = parseFloat(price);
    if (qty <= 0) {
        showFeedback('Quantity must be greater than 0', 'error');
        return;
    }
    const existingItem = cart.find(item => item.id === id);
    // Find the product in the products array
    const product = products.find(p => p.id === id);

    if (!product) {
        showFeedback('Product not found', 'error');
        return;
    }

    if (existingItem) {
        if (existingItem.quantity + qty > product.stock) {
            // Remove warning for not enough stock, just do nothing
            return;
        }
        existingItem.quantity += qty;
    } else {
        if (qty > product.stock) {
            // Remove warning for not enough stock, just do nothing
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

    // Deduct stock from the product
    product.stock -= qty;

    updateProductCardStock(id, product.stock);
    updateCart();
}

// Update the stock display and button state on the product card
function updateProductCardStock(id, newStock) {
    const card = document.querySelector(`.product-card button[onclick^="addToCart(${id},"]`)?.closest('.product-card');
    if (card) {
        // Update stock text
        const stockP = card.querySelector('p');
        if (stockP) {
            const priceText = stockP.textContent.split('|')[0].trim();
            stockP.textContent = `${priceText} | Stock: ${newStock}`;
        }
        // Disable button if out of stock
        const btn = card.querySelector('button');
        if (btn) {
            if (newStock <= 0) {
                btn.disabled = true;
                btn.classList.add('out-of-stock');
                btn.innerHTML = '<i class="fas fa-ban"></i> Out of Stock';
            }
        }
    }
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
    const product = products.find(p => p.id === item.id);
    if (!product) return;

    const newQuantity = item.quantity + change;

    if (newQuantity <= 0) {
        // Restore stock before removing
        product.stock += item.quantity;
        updateProductCardStock(product.id, product.stock);
        removeItem(index);
        return;
    }

    if (change > 0 && newQuantity > product.stock + item.quantity) {
        // Do not exceed available stock
        return;
    }

    // Adjust stock
    product.stock -= change;
    updateProductCardStock(product.id, product.stock);

    item.quantity = newQuantity;
    updateCart();
}


// Remove item from cart
function removeItem(index) {
    const item = cart[index];
    const product = products.find(p => p.id === item.id);
    if (product) {
        product.stock += item.quantity;
        updateProductCardStock(product.id, product.stock);
    }
    cart.splice(index, 1);
    updateCart();
}


// Clear cart
function clearCart() {
    if (cart.length === 0) return;
    if (confirm('Are you sure you want to clear the cart?')) {
        // Restore stock for all items
        cart.forEach(item => {
            const product = products.find(p => p.id === item.id);
            if (product) {
                product.stock += item.quantity;
                updateProductCardStock(product.id, product.stock);
            }
        });
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
    showFeedback('Checkout successful!', 'success');
    closeReceipt();
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

// Helper function to show feedback messages
function showFeedback(message, type) {
    const feedbackElement = document.getElementById('barcode-feedback');
    feedbackElement.textContent = message;
    feedbackElement.className = `feedback-message ${type}`;
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
    barcodeInput.focus();
    filterProducts('all');
});