<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Success</title>
    <link rel="stylesheet" href="../assets/css/success.css">
</head>
<body>
    <div class="success-container">
        <h2>Order Placed Successfully!</h2>
        <p>Thank you for your purchase.</p>
        <button onclick="window.location.href='home.php'">Back to Home</button>
    </div>
</body>
</html>
