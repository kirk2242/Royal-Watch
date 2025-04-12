<?php
require_once 'config/database.php';

// Fetch featured products (limit to 4)
$stmt = $pdo->prepare("SELECT * FROM products ORDER BY id DESC LIMIT 6");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Emporium - Luxury Watches</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header id="header">
        <a href="#" class="logo"><i class="fas fa-clock"></i> Time Emporium</a>
        <div class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </div>
        <nav class="nav-links" id="navLinks">
            <a href="#">Home</a>
            <a href="#collections">Collections</a>
            <a href="#brands">Brands</a>
            <a href="#featured-products">Featured Products</a>
            <a href="../Codex/auth/login.php" class="cta-button" style="background-color: #d4af37;">Login</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Timeless Elegance, Modern Precision</h1>
            <p>Discover our curated collection of luxury timepieces from the world's finest watchmakers. Each watch tells a story of craftsmanship and heritage.</p>
            <div class="hero-buttons">
                <a href="#collections" class="cta-button">Explore Collections</a>
                <a href="../Codex/auth/login.php" class="secondary-button">New Arrivals</a>
            </div>
        </div>
        <div class="hero-image"></div>
    </section>

    <!-- Collections Section -->
    <section class="collections" id="collections">
        <div class="section-title">
            <h2>Our Collections</h2>
            <p>Hand-selected timepieces that combine artistry with precision engineering.</p>
        </div>
        <div class="collections-grid">
            <div class="collection-card">
                <div class="collection-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <h3>Digital</h3>
                <p>Exquisite watches from prestigious brands that define opulence and status.</p>
                <a href="../Codex/auth/login.php" class="collection-link">View Collection →</a>
            </div>
            <div class="collection-card">
                <div class="collection-icon">
                    <i class="fas fa-compass"></i>
                </div>
                <h3>Analog</h3>
                <p>Robust timepieces built for adventure with advanced functionality.</p>
                <a href="../Codex/auth/login.php" class="collection-link">View Collection →</a>
            </div>
            <div class="collection-card">
                <div class="collection-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <h3>Smart Watch</h3>
                <p>Elegant watches designed to complement formal attire with refined sophistication.</p>
                <a href="../Codex/auth/login.php" class="collection-link">View Collection →</a>
            </div>
        </div>
    </section>

    <!-- Brands Section -->
    <section class="brands" id="brands">
        <div class="section-title">
            <h2>Featured Brands</h2>
            <p>We partner with the most prestigious names in horology.</p>
        </div>
        <div class="brands-grid" id="brandsContainer">
            <!-- Brand cards will be inserted here by JavaScript -->
        </div>
    </section>

    <!-- Featured Watch -->
    <section class="featured-watch">
        <div class="watch-container">
            <div class="watch-image">
                <div class="watch-badge">Coming Soon</div>
            </div>
            <div class="watch-details">
                <h2>Royal Chronograph</h2>
                <h3>Limited Edition</h3>
                <p class="watch-description">This masterpiece features a hand-wound mechanical movement with 65 hours power reserve, housed in an 18k rose gold case with sapphire crystal case back.</p>
                <div class="watch-features">
                    <div class="feature">
                        <i class="fas fa-water"></i>
                        <span>100m Water Resistant</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-clock"></i>
                        <span>Swiss Movement</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-gem"></i>
                        <span>18k Gold Case</span>
                    </div>
                </div>
                <div class="watch-price">
                    <span class="price">P12,499</span>
                    <a href="../Codex/auth/login.php" class="cta-button"><i class="fas fa-shopping-cart"></i> Pre-Order</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products" id="featured-products">
        <div class="section-title">
            <h2>Featured Products</h2>
            <p>Explore our most popular timepieces loved by watch enthusiasts worldwide.</p>
        </div>
        <div class="products-container">
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-id="<?= $product['id'] ?>">
                            <div class="product-image">
                                <img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                            </div>
                            <div class="product-details">
                                <h3><?= htmlspecialchars($product['name']) ?></h3>
                                <div class="product-rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
                                <div class="product-price">
                                    <span class="price">₱<?= number_format($product['price'], 2) ?></span>
                                    <p class="stock-info">Stock: <?= htmlspecialchars($product['stock']) ?></p>
                                </div>
                                <a href="../Codex/auth/login.php" class="cta-button add-to-cart"><i class="fas fa-shopping-cart"></i> Add to Cart</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-products">No products found. Please check back later for our featured products.</p>
            <?php endif; ?>
            
            <div class="view-all-container">
                <a href="../Codex/auth/login.php" class="view-all-button">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer id="contact">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Time Emporium</h3>
                <p>Your trusted source for authentic luxury and affordable timepieces since 2025.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Shop</h3>
                <ul class="footer-links">
                    <li><a href="../Codex/auth/login.php">New Arrivals</a></li>
                    <li><a href="../Codex/auth/login.php">Best Sellers</a></li>
                    <li><a href="../Codex/auth/login.php">Limited Editions</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Contact</h3>
                <p><i class="fas fa-map-marker-alt"></i> CTU, Sabang, Danao, Cebu</p>
                <p><i class="fas fa-phone"></i> 09925728981 </p>
                <p><i class="fas fa-envelope"></i> TimeEmporium@gmail.com</p>
                <p><i class="fas fa-clock"></i> Mon-Sat: 10AM - 7PM EST</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 Time Emporium. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms</a> | <a href="#">Authenticity Guarantee</a></p>
        </div>
    </footer>

    <script src="../Codex/assets/js/script.js"></script>
</body>
</html>
