<?php
$pageTitle = "Register";
$authStyle = true;
require_once '../includes/header.php'; // Corrected path
?>
<link rel="stylesheet" href="/assets/css/auth-style.css">
</head>
<body>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../includes/db.php';

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $address = $_POST['address'];
    $contact_number = $_POST['contact_number'];

    $stmt = $pdo->prepare("INSERT INTO customers (username, password, firstname, lastname, age, sex, address, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$username, $password, $firstname, $lastname, $age, $sex, $address, $contact_number]);

    header("Location: login.php");
    exit();
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
