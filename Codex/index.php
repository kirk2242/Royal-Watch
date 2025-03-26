<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Time Emporium</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <header>
        <h1>Time Emporium</h1>
        <nav>
            <a href="auth/login.php">Login</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </nav>
    </header>

    <section class="hero">
        <div class="hero-content">
            <h2>Efficient and Easy-to-Use POS System</h2>
            <p>Manage your sales, products, and customers with ease.</p>
            <div class="hero-buttons">
                <a href="auth/register.php" class="btn primary">Get Started</a>
            </div>
        </div>
    </section>

    <section id="about" class="content-section">
        <h2>About Our Product</h2>
        <p>Time Emporium is designed to streamline your business operations, offering features like barcode scanning, inventory tracking, and detailed sales reporting.</p>
    </section>

    <section id="contact" class="content-section">
        <h2>Contact Us</h2>
        <p>Email: <a href="mailto:support@timeemporium.com">support@timeemporium.com</a></p>
        <p>Phone: +123 456 7890</p>
    </section>

    <footer>
        <p>&copy; <?= date("Y"); ?> Time Emporium. All rights reserved.</p>
    </footer>

</body>
</html>
