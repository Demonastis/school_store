<?php 
require_once '../../config/db.php';

$total_products_query = "SELECT a.log_id, u.first_name, u.last_name, u.role, a.action, a.module, a.description, a.created_at FROM audit_logs a LEFT JOIN users u ON a.user_id = u.user_id";
$total_products_result = $conn->query($total_products_query);
?>



<div class="table-container" style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px;">Name</th>
                <th style="padding: 12px;">Category</th>
                <th style="padding: 12px;">Price</th>
                <th style="padding: 12px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($total_products_result->num_rows === 0): ?>
                <tr>
                    <td colspan="6" style="padding: 20px; text-align: center; color: #868e96;">No active transaction orders require packing fulfillment right now.</td>
                </tr>
            <?php else: ?>
                <?php while ($order = $total_products_result->fetch_assoc()): ?>
                    <!-- <tr style="border-bottom: 1px solid #e9ecef;"> -->
                        <!-- <td style="padding: 12px; font-weight: bold;"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></td>
                        <td style="padding: 12px; font-weight: bold;"><?= $order['description'] ?></td>
                        <td style="padding: 12px; font-weight: bold;"><?= $order['module'] ?></td>
                        <td style="padding: 12px; font-weight: bold;"><?= $order['first_name'] . " " . $order['last_name'] ?></td>
                        <td style="padding: 12px; font-weight: bold;"><?= $order['role'] ?></td> -->
                        <!-- <td style="padding: 12px; color: #555;"><?= date('M d, Y H:i', strtotime($order['transaction_date'])) ?></td> -->

                    <!-- </tr> -->
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>