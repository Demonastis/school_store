<div class="table-container" style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px;">Timestamp</th>
                <th style="padding: 12px;">User Role</th>
                <th style="padding: 12px;">Action</th>
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
                        <td style="padding: 12px;">
                            <?php if ($order['status'] === 'Pending'): ?>
                                <a class="d-flex gap-1 justify-content-center p-2 " href="process_fulfillment.php?action=pack&id=<?= $order['transaction_id'] ?>" style="background: #1A6B54; color: white; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: bold;">
                                    <?php include ("../../icons/layer-small-icon.html") ?> Activate
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