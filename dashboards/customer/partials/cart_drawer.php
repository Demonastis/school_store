<!-- RIGHT PANEL DRAWER MODAL SLIDE COMPONENT FOR SHOPPING CART -->
<div
    id="cartDrawerPanel"
    style="display: none; position: fixed; top: 0; right: 0; width: 360px; height: 100%; background: white; box-shadow: -10px 0 25px rgba(0,0,0,0.15); z-index: 99999; padding: 25px; box-sizing: border-box; overflow-y: auto;"
>
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px;">
        <h3 style="margin:0; font-size: 1.1rem; font-weight: 600;">
            Your Cart Basket
        </h3>

        <button
            onclick="document.getElementById('cartDrawerPanel').style.display='none';"
            style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #94a3b8;"
        >
            ✕
        </button>
    </div>

    <?php if (empty($cart_items)): ?>
        <p style="text-align: center; color: #64748b; margin-top: 40px; font-style: italic; font-size: 0.9rem;">
            Your shopping cart basket is completely empty.
        </p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 25px;">
            <?php foreach ($cart_items as $item): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding-bottom: 10px; border-bottom: 1px dashed #f1f5f9;">
                    <div style="max-width: 70%;">
                        <strong style="color: #1e293b; display: block;">
                            <?= htmlspecialchars($item['product_name']) ?>
                        </strong>

                        <span style="color: #64748b; font-size: 0.75rem;">
                            ₱<?= number_format($item['price'], 2) ?>
                            × <?= $item['qty'] ?>
                        </span>
                    </div>

                    <div style="text-align:right;">
                        <strong style="color: #0f172a; display:block;">
                            ₱<?= number_format($item['subtotal'], 2) ?>
                        </strong>

                        <form method="POST" style="margin-top:6px;">
                            <input
                                type="hidden"
                                name="product_id"
                                value="<?= $item['product_id'] ?>"
                            >
                            <button
                                type="submit"
                                name="remove_from_cart_action"
                                style="background:none; border:0; color:#dc2626; cursor:pointer; font-size:.75rem; padding:2px 0;"
                            >
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="border-top: 2px solid #f1f5f9; padding-top: 15px; margin-bottom: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <span style="color: #64748b; font-size: 0.9rem;">
                    Estimated Bill:
                </span>

                <strong style="font-size: 1.3rem; color: #16a34a;">
                    ₱<?= number_format($running_cart_total, 2) ?>
                </strong>
            </div>

            <form method="POST">
                <button
                    type="submit"
                    name="checkout_cart_action"
                    style="width: 100%; background: #16a34a; color: white; border: none; padding: 12px; font-weight: bold; border-radius: 6px; cursor: pointer; font-size: 0.9rem; transition: background 0.1s;"
                >
                    🚀 Dispatch Order Request
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>
