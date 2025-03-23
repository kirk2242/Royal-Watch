<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            // Store user session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Debugging: Log user role
            error_log("User Role: " . $user['role']);

            // Redirect based on role
            switch ($user['role']) {
                case 'superadmin':
                    header("Location: ../superadmin/dashboard_superadmin.php");
                    break;
                case 'admin':
                    header("Location: ../admin/dashboard.php"); // FIXED PATH
                    break;
                case 'cashier':
                    header("Location: ../cashier/dashboard.php"); // FIXED PATH
                    break;
                case 'customer':
                    header("Location: ../customer/home.php");
                    break;
                default:
                    header("Location: login.php?error=Invalid role");
                    break;
            }
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/login.css">
</head>
<body>
<div class="login-container">
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <a href="register.php">Create an account</a>

    <button class="back-btn" onclick="window.location.href='../index.php'">Back to Home</button>
</div>

</body>
</html>
