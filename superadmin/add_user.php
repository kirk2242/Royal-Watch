
<?php
session_start();
require '../config/database.php';

// Ensure only Superadmin can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    header("Location: ../auth/login.php");
    exit();
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        $error = "All fields are required";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);

            if ($stmt->rowCount() > 0) {
                $error = "Username already exists!";
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                // Insert into users table
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashedPassword, $role]);

                $success = "User added successfully!";
                
                // Clear form data after successful submission
                $username = "";
                $password = "";
                $role = "";
            }
        } catch (Exception $e) { 
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/add_user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <h2>Add New User</h2>
    
    <?php if (!empty($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" id="addUserForm">
        <div class="form-group">
            <label for="username" class="required">Username</label>
            <input type="text" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
        </div>

        <div class="form-group">
            <label for="password" class="required">Password</label>
            <div class="password-toggle">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                    <i class="far fa-eye" id="toggleIcon"></i>
                </button>
            </div>
            <div class="password-strength">
                <div class="password-strength-meter" id="passwordStrengthMeter"></div>
            </div>
            <div class="password-feedback" id="passwordFeedback">Password strength will be shown here</div>
        </div>

        <div class="form-group">
            <label class="required">Select Role</label>
            <div class="role-cards">
                <div class="role-card <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>" onclick="selectRole('admin')">
                    <div class="role-card-icon"><i class="fas fa-user-shield"></i></div>
                    <div class="role-card-title">Admin</div>
                    <div class="role-card-desc">Can manage products, view reports, and handle orders</div>
                </div>
                <div class="role-card <?php echo (isset($role) && $role === 'cashier') ? 'selected' : ''; ?>" onclick="selectRole('cashier')">
                    <div class="role-card-icon"><i class="fas fa-cash-register"></i></div>
                    <div class="role-card-title">Cashier</div>
                    <div class="role-card-desc">Can process sales and manage basic inventory</div>
                </div>
            </div>
            <input type="hidden" id="role" name="role" value="<?php echo isset($role) ? $role : ''; ?>" required>
        </div>

        <button type="submit">Add User</button>
    </form>

    <a href="manage_users.php" class="btn back-btn">Back to Manage Users</a>
</div>

<script>
// Toggle password visibility
function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Select role card
function selectRole(role) {
    document.querySelectorAll('.role-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    document.querySelector(`.role-card:nth-child(${role === 'admin' ? '1' : '2'})`).classList.add('selected');
    document.getElementById('role').value = role;
}

// Password strength meter
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const meter = document.getElementById('passwordStrengthMeter');
    const feedback = document.getElementById('passwordFeedback');
    
    // Remove all classes
    meter.className = 'password-strength-meter';
    
    if (password.length === 0) {
        meter.style.width = '0';
        feedback.textContent = 'Password strength will be shown here';
        return;
    }
    
    // Check password strength
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength += 1;
    
    // Contains number
    if (/\d/.test(password)) strength += 1;
    
    // Contains special character
    if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 1;
    
    // Contains uppercase and lowercase
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 1;
    
    // Update meter
    if (strength <= 1) {
        meter.classList.add('strength-weak');
        feedback.textContent = 'Weak password';
        feedback.style.color = '#e74c3c';
    } else if (strength <= 3) {
        meter.classList.add('strength-medium');
        feedback.textContent = 'Medium strength password';
        feedback.style.color = '#f39c12';
    } else {
        meter.classList.add('strength-strong');
        feedback.textContent = 'Strong password';
        feedback.style.color = '#27ae60';
    }
});

// Form validation
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    const role = document.getElementById('role').value;
    
    if (!username || !password || !role) {
        e.preventDefault();
        alert('All fields are required');
    } else if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long');
    }
});

// Initialize role selection if value exists
document.addEventListener('DOMContentLoaded', function() {
    const roleValue = document.getElementById('role').value;
    if (roleValue) {
        selectRole(roleValue);
    }
});
</script>

</body>
</html>
</qodoArtifact>