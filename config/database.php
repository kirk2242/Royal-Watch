<?php

 $host = "localhost"; // Change if necessary
 $dbname = "u866427573_time"; // Ensure this matches your actual database name
 $username = "u866427573_time"; // Default username for XAMPP
 $password = "D6am|y9G>"; // Default password for XAMPP (empty)




// localhost database

// $host = "localhost"; // Change if necessary
// $dbname = "Time_Emporium_DB"; // Ensure this matches your actual database name
// $username = "root"; // Default username for XAMPP
// $password = ""; // Default password for XAMPP (empty)



try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>