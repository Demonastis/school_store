<?php
// dashboards/custodian/modules/dashboard.php
// Inherits standard database connection $conn from parent shell container

// 1. Fetch Dynamic Custodian Metrics
// Orders Awaiting Packing (Pending checkout transactions)
$awaiting_query = "SELECT COUNT(*) as total FROM transactions WHERE status = 'Pending'";
$awaiting_result = $conn->query($awaiting_query);
$awaiting_count = $awaiting_result->fetch_assoc()['total'] ?? 0;

// Orders Packed & Ready for Pickup
$ready_query = "SELECT COUNT(*) as total FROM transactions WHERE status = 'Ready'";
$ready_result = $conn->query($ready_query);
$ready_count = $ready_result->fetch_assoc()['total'] ?? 0;

// Incoming Deliveries scheduled for today or currently pending
$incoming_query = "
    SELECT COUNT(*) as total 
    FROM deliveries d
    INNER JOIN purchase_orders po ON d.purchase_order_id = po.purchase_order_id
    WHERE d.status = 'Pending' AND po.expected_delivery_date <= CURRENT_DATE()
";
$incoming_result = $conn->query($incoming_query);
$incoming_today = $incoming_result->fetch_assoc()['total'] ?? 0;


// 2. Query Detailed Orders List for Custodian Fulfillment Processing Queue
$orders_query = "
    SELECT t.transaction_id, t.transaction_date, t.total_amount, t.status,
           u.first_name, u.last_name,
           (SELECT SUM(quantity) FROM order_items WHERE transaction_id = t.transaction_id) as total_items
    FROM transactions t
    LEFT JOIN users u ON t.cashier_id = u.user_id
    WHERE t.status IN ('Pending', 'Ready')
    ORDER BY t.transaction_date ASC
";
$orders_result = $conn->query($orders_query);
?>

<!-- Operational Fulfillment Metrics -->
<div class="metrics-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; display: flex; align-items: center; gap: 15px;">
        <div class="card-icon orange" style="font-size: 2rem; background: #fff3cd; padding: 10px; border-radius: 8px;">⏳</div>
        <div class="card-data">
            <p style="margin: 0; color: #6c757d; font-size: 0.9rem;">Awaiting Packing</p>
            <h3 style="margin: 5px 0 0 0; font-size: 1.5rem; font-weight: bold; color: #fd7e14;"><?= $awaiting_count ?> Orders</h3>
        </div>
    </div>
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; display: flex; align-items: center; gap: 15px;">
        <div class="card-icon blue" style="font-size: 2rem; background: #cff4fc; padding: 10px; border-radius: 8px;">📦</div>
        <div class="card-data">
            <p style="margin: 0; color: #6c757d; font-size: 0.9rem;">Ready for Pickup</p>
            <h3 style="margin: 5px 0 0 0; font-size: 1.5rem; font-weight: bold; color: #0dcaf0;"><?= $ready_count ?> Orders</h3>
        </div>
    </div>
    <div class="card" style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0; display: flex; align-items: center; gap: 15px;">
        <div class="card-icon teal" style="font-size: 2rem; background: #d1e7dd; padding: 10px; border-radius: 8px;">🚚</div>
        <div class="card-data">
            <p style="margin: 0; color: #6c757d; font-size: 0.9rem;">Incoming Shipments</p>
            <h3 style="margin: 5px 0 0 0; font-size: 1.5rem; font-weight: bold; color: #198754;"><?= $incoming_today ?> Pending</h3>
        </div>
    </div>
</div>

<!-- Context Control Elements -->
<div class="action-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
    <h3 style="font-size: 1.1rem; font-weight: 600; margin: 0;">Active Customer Orders Queue</h3>
    <div class="btn-group">
        <!-- Direct clean routing connection link to your manager-shared logistics tab -->
        <a href="custodian_dashboard.php?page=deliveries" class="btn secondary" style="text-decoration: none; display: inline-block; background: #6c757d; color: white; padding: 8px 16px; border-radius: 4px; font-size: 0.9rem; font-weight: 500;">
            📥 Log New Delivery
        </a>
    </div>
</div>

<!-- Order Table Presentation Grid Layout Component -->
<div class="table-container" style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px;">Order ID</th>
                <th style="padding: 12px;">Order Placement Date</th>
                <th style="padding: 12px;">Customer/Cashier Account</th>
                <th style="padding: 12px;">Item Count Balance</th>
                <th style="padding: 12px;">Financial Value</th>
                <th style="padding: 12px;">Operational Fulfillment Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($orders_result->num_rows === 0): ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #868e96;">No active transaction orders require packing fulfillment right now.</td>
                </tr>
            <?php else: ?>
                <?php while ($order = $orders_result->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #e9ecef;">
                        <td style="padding: 12px; font-weight: bold;">#TXN-<?= $order['transaction_id'] ?></td>
                        <td style="padding: 12px; color: #555;"><?= date('M d, Y H:i', strtotime($order['transaction_date'])) ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?: 'Guest Checkout' ?></td>
                        <td style="padding: 12px; font-weight: 500;"><?= intval($order['total_items']) ?> units</td>
                        <td style="padding: 12px; font-weight: bold; color: #2b2b2b;">$<?= number_format($order['total_amount'], 2) ?></td>
                        <td style="padding: 12px;">
                            <?php if ($order['status'] === 'Pending'): ?>
                                <a href="process_fulfillment.php?action=pack&id=<?= $order['transaction_id'] ?>" style="background: #fd7e14; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold; display: inline-block;">
                                    ⚙️ Mark Packed
                                </a>
                            <?php else: ?>
                                <span style="background: #198754; color: white; padding: 5px 10px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; display: inline-block;">
                                    ✓ Ready for Release
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>




