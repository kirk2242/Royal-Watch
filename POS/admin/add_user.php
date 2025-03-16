<?php
session_start();
require '../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        echo "All fields are required!";
        exit();
    }

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        echo "Username already exists!";
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $hashed_password, $role])) {
        echo "User added successfully!";
    } else {
        echo "Error: Could not add user.";
    }

    // Redirect back to user management page
    header("Location: manage_users.php");
    exit();
}
?>

<h2>Add User</h2>
<form method="POST">
    <label>Username:</label>
    <input type="text" name="username" required><br>
    
    <label>Password:</label>
    <input type="password" name="password" required><br>
    
    <label>Role:</label>
    <select name="role">
        <option value="admin">Admin</option>
        <option value="cashier">Cashier</option>
    </select><br>
    
    <button type="submit">Add User</button>
</form>
<a href="manage_users.php">Back</a>