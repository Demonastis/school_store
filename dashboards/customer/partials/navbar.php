<navbar>
    <div class="brand">🎒 Campus<span>Supplies</span></div>

    <ul class="nav-links">
        <li><a href="#shop" id="link-shop" class="nav-item">Browse Products</a></li>
        <li><a href="#orders" id="link-orders" class="nav-item">My Orders</a></li>
        <li><a href="#history" id="link-history" class="nav-item">Purchase History</a></li>
    </ul>

    <div class="user-actions">
        <div
            class="cart-icon"
            title="Open cart"
            onclick="document.getElementById('cartDrawerPanel').style.display='block';"
        >
            🛒<div class="cart-count"><?= $cart_total_items ?></div>
        </div>

        <button
            type="button"
            class="profile-trigger"
            title="View profile"
            onclick="document.getElementById('profilePanel').style.display='block';"
            style="display:flex; align-items:center; gap:8px; border:0; background:none; cursor:pointer; color:inherit; padding:0;"
        >
            <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg'): ?>
                <img
                    src="<?= htmlspecialchars($user['profile_picture']) ?>"
                    alt="Profile"
                    style="width:32px; height:32px; border-radius:50%; object-fit:cover;"
                >
            <?php else: ?>
                <span
                    style="width:32px; height:32px; border-radius:50%; background:#e2e8f0; display:inline-flex; align-items:center; justify-content:center;"
                >
                    👤
                </span>
            <?php endif; ?>

            <span style="font-weight: 600; font-size: 0.9rem;">
                <?= htmlspecialchars($user_name) ?>
            </span>
        </button>
    </div>
</navbar>
