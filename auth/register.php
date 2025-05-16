<?php
$pageTitle = "Register";
$authStyle = true;
require_once '../includes/header.php'; // Corrected path
?>
<link rel="stylesheet" href="../assets/css/authstyle.css">
</head>
<body>
<?php
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

<div class="auth-container">
    <div class="auth-card">
        <h1><i class="fas fa-user-plus"></i> Register</h1>
        
        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>

            <div class="form-group">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>

            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" required>
            </div>

            <div class="form-group">
                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" required></textarea>
            </div>

            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" required>
            </div>

            <button type="submit" class="auth-button">Register</button>
        </form>
        
        <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
    </div>
</div>
