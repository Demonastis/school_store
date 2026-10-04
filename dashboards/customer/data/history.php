<?php
$history_query = "
    SELECT
        t.transaction_id,
        t.transaction_date,
        t.total_amount,
        (
            SELECT SUM(quantity)
            FROM order_items
            WHERE transaction_id = t.transaction_id
        ) AS total_items
    FROM transactions t
    WHERE t.cashier_id = ?
      AND t.status = 'Completed'
    ORDER BY t.transaction_date DESC
";

$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $user_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
