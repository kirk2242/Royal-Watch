<?php
session_start();
require '../config/database.php';

// Ensure only Superadmin can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all users (excluding superadmin)
$stmt = $pdo->query("SELECT * FROM users WHERE role != 'superadmin' ORDER BY role ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/manage_users.css">
    </head>
<body>

<div class="container">
    <h2>Manage Users</h2>
    
    <a href="dashboard_superadmin.php" class="btn back-btn">Back to Dashboard</a>
    <a href="add_user.php" class="btn">Add New User</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= $user['username'] ?></td>
                    <td><?= ucfirst($user['role']) ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn">Edit</a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
