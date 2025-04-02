document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function () {
        let productId = this.getAttribute('data-id');

        fetch('add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + productId
        })
        .then(response => response.json())
        .then(data => {
            const productCard = document.querySelector(`.product-card[data-id="${productId}"]`);
            if (productCard) {
                productCard.classList.add('clicked');
                setTimeout(() => productCard.classList.remove('clicked'), 300);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
