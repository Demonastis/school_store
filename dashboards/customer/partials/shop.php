<!-- SECTION A: SHOP CATALOG COMPONENT -->
<div id="shop" class="portal-section active-route">
    <div class="section-title">
        <h3>Available Products</h3>
    </div>

    <div class="products-grid">
        <?php if ($catalog_result->num_rows === 0): ?>
            <p style="grid-column: 1/-1; color: #64748b; font-style: italic;">
                No store products matching available inventory requirements found.
            </p>
        <?php else: ?>
            <?php while ($prod = $catalog_result->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-img-mock">
                        <?php
                        $cat = strtolower($prod['category']);

                        if (
                            strpos($cat, 'book') !== false ||
                            strpos($cat, 'paper') !== false
                        ) {
                            echo "📓";
                        } elseif (
                            strpos($cat, 'pen') !== false ||
                            strpos($cat, 'pencil') !== false
                        ) {
                            echo "✏️";
                        } else {
                            echo "📐";
                        }
                        ?>
                    </div>

                    <div class="product-title">
                        <?= htmlspecialchars($prod['product_name']) ?>
                    </div>

                    <div class="product-meta">
                        <?= htmlspecialchars($prod['size'] ?: 'Standard Pack') ?>
                        • Stock: <strong><?= $prod['current_stock'] ?></strong>
                    </div>

                    <div class="product-footer">
                        <span class="price">
                            ₱<?= number_format($prod['price'], 2) ?>
                        </span>

                        <form method="POST" style="margin:0;">
                            <input
                                type="hidden"
                                name="product_id"
                                value="<?= $prod['product_id'] ?>"
                            >
                            <button
                                type="submit"
                                name="add_to_cart_action"
                                class="btn-sm"
                            >
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
