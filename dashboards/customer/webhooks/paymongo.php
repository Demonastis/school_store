<?php
/**
 * PayMongo webhook endpoint.
 * Configure this URL in PayMongo as a TEST-mode webhook endpoint and subscribe
 * to checkout_session.payment.paid.
 */
require_once __DIR__ . '/../../../config/db.php';

$configFile = __DIR__ . '/../payments/paymongo_config.php';
if (is_file($configFile)) {
    require_once $configFile;
}

header('Content-Type: application/json');

$webhookSecret = defined('PAYMONGO_WEBHOOK_SECRET') && PAYMONGO_WEBHOOK_SECRET !== ''
    ? trim((string) PAYMONGO_WEBHOOK_SECRET)
    : trim((string) getenv('PAYMONGO_WEBHOOK_SECRET'));
$rawBody = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

if ($webhookSecret === '' || $rawBody === '' || $signatureHeader === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Webhook configuration or payload is missing.']);
    exit();
}

function parsePayMongoSignature(string $header): array
{
    $parts = [];
    foreach (explode(',', $header) as $part) {
        [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
        $parts[$key] = $value;
    }
    return $parts;
}

$signature = parsePayMongoSignature($signatureHeader);
$timestamp = $signature['t'] ?? '';
$testSignature = $signature['te'] ?? '';
$liveSignature = $signature['li'] ?? '';

if ($timestamp === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid PayMongo signature.']);
    exit();
}

$expectedSignature = hash_hmac('sha256', $timestamp . '.' . $rawBody, $webhookSecret);
$providedSignature = $testSignature !== '' ? $testSignature : $liveSignature;

if ($providedSignature === '' || !hash_equals($expectedSignature, $providedSignature)) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Invalid PayMongo signature.']);
    exit();
}

$payload = json_decode($rawBody, true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON payload.']);
    exit();
}

// Current PayMongo webhook envelope: data.type + data.data.
$event = $payload['data'] ?? [];
$eventType = $event['type'] ?? '';
$session = $event['data'] ?? [];

if ($eventType !== 'checkout_session.payment.paid') {
    http_response_code(200);
    echo json_encode(['ok' => true, 'ignored' => true]);
    exit();
}

$checkoutSessionId = $session['id'] ?? '';
$attributes = $session['attributes'] ?? [];
$referenceNumber = $attributes['reference_number'] ?? '';
$livemode = !empty($event['livemode']);

// This project starts in test mode. Do not accept a live event here.
$secretKey = defined('PAYMONGO_SECRET_KEY') && PAYMONGO_SECRET_KEY !== ''
    ? (string) PAYMONGO_SECRET_KEY
    : (string) getenv('PAYMONGO_SECRET_KEY');
$isConfiguredLive = strpos($secretKey, 'sk_live_') === 0;
if ($livemode !== $isConfiguredLive) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Webhook mode does not match configured PayMongo key.']);
    exit();
}

if ($checkoutSessionId === '' || $referenceNumber === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Checkout session reference is missing.']);
    exit();
}

$payments = $attributes['payments'] ?? [];
$paymongoPaymentId = $payments[0]['id'] ?? null;
$paymentAmountCents = isset($payments[0]['attributes']['amount'])
    ? (int) $payments[0]['attributes']['amount']
    : null;

$conn->begin_transaction();

try {
    $lookup = $conn->prepare(
        "SELECT
            p.payment_id,
            p.transaction_id,
            p.amount,
            p.payment_status,
            t.cashier_id,
            t.total_amount,
            t.status
         FROM payments p
         INNER JOIN transactions t ON t.transaction_id = p.transaction_id
         WHERE p.paymongo_checkout_session_id = ?
            OR t.transaction_id = ?
         LIMIT 1
         FOR UPDATE"
    );

    $referenceTransactionId = (int) preg_replace('/^ORDER-/', '', $referenceNumber);
    $lookup->bind_param("si", $checkoutSessionId, $referenceTransactionId);
    $lookup->execute();
    $paymentRow = $lookup->get_result()->fetch_assoc();

    if (!$paymentRow) {
        throw new RuntimeException('Order for PayMongo checkout was not found.');
    }

    // Idempotency: PayMongo may retry a webhook. Never deduct stock twice.
    if ($paymentRow['payment_status'] === 'Paid') {
        $conn->commit();
        http_response_code(200);
        echo json_encode(['ok' => true, 'already_processed' => true]);
        exit();
    }

    if ($paymentAmountCents !== null) {
        $expectedCents = (int) round(((float) $paymentRow['amount']) * 100);
        if ($paymentAmountCents !== $expectedCents) {
            throw new RuntimeException('PayMongo payment amount does not match the order total.');
        }
    }

    // Lock inventory rows before checking/deducting stock.
    $itemsStmt = $conn->prepare(
        "SELECT oi.product_id, oi.quantity, p.product_name, i.current_stock
         FROM order_items oi
         INNER JOIN products p ON p.product_id = oi.product_id
         INNER JOIN inventory i ON i.product_id = oi.product_id
         WHERE oi.transaction_id = ?
         FOR UPDATE"
    );
    $txId = (int) $paymentRow['transaction_id'];
    $itemsStmt->bind_param('i', $txId);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();

    $items = [];
    while ($item = $itemsResult->fetch_assoc()) {
        if ((int) $item['current_stock'] < (int) $item['quantity']) {
            throw new RuntimeException(
                "Payment received, but '{$item['product_name']}' no longer has enough stock."
            );
        }
        $items[] = $item;
    }

    foreach ($items as $item) {
        $stockStmt = $conn->prepare(
            "UPDATE inventory
             SET current_stock = current_stock - ?
             WHERE product_id = ?"
        );
        $quantity = (int) $item['quantity'];
        $productId = (int) $item['product_id'];
        $stockStmt->bind_param('ii', $quantity, $productId);
        $stockStmt->execute();
    }

    $paidStatus = 'Paid';
    $updatePayment = $conn->prepare(
        "UPDATE payments
         SET payment_status = ?, paymongo_payment_id = ?, paid_at = NOW()
         WHERE payment_id = ?"
    );
    $updatePayment->bind_param(
        'ssi',
        $paidStatus,
        $paymongoPaymentId,
        $paymentRow['payment_id']
    );
    $updatePayment->execute();

    // Keep transaction status Pending so the store's fulfillment workflow can move it to Ready/Completed.
    $auditUserId = (int) $paymentRow['cashier_id'];
    $auditDesc =
        "PayMongo payment confirmed for order #TXN-{$txId} (session {$checkoutSessionId}).";
    $auditStmt = $conn->prepare(
        "INSERT INTO audit_logs
            (user_id, action, module, description)
         VALUES (?, 'Payment Confirmed', 'PayMongo Payment Module', ?)"
    );
    $auditStmt->bind_param('is', $auditUserId, $auditDesc);
    $auditStmt->execute();

    $conn->commit();

    http_response_code(200);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    $conn->rollback();

    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
