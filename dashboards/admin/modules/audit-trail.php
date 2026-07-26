<?php 
require_once '../../config/db.php';

$total_products_query = "SELECT a.log_id, u.first_name, u.last_name, u.role, a.action, a.module, a.description, a.created_at FROM audit_logs a LEFT JOIN users u ON a.user_id = u.user_id ORDER BY a.created_at DESC";
$total_products_result = $conn->query($total_products_query);
?>



<div class="table-container" style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <table class="table table-striped" style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 10px;">Name</th>
                <th style="padding: 10px;">Role</th>
                <th style="padding: 10px;">Module</th>
                <th style="padding: 10px;">Description</th>
                <th style="padding: 10px;">Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($total_products_result->num_rows === 0): ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #868e96;">No active transaction orders require packing fulfillment right now.</td>
                </tr>
            <?php else: ?>
                <?php while ($order = $total_products_result->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #e9ecef;">
                        <td style="padding: 12px;"><?= $order['first_name'] . " " . $order['last_name'] ?></td>
                        <td style="padding: 12px; font-weight: bold;"><?= $order['role'] ?></td>
                        <td style="padding: 12px;"><?= $order['module'] ?></td>
                        <td style="padding: 12px; width: 30%;"><?= $order['description'] ?></td>
                        <td style="padding: 12px;"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>

                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>