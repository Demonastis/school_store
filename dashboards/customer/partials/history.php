<!-- SECTION C: HISTORICAL ARCHIVE ORDER LOGS COMPONENT -->
<div id="history" class="portal-section">
    <div class="section-title">
        <h3>Completed Purchase History</h3>
    </div>

    <div class="history-list" style="max-width: 700px;">
        <?php if ($history_result->num_rows === 0): ?>
            <p style="color: #64748b; font-style: italic; font-size: 0.85rem;">
                No past transactions found in archival historical accounts.
            </p>
        <?php else: ?>
            <?php while ($hist = $history_result->fetch_assoc()): ?>
                <div class="history-card">
                    <div class="details">
                        <strong style="color: #1e293b;">
                            Transaction #TXN-<?= $hist['transaction_id'] ?>
                        </strong>

                        <span style="font-size: 0.75rem; color: #64748b;">
                            Claimed on:
                            <?= date('M d, Y H:i', strtotime($hist['transaction_date'])) ?>
                        </span>

                        <span style="font-size: 0.8rem; color: #475569; font-weight: 500;">
                            <?= intval($hist['total_items']) ?> item(s) total package balance
                        </span>
                    </div>

                    <div class="amount">
                        ₱<?= number_format($hist['total_amount'], 2) ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
