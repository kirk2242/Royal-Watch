<?php
require '../config/database.php';

// Fetch item names and quantities sold
$stmt = $pdo->query("SELECT products.name AS label, SUM(sales_items.quantity) AS value 
                     FROM sales_items 
                     JOIN products ON sales_items.product_id = products.id 
                     GROUP BY products.name");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Format data for the chart
$response = [
    'labels' => array_column($data, 'label'),
    'values' => array_column($data, 'value')
];

header('Content-Type: application/json');
echo json_encode($response);
?>
