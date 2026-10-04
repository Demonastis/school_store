<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

/**
 * Remove one product completely from the session cart.
 * Inventory is NOT changed here because stock is only deducted during checkout.
 */
if (isset($_POST['remove_from_cart_action'])) {
    $pid = intval($_POST['product_id']);

    if (isset($_SESSION['cart'][$pid])) {
        unset($_SESSION['cart'][$pid]);
    }

    header("Location: customer_dashboard.php?status=item_removed#shop");
    exit();
}

/**
 * Add a product to the session cart.
 */
if (!isset($_POST['add_to_cart_action'])) {
    return;
}

$pid = intval($_POST['product_id']);

$stock_chk = $conn->prepare(
    "SELECT current_stock
     FROM inventory
     WHERE product_id = ?
     LIMIT 1"
);
$stock_chk->bind_param("i", $pid);
$stock_chk->execute();

$stock_row = $stock_chk->get_result()->fetch_assoc();
$avail_stock = $stock_row['current_stock'] ?? 0;

if ($avail_stock > 0) {
    if (isset($_SESSION['cart'][$pid])) {
        if ($_SESSION['cart'][$pid] < $avail_stock) {
            $_SESSION['cart'][$pid]++;
        }
    } else {
        $_SESSION['cart'][$pid] = 1;
    }

    header("Location: customer_dashboard.php?status=item_added#shop");
    exit();
}
