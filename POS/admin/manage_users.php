<?php
session_start();
require '../config/database.php';

// Restrict access to admin only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Manage Users</h2>
<a href="dashboard.php">Back to Dashboard</a> | <a href="add_user.php">Add User</a>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Username</th>
        <th>Role</th>
        <th>Action</th>
    </tr>
    <?php foreach ($users as $user): ?>
    <tr>
        <td><?= $user['id'] ?></td>
        <td><?= $user['username'] ?></td>
        <td><?= $user['role'] ?></td>
        <td>
            <a href="edit_user.php?id=<?= $user['id'] ?>">Edit</a> | 
            <a href="delete_user.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
