<?php
session_start();
require '../config/database.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = $error = "";

// Fetch customer details
$stmt = $pdo->prepare("SELECT u.username, c.firstname, c.lastname, c.age, c.sex, c.address, c.contact_number 
                       FROM users u 
                       LEFT JOIN customers c ON u.id = c.user_id 
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_username = trim($_POST['username']);
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
    $firstname = trim($_POST['firstname']);
    $lastname = trim($_POST['lastname']);
    $age = intval($_POST['age']);
    $sex = $_POST['sex'];
    $address = trim($_POST['address']);
    $contact_number = trim($_POST['contact_number']);

    try {
        $pdo->beginTransaction();

        // Update username
        $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->execute([$new_username, $user_id]);

        // Update password if changed
        if ($new_password) {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_password, $user_id]);
        }

        // Update customer details
        $stmt = $pdo->prepare("UPDATE customers SET firstname = ?, lastname = ?, age = ?, sex = ?, address = ?, contact_number = ? WHERE user_id = ?");
        $stmt->execute([$firstname, $lastname, $age, $sex, $address, $contact_number, $user_id]);

        $pdo->commit();
        $success = "Account updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error updating account: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account</title>
    <link rel="stylesheet" href="../assets/css/account.css">
    
</head>
<body>
<div class="account-container">
    <h2>My Account</h2>
    
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>
    <?php if ($error) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?= htmlspecialchars($customer['username']) ?>" required>

        <label>New Password (leave blank to keep current password):</label>
        <input type="password" name="password">

        <label>First Name:</label>
        <input type="text" name="firstname" value="<?= htmlspecialchars($customer['firstname']) ?>" required>

        <label>Last Name:</label>
        <input type="text" name="lastname" value="<?= htmlspecialchars($customer['lastname']) ?>" required>

        <label>Age:</label>
        <input type="number" name="age" value="<?= htmlspecialchars($customer['age']) ?>" required>

        <label>Sex:</label>
        <select name="sex" required>
            <option value="Male" <?= ($customer['sex'] == 'Male') ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($customer['sex'] == 'Female') ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= ($customer['sex'] == 'Other') ? 'selected' : '' ?>>Other</option>
        </select>

        <label>Address:</label>
        <textarea name="address" required><?= htmlspecialchars($customer['address']) ?></textarea>

        <label>Contact Number:</label>
        <input type="text" name="contact_number" value="<?= htmlspecialchars($customer['contact_number']) ?>" required>

        <button type="submit">Update Account</button>
    </form>

    <button class="back-btn" onclick="window.location.href='home.php'">Back to Home</button>
</div>

</body>
</html>
