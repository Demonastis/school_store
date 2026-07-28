<?php
// dashboards/custodian/modules/stock-count.php
// Inherits standard database connection $conn from parent custodian dashboard shell

// 1. Process Physical Audit Count Adjustment Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adjust_stock_action'])) {
    $inventory_id = intval($_POST['inventory_id']);
    $product_name = $_POST['product_name'];
    $current_stock = intval($_POST['current_stock']);
    $physical_count = intval($_POST['physical_count']);
    $reason = trim($_POST['adjustment_reason']);
    $custodian_name = $_SESSION['username'] ?? 'J. Santos'; // Fallback context tracking

    // Calculate variance delta string metrics
    $variance = $physical_count - $current_stock;
    $variance_formatted = ($variance >= 0) ? "+".$variance : $variance;

    if ($physical_count >= 0) {
        $conn->begin_transaction();
        try {
            // Update the real physical stock matrix in inventory
            $update_stmt = $conn->prepare("UPDATE inventory SET current_stock = ? WHERE inventory_id = ?");
            $update_stmt->bind_param("ii", $physical_count, $inventory_id);
            $update_stmt->execute();

            // Document the correction within the central audit logs database for transparency
            $audit_desc = "Manual inventory reconciliation for item '{$product_name}'. Count adjusted from {$current_stock} to {$physical_count} (Variance: {$variance_formatted} units). Reason: {$reason}.";
            $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (4, 'Inventory Reconciliation', 'Warehouse Module', ?)");
            $audit_stmt->bind_param("s", $audit_desc);
            $audit_stmt->execute();

            $conn->commit();
            echo "<div style='padding:15px; margin-bottom:20px; background:#d1e7dd; color:#0f5132; border-radius:4px; font-weight:500;'>✓ Inventory audit complete. Physical balance modified and variance logged.</div>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "<div style='padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:4px; font-weight:500;'>✕ Database adjustment rolled back: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// 2. Query Current Product Stock vs Safety Threshold Matrix
$stock_query = "
    SELECT i.inventory_id, i.current_stock, i.minimum_stock, i.last_updated,
           p.product_id, p.product_name, p.category, p.size
    FROM inventory i
    INNER JOIN products p ON i.product_id = p.product_id
    ORDER BY p.category ASC, p.product_name ASC
";
$stock_result = $conn->query($stock_query);
?>

<div class="stock-count-module" style="background: white; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600;">Physical Inventory Audit & Reconciliation</h3>
        <span style="background: #f1f3f5; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; color: #495057;">
            📦 Unique SKUs in Stock: <?= $stock_result->num_rows ?>
        </span>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px;">Product Details</th>
                <th style="padding: 12px;">Category</th>
                <th style="padding: 12px;">Size</th>
                <th style="padding: 12px; text-align: center;">Minimum Stock</th>
                <th style="padding: 12px; text-align: center;">System Book Stock</th>
                <th style="padding: 12px; text-align: center;">Status Health</th>
                <th style="padding: 12px; text-align: right;">In-Line Reconciliation Audit</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($stock_result->num_rows === 0): ?>
                <tr>
                    <td colspan="7" style="padding: 24px; text-align: center; color: #868e96; font-style: italic;">No active product inventory records resolved in catalog.</td>
                </tr>
            <?php else: ?>
                <?php while ($item = $stock_result->fetch_assoc()): ?>
                    <?php 
                        $is_low = ($item['current_stock'] <= $item['minimum_stock']);
                        $row_bg = $is_low ? '#fff8f8' : 'transparent';
                    ?>
                    <tr style="border-bottom: 1px solid #e9ecef; background: <?= $row_bg ?>; transition: background 0.2s;" onmouseover="this.style.background='#f1f3f5'" onmouseout="this.style.background='<?= $row_bg ?>'">
                        <td style="padding: 12px; font-weight: 500;">
                            <?= htmlspecialchars($item['product_name']) ?>
                            <span style="display:block; font-size:0.75rem; color:#868e96;">ID: #PROD-<?= $item['product_id'] ?></span>
                        </td>
                        <td style="padding: 12px; color: #495057;"><?= htmlspecialchars($item['category']) ?></td>
                        <td style="padding: 12px; color: #6c757d;"><?= htmlspecialchars($item['size'] ?: 'N/A') ?></td>
                        <td style="padding: 12px; text-align: center; font-weight: bold; color: #6c757d;"><?= $item['minimum_stock'] ?></td>
                        <td style="padding: 12px; text-align: center; font-weight: bold; font-size: 1rem; color: <?= $is_low ? '#dc3545' : '#198754' ?>;">
                            <?= $item['current_stock'] ?>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <?php if ($is_low): ?>
                                <span style="background: #f8d7da; color: #721c24; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; display: inline-block;">⚠️ Low Stock</span>
                            <?php else: ?>
                                <span style="background: #d1e7dd; color: #0f5132; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; display: inline-block;">✓ Healthy</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <!-- In-line Quick Stock Balancing Form Block -->
                            <form method="POST" style="display: inline-flex; gap: 6px; margin: 0; align-items: center; justify-content: flex-end;">
                                <input type="hidden" name="inventory_id" value="<?= $item['inventory_id'] ?>">
                                <input type="hidden" name="product_name" value="<?= htmlspecialchars($item['product_name']) ?>">
                                <input type="hidden" name="current_stock" value="<?= $item['current_stock'] ?>">
                                
                                <input type="number" name="physical_count" min="0" placeholder="Actual" required style="width: 75px; padding: 5px; border-radius: 4px; border: 1px solid #ced4da; text-align: center; font-weight: bold;">
                                
                                <select name="adjustment_reason" required style="padding: 5px; font-size: 0.8rem; border-radius: 4px; border: 1px solid #ced4da; background: #fff; width: 140px;">
                                    <option value="">-- Reason --</option>
                                    <option value="Physical Count Audit">Reconciliation Check</option>
                                    <option value="Damaged Goods Discard">Damaged / Expired</option>
                                    <option value="Incorrect Initial Entry">Correction Fix</option>
                                    <option value="Store Theft / Loss">Unaccounted Loss</option>
                                </select>
                                
                                <button type="submit" name="adjust_stock_action" style="background: #0d6efd; color: white; border: none; padding: 6px 10px; font-size: 0.8rem; font-weight: bold; border-radius: 4px; cursor: pointer; transition: background 0.1s;">
                                    Sync
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
