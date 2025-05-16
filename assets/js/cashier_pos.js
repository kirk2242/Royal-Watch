let cart = [];

function addToCart(id, name, price, image, stock, quantity = 1) {
    let existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        if (existingItem.quantity + quantity > stock) {
            alert("Not enough stock available.");
            return;
        }
        existingItem.quantity += quantity;
    } else {
        if (quantity > stock) {
            alert("Not enough stock available.");
            return;
        }
        cart.push({ id, name, price: parseFloat(price), quantity, image, stock });
    }

    const productCard = document.querySelector(`.product-card[data-id="${id}"]`);
    if (productCard) {
        productCard.classList.add('clicked');
        setTimeout(() => productCard.classList.remove('clicked'), 300);
    }

    console.log("Cart Updated:", cart); // Debugging
    updateCart();
}

function addToCartByBarcode() {
    let barcode = document.getElementById("barcode").value;
    let quantity = parseInt(document.getElementById("quantity").value);

    if (!barcode || quantity < 1) {
        alert("Enter a valid barcode and quantity.");
        return;
    }

    console.log("Scanning barcode:", barcode); // Debugging

    // Send AJAX request to get product details by barcode
    fetch(`get_product.php?barcode=${barcode}`)
        .then(response => {
            console.log("Response status:", response.status); // Debugging
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(product => {
            console.log("Product response:", product); // Debugging

            if (product.error) {
                alert(product.error);
            } else {
                addToCart(product.id, product.name, product.price, product.image, product.stock, quantity);
            }
        })
        .catch(error => {
            console.error("Error fetching product:", error);
            alert("Failed to fetch product. Please check the console for details.");
        });
}

function updateCart() {
    let cartItems = document.getElementById("cart-items");
    let totalPrice = document.getElementById("total-price");
    cartItems.innerHTML = "";

    let total = 0;

    cart.forEach(item => {
        let li = document.createElement("li");
        li.innerHTML = `
            <img src="../uploads/${item.image}" width="50"> 
            ${item.name} - ₱${item.price.toFixed(2)} x ${item.quantity}
            <button onclick="removeFromCart(${item.id})">Remove</button>
        `;
        cartItems.appendChild(li);
        total += item.price * item.quantity;
    });

    totalPrice.innerText = total.toFixed(2);
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCart();
}

function clearCart() {
    cart = [];
    updateCart();
}

function checkout() {
    if (cart.length === 0) {
        alert("Your cart is empty.");
        return;
    }

    // Show payment modal
    const paymentModal = document.getElementById("payment-modal");
    paymentModal.style.display = "block";
}

function processPayment(paymentMethod) {
    const paymentModal = document.getElementById("payment-modal");

    fetch("../cashier/process_checkout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart, payment_method: paymentMethod })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert("Purchase successful!");
            cart = [];
            updateCart();
            showReceipt(data.receipt);
        } else {
            alert("Checkout failed: " + (data.message || "Unknown error"));
        }
    })
    .catch(error => {
        console.error("Error during checkout:", error);
        alert("Failed to process checkout. Please check the console for details.");
    })
    .finally(() => {
        paymentModal.style.display = "none";
    });
}

function showReceipt(receipt) {
    const receiptModal = document.getElementById("receipt-modal");
    const receiptContent = document.getElementById("receipt-content");

    receiptContent.innerHTML = `
        <p><strong>Receipt ID:</strong> ${receipt.id}</p>
        <p><strong>Date:</strong> ${receipt.date}</p>
        <p><strong>Payment Method:</strong> ${receipt.payment_method}</p>
        <p><strong>Total:</strong> ₱${receipt.total.toFixed(2)}</p>
        <h4>Items:</h4>
        <ul>
            ${receipt.items.map(item => `
                <li>${item.name} - ₱${item.price.toFixed(2)} x ${item.quantity}</li>
            `).join('')}
        </ul>
    `;

    receiptModal.style.display = "block";
}

function closeReceipt() {
    const receiptModal = document.getElementById("receipt-modal");
    receiptModal.style.display = "none";
}

function filterProducts(category) {
    const productCards = document.querySelectorAll('.product-card');

    productCards.forEach(card => {
        const productCategory = card.getAttribute('data-category');

        if (category === 'all' || productCategory === category) {
            card.style.display = 'block'; // Show the product
        } else {
            card.style.display = 'none'; // Hide the product
        }
    });

    console.log(`Filtered products by category: ${category}`); // Debugging
}
