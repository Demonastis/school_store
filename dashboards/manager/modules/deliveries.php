<?php
// Ensure database connection wrapper exists prior to this file block
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_delivery_status'])) {
    $delivery_id = intval($_POST['delivery_id']);
    $new_status = $_POST['status'];
    $current_manager_id = 1; 

    if (in_array($new_status, ['Received', 'Delayed', 'Cancelled'])) {
        $conn->begin_transaction();
        try {
            // Update the delivery row status
            $update_delivery = $conn->prepare("UPDATE deliveries SET status = ?, received_date = NOW(), received_by = ? WHERE delivery_id = ?");
            $update_delivery->bind_param("sii", $new_status, $current_manager_id, $delivery_id);
            $update_delivery->execute();

            if ($new_status === 'Received') {
                // Fixed: Link the query directly to the product specified on the Purchase Order 
                $get_po_item = $conn->prepare("
                    SELECT po.Box_unit, po.product_id 
                    FROM deliveries d
                    INNER JOIN purchase_orders po ON d.purchase_order_id = po.purchase_order_id
                    WHERE d.delivery_id = ? LIMIT 1
                ");
                $get_po_item->bind_param("i", $delivery_id);
                $get_po_item->execute();
                $po_data = $get_po_item->get_result()->fetch_assoc();

                if ($po_data && isset($po_data['product_id'])) {
                    $pid = $po_data['product_id'];
                    $qty_to_add = intval($po_data['Box_unit']);

                    $update_stock = $conn->prepare("UPDATE inventory SET current_stock = current_stock + ? WHERE product_id = ?");
                    $update_stock->bind_param("ii", $qty_to_add, $pid);
                    $update_stock->execute();
                }
                
                $update_po = $conn->prepare("
                    UPDATE purchase_orders po
                    INNER JOIN deliveries d ON po.purchase_order_id = d.purchase_order_id
                    SET po.status = 'Completed' 
                    WHERE d.delivery_id = ?
                ");
                $update_po->bind_param("i", $delivery_id);
                $update_po->execute();
            }

            $conn->commit();
            echo "<div style='padding:15px; margin-bottom:20px; background:#d4edda; color:#155724; border-radius:4px;'>v Delivery state finalized, storage indexes adjusted.</div>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<div style='padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:4px;'>x Transaction failed: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

$deliveries_query = "
    SELECT d.delivery_id, d.received_date, d.status as delivery_status,
           po.purchase_order_id, po.expected_delivery_date, po.total_amount, po.Box_unit,
           s.supplier_name,
           u.username as receiver_name
    FROM deliveries d
    INNER JOIN purchase_orders po ON d.purchase_order_id = po.purchase_order_id
    INNER JOIN suppliers s ON po.supplier_id = s.supplier_id
    LEFT JOIN users u ON d.received_by = u.user_id
    ORDER BY po.expected_delivery_date DESC
";
$deliveries_result = $conn->query($deliveries_query);
?>

<div class="deliveries-module" style="background: white; padding: 20px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <div style="display: flex; justify-content: end; align-items: center; margin-bottom: 20px;">
        <span style="background: #e9ecef; padding: 5px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: bold; color: #495057;">
            Total Active Shipments: <?= $deliveries_result->num_rows ?>
        </span>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f1f3f5; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px;">ID</th>
                <th style="padding: 12px;">Supplier</th>
                <th style="padding: 12px;">Expected Arrival</th>
                <th style="padding: 12px;">Cargo Units (Boxes)</th>
                <th style="padding: 12px;">Value</th>
                <th style="padding: 12px;">Receiver Check</th>
                <th style="padding: 12px;">Status</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($deliveries_result->num_rows === 0): ?>
                <tr>
                    <td colspan="8" style="padding: 20px; text-align: center; color: #868e96;">No shipments or transport orders found in logs.</td>
                </tr>
            <?php else: ?>
                <?php while ($delivery = $deliveries_result->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #e9ecef;">
                        <td style="padding: 12px; font-weight: bold;">#DLV-<?= $delivery['delivery_id'] ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($delivery['supplier_name']) ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($delivery['expected_delivery_date']) ?></td>
                        <td style="padding: 12px;"><?= htmlspecialchars($delivery['Box_unit'] ?? '0') ?> bxs</td>
                        <td style="padding: 12px; font-weight: bold; color: #2b2b2b;">$<?= number_format($delivery['total_amount'], 2) ?></td>
                        <td style="padding: 12px; color: #666;">
                            <?= $delivery['receiver_name'] ? htmlspecialchars($delivery['receiver_name']) : '<em style="color:#adb5bd;">Unassigned</em>' ?>
                        </td>
                        <td style="padding: 12px;">
                            <?php 
                            $status = $delivery['delivery_status'];
                            $badge_color = '#6c757d'; 
                            if ($status === 'Received') $badge_color = '#28a745';
                            if ($status === 'Pending') $badge_color = '#fd7e14';
                            if ($status === 'Delayed') $badge_color = '#dc3545';
                            if ($status === 'Cancelled') $badge_color = '#6c757d';
                            ?>
                            <span style="background: <?= $badge_color ?>; color: white; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;">
                                <?= htmlspecialchars($status) ?>
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <?php if ($status === 'Pending' || $status === 'Delayed'): ?>
                                <form method="POST" style="display: inline-block; margin: 0;">
                                    <input type="hidden" name="delivery_id" value="<?= $delivery['delivery_id'] ?>">
                                    <select name="status" required style="padding: 4px; font-size: 0.8rem; border-radius: 4px; border: 1px solid #ced4da;">
                                        <option value="">-- Change --</option>
                                        <option value="Received">Received</option>
                                        <option value="Delayed">Delayed</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                    <button type="submit" name="update_delivery_status" style="background: #007bff; color: white; border: none; padding: 4px 8px; font-size: 0.8rem; border-radius: 4px; cursor: pointer; margin-left: 2px;">
                                        Apply
                                    </button>
                                </form>
                            <?php else: ?>
                                <small style="color: #9c9c9c; font-style: italic;">Locked (<?= htmlspecialchars($delivery['received_date'] ? date('M d, H:i', strtotime($delivery['received_date'])) : '') ?>)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
