<?php
/**
 * Creates a pending order and a PayMongo Hosted Checkout Session.
 * Inventory is deducted only after PayMongo confirms payment through the webhook.
 */
if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' ||
    !isset($_POST['checkout_cart_action']) ||
    empty($_SESSION['cart'])
) {
    return;
}

require_once __DIR__ . '/../payments/PayMongoClient.php';

$conn->begin_transaction();

try {
    $total_bill = 0.00;
    $order_items_buffer = [];

    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = (int) $pid;
        $qty = (int) $qty;

        if ($pid <= 0 || $qty <= 0) {
            throw new Exception('Checkout halted: invalid cart item.');
        }

        $item_stmt = $conn->prepare(
            "SELECT p.price, p.product_name, i.current_stock
             FROM products p
             INNER JOIN inventory i ON p.product_id = i.product_id
             WHERE p.product_id = ?
             LIMIT 1"
        );
        $item_stmt->bind_param("i", $pid);
        $item_stmt->execute();
        $item_meta = $item_stmt->get_result()->fetch_assoc();

        if (!$item_meta) {
            throw new Exception("Checkout halted: Product #{$pid} could not be found.");
        }

        if ((int) $item_meta['current_stock'] < $qty) {
            throw new Exception(
                "Checkout halted: '{$item_meta['product_name']}' does not have enough remaining stock."
            );
        }

        $unit_price = (float) $item_meta['price'];
        $subtotal = $unit_price * $qty;
        $total_bill += $subtotal;

        $order_items_buffer[] = [
            'product_id' => $pid,
            'qty' => $qty,
            'unit_price' => $unit_price,
            'subtotal' => $subtotal,
            'product_name' => $item_meta['product_name'],
        ];
    }

    if ($total_bill <= 0) {
        throw new Exception('Checkout halted: order total must be greater than zero.');
    }

    $status = 'Pending';
    $tx_stmt = $conn->prepare(
        "INSERT INTO transactions
            (cashier_id, transaction_date, total_amount, status)
         VALUES (?, NOW(), ?, ?)"
    );
    $tx_stmt->bind_param("ids", $user_id, $total_bill, $status);
    $tx_stmt->execute();
    $new_tx_id = (int) $tx_stmt->insert_id;

    foreach ($order_items_buffer as $row) {
        // Matches the supplied schema: unit_price + subtotal.
        $oi_stmt = $conn->prepare(
            "INSERT INTO order_items
                (transaction_id, product_id, quantity, unit_price, subtotal)
             VALUES (?, ?, ?, ?, ?)"
        );
        $oi_stmt->bind_param(
            "iiidd",
            $new_tx_id,
            $row['product_id'],
            $row['qty'],
            $row['unit_price'],
            $row['subtotal']
        );
        $oi_stmt->execute();
    }

    $configFile = __DIR__ . '/../payments/paymongo_config.php';
    if (is_file($configFile)) {
        require_once $configFile;
    }

    $configuredAppUrl = defined('PAYMONGO_APP_URL') ? (string) PAYMONGO_APP_URL : '';
    $appUrl = rtrim($configuredAppUrl !== '' ? $configuredAppUrl : (string) getenv('PAYMONGO_APP_URL'), '/');
    if ($appUrl === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $appUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    $reference = 'ORDER-' . $new_tx_id;
    $lineItems = [];

    foreach ($order_items_buffer as $row) {
        $lineItems[] = [
            'name' => $row['product_name'],
            'amount' => (int) round($row['unit_price'] * 100),
            'currency' => 'PHP',
            'quantity' => $row['qty'],
        ];
    }

    $client = new PayMongoClient();
    $checkout = $client->createCheckoutSession([
        'line_items' => $lineItems,
        'payment_method_types' => ['card', 'gcash', 'qrph'],
        'success_url' => $appUrl . '/customer_dashboard.php?status=payment_returned#orders',
        'cancel_url' => $appUrl . '/customer_dashboard.php?status=payment_cancelled#orders',
        'reference_number' => $reference,
        'send_email_receipt' => true,
        'metadata' => [
            'transaction_id' => (string) $new_tx_id,
            'user_id' => (string) $user_id,
        ],
    ]);

    $checkoutSessionId = $checkout['id'];
    $checkoutUrl = $checkout['attributes']['checkout_url'];
    $paymentMethod = 'PayMongo Hosted Checkout';
    $paymentStatus = 'Pending';
    $paymentAmount = $total_bill;

    $payment_stmt = $conn->prepare(
        "INSERT INTO payments
            (transaction_id, payment_method, amount, payment_status,
             paymongo_checkout_session_id, paymongo_checkout_url)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $payment_stmt->bind_param(
        "isdsss",
        $new_tx_id,
        $paymentMethod,
        $paymentAmount,
        $paymentStatus,
        $checkoutSessionId,
        $checkoutUrl
    );
    $payment_stmt->execute();

    $audit_desc =
        "Customer {$user_name} created PayMongo checkout for order #TXN-{$new_tx_id} valued at ₱"
        . number_format($total_bill, 2) . ". Payment is pending.";

    $audit_stmt = $conn->prepare(
        "INSERT INTO audit_logs
            (user_id, action, module, description)
         VALUES (?, 'Create Payment Checkout', 'PayMongo Payment Module', ?)"
    );
    $audit_stmt->bind_param("is", $user_id, $audit_desc);
    $audit_stmt->execute();

    $conn->commit();
    $_SESSION['cart'] = [];

    header('Location: ' . $checkoutUrl);
    exit();
} catch (Throwable $e) {
    $conn->rollback();

    header(
        'Location: customer_dashboard.php?error=' .
        urlencode($e->getMessage())
    );
    exit();
}
