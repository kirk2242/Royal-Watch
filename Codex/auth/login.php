<?php
$pageTitle = "Login";
$authStyle = true;
require_once '../includes/header.php'; // Corrected path
?>
<link rel="stylesheet" href="/assets/css/auth-style.css">
</head>
<body>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../config/database.php'; // Corrected path to the database file
    
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Store the role in the session

        // Redirect based on role
        switch ($user['role']) {
            case 'superadmin':
                header("Location: /superadmin/dashboard_superadmin.php");
                break;
            case 'admin':
                header("Location: /admin/dashboard.php");
                break;
            case 'cashier':
                header("Location: /cashier/dashboard.php");
                break;
            case 'customer':
                header("Location: /customer/home.php");
                break;
            default:
                header("Location: /");
                break;
        }
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h1><i class="fas fa-sign-in-alt"></i> Login</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="auth-button">Login</button>
        </form>
        
        <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>
</body>
</html>

