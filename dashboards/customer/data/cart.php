<?php
$cart_total_items = array_sum($_SESSION['cart']);
$cart_items = [];
$running_cart_total = 0.00;

foreach ($_SESSION['cart'] as $pid => $qty) {
    $crt_stmt = $conn->prepare(
        "SELECT product_name, price
         FROM products
         WHERE product_id = ?"
    );
    $crt_stmt->bind_param("i", $pid);
    $crt_stmt->execute();

    $crt_item = $crt_stmt->get_result()->fetch_assoc();

    // Ignore stale cart entries if a product was removed from the catalog.
    if (!$crt_item) {
        continue;
    }

    $sub = floatval($crt_item['price']) * $qty;
    $running_cart_total += $sub;

    $cart_items[] = [
        'product_id' => (int) $pid,
        'product_name' => $crt_item['product_name'],
        'price' => $crt_item['price'],
        'qty' => $qty,
        'subtotal' => $sub,
    ];
}
