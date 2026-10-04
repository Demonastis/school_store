<?php if (isset($_GET['status']) && $_GET['status'] === 'payment_returned'): ?>
    <div style="padding:15px; margin-bottom:20px; background:#d1e7dd; color:#0f5132; border-radius:6px; font-weight:500;">
        ✓ You returned from PayMongo Checkout. Payment confirmation is handled securely by the PayMongo webhook.
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'payment_cancelled'): ?>
    <div style="padding:15px; margin-bottom:20px; background:#fff3cd; color:#664d03; border-radius:6px; font-weight:500;">
        Payment was not completed. Your order remains pending and can be reviewed in My Orders.
    </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'item_removed'): ?>
    <div style="padding:15px; margin-bottom:20px; background:#e2e8f0; color:#334155; border-radius:6px; font-weight:500;">
        ✓ Item removed from your cart.
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div style="padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:6px; font-weight:500;">
        ✕ Transaction Alert: <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>
