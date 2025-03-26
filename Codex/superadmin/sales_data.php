<?php
require '../config/database.php';

try {
    $stmt = $pdo->query("
        SELECT DATE(s.created_at) AS sale_date, 
               SUM(si.quantity * si.price) AS total_sales
        FROM sales s
        JOIN sales_items si ON s.id = si.sale_id
        GROUP BY sale_date
    ");

    // Fetch results
    $salesData = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching sales data: " . $e->getMessage());
}

// Return JSON response (if used in AJAX charts)
header('Content-Type: application/json');
echo json_encode($salesData);
?>
