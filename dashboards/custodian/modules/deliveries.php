<?php
// dashboards/custodian/modules/deliveries.php
// Inherits standard database connection $conn from parent custodian dashboard shell

// 1. Process Delivery Intake Request Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log_delivery_action'])) {
    $po_id = intval($_POST['purchase_order_id']);
    $delivery_status = $_POST['delivery_status'];
    $custodian_id = $_SESSION['user_id'] ?? 1; 

    if (in_array($delivery_status, ['Received', 'Delayed', 'Cancelled'])) {
        // Begin ACID transaction block to guarantee storage consistency
        $conn->begin_transaction();
        try {
            // Update parent purchase order lifecycle tracker status
            $po_final_status = ($delivery_status === 'Received') ? 'Completed' : $delivery_status;
            $update_po = $conn->prepare("UPDATE purchase_orders SET status = ? WHERE purchase_order_id = ?");
            $update_po->bind_param("si", $po_final_status, $po_id);
            $update_po->execute();

            // Insert tracking entry logs straight into the deliveries table pipeline
            $received_timestamp = ($delivery_status === 'Received') ? date('Y-m-d H:i:s') : null;
            $insert_dlv = $conn->prepare("INSERT INTO deliveries (purchase_order_id, received_date, received_by, status) VALUES (?, ?, ?, ?)");
            $insert_dlv->bind_param("isis", $po_id, $received_timestamp, $custodian_id, $delivery_status);
            $insert_dlv->execute();

            // If shipment successfully arrives, calculate total item quantities and increment inventory
            if ($delivery_status === 'Received') {
                $get_po_details = $conn->prepare("
                    SELECT po.Box_unit as boxes_bought, sp.qty_per_unit, sp.product_id 
                    FROM purchase_orders po
                    INNER JOIN supplier_products sp ON po.supplier_id = sp.supplier_id
                    WHERE po.purchase_order_id = ? LIMIT 1
                ");
                $get_po_details->bind_param("i", $po_id);
                $get_po_details->execute();
                $po_data = $get_po_details->get_result()->fetch_assoc();

                if ($po_data) {
                    $pid = $po_data['product_id'];
                    $boxes = intval($po_data['boxes_bought']);
                    $items_per_box = intval($po_data['qty_per_unit']);
                    
                    // FIXED MATHEMATICAL FORMULA: Total Units = Boxes x Qty Per Unit Box
                    $total_individual_items = $boxes * $items_per_box;

                    // Update physical index totals inside inventory table database
                    $update_stock = $conn->prepare("UPDATE inventory SET current_stock = current_stock + ? WHERE product_id = ?");
                    $update_stock->bind_param("ii", $total_individual_items, $pid);
                    $update_stock->execute();
                }
            }

            $conn->commit();
            echo "<div style='padding:15px; margin-bottom:20px; background:#d4edda; color:#155724; border-radius:4px; font-weight:500;'>✓ Delivery status registered and inventory levels adjusted successfully.</div>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<div style='padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:4px; font-weight:500;'>✕ Processing failure. Changes rolled back: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// 2. Fetch all Pending/Delayed purchase orders with Box Count AND Internal Pack Quantities
$weekly_pos_query = "
    SELECT po.purchase_order_id, po.order_date, po.expected_delivery_date, po.Box_unit as boxes_ordered, po.status as po_status,
           s.supplier_name,
           p.product_name,
           sp.qty_per_unit as items_per_box
    FROM purchase_orders po
    INNER JOIN suppliers s ON po.supplier_id = s.supplier_id
    -- Link directly through the supplier products matrix to fetch packaging breakdowns
    INNER JOIN supplier_products sp ON po.supplier_id = sp.supplier_id
    INNER JOIN products p ON sp.product_id = p.product_id
    WHERE po.status IN ('Pending', 'Delayed')
    ORDER BY po.expected_delivery_date ASC
";
$weekly_pos_result = $conn->query($weekly_pos_query);
?>

<div class="deliveries-module" style="background: white; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600;">Weekly Logistical Arrivals & Inbound Verification</h3>
        <span style="background: #f1f3f5; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; color: #495057;">
            ⏱️ Expected Inbound POs: <?= $weekly_pos_result->num_rows ?>
        </span>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px;">PO ID</th>
                <th style="padding: 12px;">Vendor / Supplier</th>
                <th style="padding: 12px;">Item Name</th>
                <th style="padding: 12px; text-align: center;">Cargo Volume</th>
                <th style="padding: 12px; text-align: center; background: #eef2f7; color: #0056b3;">Expected Loose Items</th>
                <th style="padding: 12px;">Delivery ETA Cutoff</th>
                <th style="padding: 12px; text-align: right;">Actions Intake Logging</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($weekly_pos_result->num_rows === 0): ?>
                <tr>
                    <td colspan="7" style="padding: 24px; text-align: center; color: #868e96; font-style: italic;">
                        No pending purchase orders found in logs scheduled for this calendar week.
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($po = $weekly_pos_result->fetch_assoc()): ?>
                    <?php 
                        $boxes = intval($po['boxes_ordered']);
                        $per_box = intval($po['items_per_box']);
                        // Dynamic breakdown calculation for custodian visibility
                        $total_pieces = $boxes * $per_box;
                    ?>
                    <tr style="border-bottom: 1px solid #e9ecef; transition: background 0.2s;" onmouseover="this.style.background='#fdfdfd'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 12px; font-weight: bold;">#PO-<?= $po['purchase_order_id'] ?></td>
                        <td style="padding: 12px; font-weight: 500;"><?= htmlspecialchars($po['supplier_name']) ?></td>
                        <td style="padding: 12px; color: #495057;"><?= htmlspecialchars($po['product_name']) ?></td>
                        
                        <!-- Physical Box Breakdown Display -->
                        <td style="padding: 12px; text-align: center; color: #6c757d;">
                            <strong><?= $boxes ?></strong> boxes <br>
                            <span style="font-size: 0.75rem; color: #999;">({$per_box}/box)</span>
                        </td>
                        
                        <!-- NEW REHASHED COLUMN: Exact pieces count within the arriving boxes -->
                        <td style="padding: 12px; text-align: center; background: #f4f7fa; font-weight: bold; font-size: 1rem; color: #0d6efd;">
                            <?= number_format($total_pieces) ?> pcs
                        </td>
                        
                        <td style="padding: 12px; color: #dc3545; font-weight: 500;"><?= date('M d, Y', strtotime($po['expected_delivery_date'])) ?></td>
                        <td style="padding: 12px; text-align: right;">
                            <form method="POST" style="display: inline-flex; gap: 6px; margin: 0; align-items: center;">
                                <input type="hidden" name="purchase_order_id" value="<?= $po['purchase_order_id'] ?>">
                                <select name="delivery_status" required style="padding: 6px; font-size: 0.85rem; border-radius: 4px; border: 1px solid #ced4da; background: #fff;">
                                    <option value="">-- Set Shipment State --</option>
                                    <option value="Received">🚚 Received (Add <?= $total_pieces ?> Pcs)</option>
                                    <option value="Delayed">⚠️ Delayed Shipment</option>
                                    <option value="Cancelled">❌ Cancelled Order</option>
                                </select>
                                <button type="submit" name="log_delivery_action" style="background: #198754; color: white; border: none; padding: 6px 12px; font-size: 0.85rem; font-weight: bold; border-radius: 4px; cursor: pointer;">
                                    Apply
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
