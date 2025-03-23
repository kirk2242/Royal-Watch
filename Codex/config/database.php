<?php
// Database connection settings
$host = "localhost"; // Change if necessary
$dbname = "Time_Emporium_DB"; // Ensure this matches your actual database name
$username = "root"; // Default XAMPP/MAMP username
$password = ""; // Default XAMPP password (empty)

// Establish PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>