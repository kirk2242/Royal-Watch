<?php
session_start();
require '../config/database.php';

// Ensure only Superadmin can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$user_id = $_GET['id'];

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: manage_users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_role = $_POST['role'];

    // Update role in the database
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$new_role, $user_id]);

    header("Location: manage_users.php?success=User updated successfully");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/manage_users.css">
    </head>
<body>

<div class="container">
    <h2>Edit User</h2>

    <form method="POST">
        <label>Username:</label>
        <input type="text" value="<?= $user['username'] ?>" disabled>

        <label>Role:</label>
        <select name="role">
            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
            <option value="cashier" <?= $user['role'] == 'cashier' ? 'selected' : '' ?>>Cashier</option>
            <option value="customer" <?= $user['role'] == 'customer' ? 'selected' : '' ?>>Customer</option>
        </select>

        <button type="submit">Update User</button>
    </form>

    <a href="manage_users.php" class="btn back-btn">Back to Manage Users</a>
</div>

</body>
</html>
