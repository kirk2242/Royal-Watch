<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role']; // 'admin' or 'cashier'

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        $error = "Username already exists!";
    } else {
        // Insert user into database
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $password, $role])) {
            header("Location: login.php");
            exit();
        } else {
            $error = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="authstyle.css">
</head>
<body>

<div class="register-container">
    <h2>Register</h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Role:</label>
        <select name="role">
            <option value="admin">Admin</option>
            <option value="cashier">Customer</option>
        </select>

        <button type="submit">Register</button>
    </form>

    <a href="login.php">Already have an account? Login</a>
</div>

</body>
</html>
