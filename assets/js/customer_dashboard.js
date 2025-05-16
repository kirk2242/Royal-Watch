/**
 * Time Emporium - Customer Dashboard JavaScript
 * Gold and White Theme Enhancements
 */

document.addEventListener('DOMContentLoaded', function() {
    // Format price and stock display
    formatProductCards();
    
    // Add to cart functionality
    setupAddToCartButtons();
    
    // Add smooth scrolling
    enableSmoothScroll();
    
    // Add responsive menu toggle for mobile
    setupMobileMenu();
    
    // Add gold shimmer effects
    enhanceGoldEffects();
});

/**
 * Format product cards to enhance price and stock display
 */
function formatProductCards() {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const priceParagraph = card.querySelector('p');
        if (!priceParagraph) return;
        
        const text = priceParagraph.textContent;
        
        // Extract price and stock information
        const priceMatch = text.match(/Price:\s*₱([\d,\.]+)/);
        const stockMatch = text.match(/Stock:\s*(\d+)/);
        
        if (priceMatch && stockMatch) {
            const price = priceMatch[1];
            const stock = parseInt(stockMatch[1]);
            
            // Create new formatted elements
            priceParagraph.innerHTML = `
                <span class="price">₱${price}</span>
                <span class="stock ${stock < 5 ? 'low' : ''}">
                    ${stock < 5 ? '⚠️ Only ' + stock + ' left in stock!' : 'In Stock: ' + stock}
                </span>
            `;
        }
    });
}

/**
 * Setup add to cart button functionality
 */
function setupAddToCartButtons() {
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-id');
            const productCard = this.closest('.product-card');
            
            // Visual feedback
            const originalText = this.textContent;
            this.textContent = 'Adding...';
            this.disabled = true;
            
            // Add gold pulse effect
            productCard.classList.add('gold-pulse');
            
            // Add to cart via AJAX
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}&quantity=1`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success feedback
                    this.textContent = '✓ Added!';
                    
                    // Create and show notification
                    showNotification('Product added to cart!', 'success');
                    
                    // Reset button after delay
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                        productCard.classList.remove('gold-pulse');
                    }, 2000);
                } else {
                    // Error feedback
                    this.textContent = 'Error!';
                    showNotification(data.message || 'Failed to add product', 'error');
                    
                    // Reset button after delay
                    setTimeout(() => {
                        this.textContent = originalText;
                        this.disabled = false;
                        productCard.classList.remove('gold-pulse');
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.textContent = 'Error!';
                showNotification('Network error occurred', 'error');
                
                // Reset button after delay
                setTimeout(() => {
                    this.textContent = originalText;
                    this.disabled = false;
                    productCard.classList.remove('gold-pulse');
                }, 2000);
            });
        });
    });
}

/**
 * Show notification message with gold styling
 */
function showNotification(message, type = 'info') {
    // Create notification element if it doesn't exist
    let notification = document.querySelector('.notification');
    
    if (!notification) {
        notification = document.createElement('div');
        notification.className = 'notification';
        document.body.appendChild(notification);
        
        // Add styles
        notification.style.position = 'fixed';
        notification.style.bottom = '20px';
        notification.style.right = '20px';
        notification.style.padding = '12px 20px';
        notification.style.borderRadius = '4px';
        notification.style.color = 'white';
        notification.style.fontWeight = 'bold';
        notification.style.zIndex = '1000';
        notification.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        notification.style.transition = 'all 0.3s ease';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';
        notification.style.border = '1px solid #9e7c1e';
    }
    
    // Set type-specific styles
    if (type === 'success') {
        notification.style.background = 'linear-gradient(135deg, #d4af37 0%, #9e7c1e 100%)';
    } else if (type === 'error') {
        notification.style.backgroundColor = '#e74c3c';
    } else {
        notification.style.background = 'linear-gradient(135deg, #f1d78f 0%, #d4af37 100%)';
        notification.style.color = '#333';
    }
    
    // Set message and show
    notification.textContent = message;
    notification.style.opacity = '1';
    notification.style.transform = 'translateY(0)';
    
    // Hide after delay
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(20px)';
    }, 3000);
}

/**
 * Enable smooth scrolling for anchor links
 */
function enableSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

/**
 * Setup mobile menu toggle
 */
function setupMobileMenu() {
    // Check if we're on mobile
    const isMobile = window.matchMedia('(max-width: 768px)').matches;
    
    if (isMobile) {
        const header = document.querySelector('header');
        
        // Create menu toggle button with gold styling
        const menuToggle = document.createElement('button');
        menuToggle.className = 'menu-toggle';
        menuToggle.innerHTML = '☰';
        menuToggle.style.position = 'absolute';
        menuToggle.style.top = '20px';
        menuToggle.style.right = '20px';
        menuToggle.style.background = 'linear-gradient(135deg, #d4af37 0%, #9e7c1e 100%)';
        menuToggle.style.border = 'none';
        menuToggle.style.color = 'white';
        menuToggle.style.borderRadius = '4px';
        menuToggle.style.padding = '8px 12px';
        menuToggle.style.fontSize = '20px';
        menuToggle.style.cursor = 'pointer';
        menuToggle.style.zIndex = '1001';
        
        // Add toggle button to header
        header.style.position = 'relative';
        header.appendChild(menuToggle);
        
        // Get nav links
        const navLinks = document.querySelector('.nav-links');
        navLinks.style.display = 'none';
        navLinks.style.flexDirection = 'column';
        navLinks.style.width = '100%';
        
        // Toggle menu on click
        menuToggle.addEventListener('click', function() {
            if (navLinks.style.display === 'none') {
                navLinks.style.display = 'flex';
                menuToggle.innerHTML = '✕';
            } else {
                navLinks.style.display = 'none';
                menuToggle.innerHTML = '☰';
            }
        });
    }
}

/**
 * Add gold shimmer and hover effects
 */
function enhanceGoldEffects() {
    // Add CSS for gold pulse effect
    const style = document.createElement('style');
    style.textContent = `
        @keyframes goldPulse {
            0% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(212, 175, 55, 0); }
            100% { box-shadow: 0 0 0 0 rgba(212, 175, 55, 0); }
        }
        
        .gold-pulse {
            animation: goldPulse 1.5s ease-out;
        }
        
        .product-card:hover .price {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
    `;
    document.head.appendChild(style);
    
    // Add hover effect to product images
    const productImages = document.querySelectorAll('.product-image');
    productImages.forEach(image => {
        image.addEventListener('mouseenter', function() {
            this.style.boxShadow = 'inset 0 0 30px rgba(212, 175, 55, 0.2)';
        });
        
        image.addEventListener('mouseleave', function() {
            this.style.boxShadow = 'none';
        });
    });
}

/**
 * Add a subtle gold border to product cards on load
 */
window.addEventListener('load', function() {
    // Add a subtle gold border animation to product cards
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.borderColor = '#d4af37';
        }, 100 * index);
    });
});