<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Emporium - <?php echo $pageTitle ?? 'Luxury Watches'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
    <?php if (isset($authStyle)): ?>
        <link rel="stylesheet" href="/auth/auth-style.css">
    <?php endif; ?>
</head>
<body>
    <header id="header">
        <a href="/" class="logo"><i class="fas fa-clock"></i> Time Emporium</a>
        <nav class="nav-links">
    <a href="/">Home</a>
    <a href="#collections">Collections</a>
    <a href="#brands">Brands</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="/auth/logout.php" class="cta-button"><i class="fas fa-sign-out-alt"></i> Logout</a>
    <?php else: ?>
        <a href="/auth/login.php" class="cta-button"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="/auth/register.php" class="secondary-button">Register</a>
    <?php endif; ?>
</nav>
    </header>