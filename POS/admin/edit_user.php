<?php
session_start();
require '../config/database.php';

// Restrict access to admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: manage_users.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $role = $_POST['role'];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $password, $role, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $role, $id]);
    }

    header("Location: manage_users.php");
    exit();
}
?>

<h2>Edit User</h2>
<form method="POST">
    <label>Username:</label>
    <input type="text" name="username" value="<?= $user['username'] ?>" required><br>

    <label>New Password (leave blank to keep current):</label>
    <input type="password" name="password"><br>

    <label>Role:</label>
    <select name="role">
        <option value="admin" <?= ($user['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
        <option value="cashier" <?= ($user['role'] === 'cashier') ? 'selected' : '' ?>>Cashier</option>
    </select><br>

    <button type="submit">Update User</button>
</form>
<a href="manage_users.php">Back</a>
