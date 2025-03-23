<?php
session_start();
require '../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $age = intval($_POST['age']);
    $sex = $_POST['sex'];
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);

    try {
        $pdo->beginTransaction();

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            throw new Exception("Username already exists!");
        }

        // Insert into users table
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'customer')");
        $stmt->execute([$username, $password]);

        // Get the newly created user ID
        $user_id = $pdo->lastInsertId();

        // Insert into customers table
        $stmt = $pdo->prepare("INSERT INTO customers (user_id, firstname, lastname, age, sex, address, contact_number) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $firstname, $lastname, $age, $sex, $address, $contact_number]);

        $pdo->commit();
        header("Location: login.php?success=Account created!");
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../assets/css/register.css">
</head>
<body>
<div class="register-container">
    <h2>Register</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>First Name:</label>
        <input type="text" name="firstname" required>

        <label>Last Name:</label>
        <input type="text" name="lastname" required>

        <label>Age:</label>
        <input type="number" name="age" required>

        <label>Sex:</label>
        <select name="sex" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label>Address:</label>
        <textarea name="address" required></textarea>

        <label>Contact Number:</label>
        <input type="text" name="contact_number" required>

        <button type="submit">Register</button>
    </form>

    <a href="login.php">Already have an account? Login here</a>

    <button class="back-btn" onclick="window.location.href='../index.php'">Back to Home</button>
</div>
</body>
</html>
