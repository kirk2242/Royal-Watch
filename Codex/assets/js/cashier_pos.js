let cart = [];

function addToCart(id, name, price, image, quantity = 1) {
    let existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ id, name, price, quantity, image });
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

    // Send AJAX request to get product details by barcode
    fetch(`get_product.php?barcode=${barcode}`)
        .then(response => response.json())
        .then(product => {
            if (product.error) {
                alert(product.error);
            } else {
                addToCart(product.id, product.name, product.price, product.image, quantity);
            }
        })
        .catch(error => console.error("Error fetching product:", error));
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

    fetch("process_checkout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cart })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Purchase successful!");
            cart = [];
            updateCart();
        } else {
            alert("Checkout failed.");
        }
    })
    .catch(error => console.error("Error:", error));
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
        .then(response => response.json())
        .then(product => {
            console.log("Product response:", product); // Debugging

            if (product.error) {
                alert(product.error);
            } else {
                addToCart(product.id, product.name, product.price, product.image, quantity);
            }
        })
        .catch(error => console.error("Error fetching product:", error));
}
