<?php
$live_orders_query = "
    SELECT
        t.transaction_id,
        t.transaction_date,
        t.total_amount,
        t.status,
        pay.payment_status,
        pay.paymongo_checkout_url,
        (
            SELECT SUM(quantity)
            FROM order_items
            WHERE transaction_id = t.transaction_id
        ) AS total_pieces
    FROM transactions t
    LEFT JOIN payments pay ON pay.transaction_id = t.transaction_id
    WHERE t.cashier_id = ?
      AND t.status IN ('Pending', 'Ready')
    ORDER BY t.transaction_date DESC
";

$live_orders_stmt = $conn->prepare($live_orders_query);
$live_orders_stmt->bind_param("i", $user_id);
$live_orders_stmt->execute();
$live_orders_result = $live_orders_stmt->get_result();
