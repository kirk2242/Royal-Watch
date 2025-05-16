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
$success_message = '';

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: manage_users.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = $_POST['username'];
    $new_password = $_POST['password'];
    $new_role = $_POST['role'];

    // Update user details in the database
    $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
    $stmt->execute([$new_username, $new_role, $user_id]);

    // Update password if provided
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed_password, $user_id]);
    }

    $success_message = "User updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/edit_user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <h2>Edit User <span class="user-id">ID: <?= $user_id ?></span></h2>
    
    <?php if ($success_message): ?>
        <div class="success-message">
            <?= $success_message ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" placeholder="Leave blank to keep current password">
        </div>

        <div class="form-group">
            <label>Select Role:</label>
            <div class="role-cards">
                <div class="role-card <?= $user['role'] == 'admin' ? 'selected' : '' ?>" data-role="admin">
                    <div class="role-card-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="role-card-title">Admin</div>
                    <div class="role-card-desc">Can manage products and view reports</div>
                </div>
                <div class="role-card <?= $user['role'] == 'cashier' ? 'selected' : '' ?>" data-role="cashier">
                    <div class="role-card-icon"><i class="fas fa-cash-register"></i></div>
                    <div class="role-card-title">Cashier</div>
                    <div class="role-card-desc">Can process sales and manage inventory</div>
                </div>
                <div class="role-card <?= $user['role'] == 'customer' ? 'selected' : '' ?>" data-role="customer">
                    <div class="role-card-icon"><i class="fas fa-user"></i></div>
                    <div class="role-card-title">Customer</div>
                    <div class="role-card-desc">Can browse products and make purchases</div>
                </div>
            </div>
            <input type="hidden" name="role" id="selected-role" value="<?= $user['role'] ?>">
        </div>

        <div class="button-group">
            <button type="submit">Update User</button>
            <a href="manage_users.php" class="btn back-btn">Back to Manage Users</a>
            <a href="delete_user.php?id=<?= $user_id ?>" class="btn delete-btn" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">Delete User</a>
        </div>
    </form>
</div>

<script>
// Role card selection
document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', function() {
        // Remove selected class from all cards
        document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
        
        // Add selected class to clicked card
        this.classList.add('selected');
        
        // Update hidden input value
        document.getElementById('selected-role').value = this.dataset.role;
    });
});
</script>

</body>
</html>