<?php
$catalog_query = "
    SELECT p.product_id, p.product_name, p.category, p.price, p.size, i.current_stock
    FROM products p
    INNER JOIN inventory i ON p.product_id = i.product_id
    WHERE p.availability = 'Available' AND i.current_stock > 0
    ORDER BY p.product_name ASC
";

$catalog_result = $conn->query($catalog_query);
