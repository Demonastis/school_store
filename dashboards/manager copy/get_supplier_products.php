<?php
require_once '../../config/db.php';

if (isset($_GET['supplier_id'])) {
    $sid = intval($_GET['supplier_id']);
    $query = "SELECT sp.product_id, p.product_name, sp.wholesale_cost, sp.qty_per_unit, sp.lead_time_days 
              FROM supplier_products sp 
              JOIN products p ON sp.product_id = p.product_id 
              WHERE sp.supplier_id = $sid";
              
    $result = $conn->query($query);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($products);
    exit;
}
