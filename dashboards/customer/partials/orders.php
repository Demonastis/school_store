<!-- SECTION B: LIVE ACTIVE ORDERS COMPONENT -->
<div id="orders" class="portal-section">
    <div class="section-title">
        <h3>Track Live Orders</h3>
    </div>

    <div class="panel" style="max-width: 700px;">
        <?php if ($live_orders_result->num_rows === 0): ?>
            <p style="margin: 0; color: #64748b; text-align: center; font-style: italic; font-size: 0.85rem;">
                No active orders require packing fulfillment right now.
            </p>
        <?php else: ?>
            <?php while ($ord = $live_orders_result->fetch_assoc()): ?>
                <?php $is_ready = ($ord['status'] === 'Ready'); ?>
                <?php $is_paid = ($ord['payment_status'] === 'Paid'); ?>

                <div class="track-card <?= $is_ready ? 'ready' : '' ?>">
                    <div>
                        <strong style="font-size: 0.85rem; color:#0f172a;">
                            #TXN-<?= $ord['transaction_id'] ?>
                        </strong>

                        <div style="font-size: 0.75rem; color:#64748b; margin-top:2px;">
                            <?= $ord['total_pieces'] ?> item(s)
                            • ₱<?= number_format($ord['total_amount'], 2) ?>
                        </div>

                        <div style="font-size: 0.75rem; margin-top:6px; color:<?= $is_paid ? '#15803d' : '#b45309' ?>;">
                            <?= $is_paid ? '✓ Payment confirmed' : '⏳ Payment pending' ?>
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <span class="status-badge <?= $is_ready ? 'ready' : 'pending' ?>">
                            <?= $is_ready ? '✓ Ready' : '⏳ Packing' ?>
                        </span>

                        <?php if (!$is_paid && !empty($ord['paymongo_checkout_url'])): ?>
                            <a
                                href="<?= htmlspecialchars($ord['paymongo_checkout_url']) ?>"
                                style="display:inline-block; margin-top:8px; font-size:.75rem; color:#2563eb; text-decoration:none;"
                            >
                                Continue Payment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
