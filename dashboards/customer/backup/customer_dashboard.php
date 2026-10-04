<?php
session_start();
require_once '../../config/db.php';

// Authentication Check: Fallback to mock session data if login middleware is not set up
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 5; // Sample Customer/Student identifier
    $_SESSION['first_name'] = "Alice";
    $_SESSION['last_name'] = "Santos";
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['first_name'] . " " . $_SESSION['last_name'];

// Initialize core storage arrays for your shopping cart pipeline
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// 1. Core Post Request Actions Processing Router
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Action A: Add product entry selection to user session cart array
    if (isset($_POST['add_to_cart_action'])) {
        $pid = intval($_POST['product_id']);

        // Confirm item has matching physical availability thresholds before adding
        $stock_chk = $conn->prepare("SELECT current_stock FROM inventory WHERE product_id = ? LIMIT 1");
        $stock_chk->bind_param("i", $pid);
        $stock_chk->execute();
        $avail_stock = $stock_chk->get_result()->fetch_assoc()['current_stock'] ?? 0;

        if ($avail_stock > 0) {
            if (isset($_SESSION['cart'][$pid])) {
                if ($_SESSION['cart'][$pid] < $avail_stock) {
                    $_SESSION['cart'][$pid]++;
                }
            } else {
                $_SESSION['cart'][$pid] = 1;
            }
            header("Location: customer_dashboard.php?status=item_added#shop");
            exit();
        }
    }

    // Action B: Complete checkout generation loops using database transactions
    if (isset($_POST['checkout_cart_action']) && !empty($_SESSION['cart'])) {
        $conn->begin_transaction();
        try {
            $total_bill = 0.00;
            $order_items_buffer = [];

            // Calculate total cost and verify stock levels across items
            foreach ($_SESSION['cart'] as $pid => $qty) {
                $item_stmt = $conn->prepare("SELECT p.price, p.product_name, i.current_stock FROM products p INNER JOIN inventory i ON p.product_id = i.product_id WHERE p.product_id = ? LIMIT 1");
                $item_stmt->bind_param("i", $pid);
                $item_stmt->execute();
                $item_meta = $item_stmt->get_result()->fetch_assoc();

                if (!$item_meta || $item_meta['current_stock'] < $qty) {
                    throw new Exception("Checkout halted: '{$item_meta['product_name']}' does not have enough remaining stock.");
                }

                $subtotal = floatval($item_meta['price']) * $qty;
                $total_bill += $subtotal;

                $order_items_buffer[] = [
                    'product_id' => $pid,
                    'qty' => $qty,
                    'price' => $item_meta['price']
                ];
            }

            // Insert a new transaction parent record
            $status = "Pending"; // Matches Custodian processing filter layout values
            $tx_stmt = $conn->prepare("INSERT INTO transactions (cashier_id, transaction_date, total_amount, status) VALUES (?, NOW(), ?, ?)");
            $tx_stmt->bind_param("ids", $user_id, $total_bill, $status);
            $tx_stmt->execute();
            $new_tx_id = $tx_stmt->insert_id;

            // Bind individual items to order_items tracking indexes and deduct stock values
            foreach ($order_items_buffer as $row) {
                $oi_stmt = $conn->prepare("INSERT INTO order_items (transaction_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $oi_stmt->bind_param("iiid", $new_tx_id, $row['product_id'], $row['qty'], $row['price']);
                $oi_stmt->execute();

                $stock_stmt = $conn->prepare("UPDATE inventory SET current_stock = current_stock - ? WHERE product_id = ?");
                $stock_stmt->bind_param("ii", $row['qty'], $row['product_id']);
                $stock_stmt->execute();
            }

            // Document the purchase order within system audit logs
            $audit_desc = "Customer {$user_name} placed checkout request order #TXN-{$new_tx_id} valued at ₱" . number_format($total_bill, 2) . ".";
            $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (?, 'Place Customer Order', 'Storefront POS Module', ?)");
            $audit_stmt->bind_param("is", $user_id, $audit_desc);
            $audit_stmt->execute();

            $conn->commit();
            $_SESSION['cart'] = []; // Clear user session variables upon success
            header("Location: customer_dashboard.php?status=checkout_completed#orders");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            header("Location: customer_dashboard.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }
}

// 2. Query Live Available Product Records Matched Against In-Stock Assets
$catalog_query = "
    SELECT p.product_id, p.product_name, p.category, p.price, p.size, i.current_stock 
    FROM products p 
    INNER JOIN inventory i ON p.product_id = i.product_id 
    WHERE p.availability = 'Available' AND i.current_stock > 0
    ORDER BY p.product_name ASC
";
$catalog_result = $conn->query($catalog_query);

// 3. Query Active Live Tracking Inbound Orders Matrix for Current Account
$live_orders_query = "
    SELECT t.transaction_id, t.transaction_date, t.total_amount, t.status,
           (SELECT SUM(quantity) FROM order_items WHERE transaction_id = t.transaction_id) as total_pieces
    FROM transactions t
    WHERE t.cashier_id = ? AND t.status IN ('Pending', 'Ready')
    ORDER BY t.transaction_date DESC
";
$live_orders_stmt = $conn->prepare($live_orders_query);
$live_orders_stmt->bind_param("i", $user_id);
$live_orders_stmt->execute();
$live_orders_result = $live_orders_stmt->get_result();

// 4. Calculate total number of items currently sitting inside cart
$cart_total_items = array_sum($_SESSION['cart']);
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Portal - School Supplies Store</title>
    <link rel="stylesheet" href="../../css/customer.css"/>
</head>

<body>

    <navbar>
        <div class="brand">🎒 Campus<span>Supplies</span></div>
        <ul class="nav-links">
            <li><a href="#shop" id="link-shop" class="nav-item">Browse Products</a></li>
            <li><a href="#orders" id="link-orders" class="nav-item">My Orders</a></li>
            <li><a href="#history" id="link-history" class="nav-item">Purchase History</a></li>
        </ul>
        <div class="user-actions">
            <div class="cart-icon" onclick="document.getElementById('cartDrawerPanel').style.display='block';">
                🛒<div class="cart-count"><?= $cart_total_items ?></div>
            </div>
            <div style="font-weight: 600; font-size: 0.9rem;">Hi, <?= htmlspecialchars($user_name) ?>!</div>
        </div>
    </navbar>

    <main>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'checkout_completed'): ?>
            <div style="padding:15px; margin-bottom:20px; background:#d1e7dd; color:#0f5132; border-radius:6px; font-weight:500;">🎉 Order submitted successfully! Packings can be validated inside live tracking boards.</div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div style="padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:6px; font-weight:500;">✕ Transaction Alert: <?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="welcome-strip">
            <h2>Welcome Back, <?= htmlspecialchars($user_name) ?>!</h2>
            <p>Find and track all your school essentials for the upcoming academic calendar timeline.</p>
        </div>

        <!-- SECTION A: SHOP CATALOG COMPONENT -->
        <div id="shop" class="portal-section active-route">
            <div class="section-title">
                <h3>Available Products</h3>
            </div>
            <div class="products-grid">
                <?php if ($catalog_result->num_rows === 0): ?>
                    <p style="grid-column: 1/-1; color: #64748b; font-style: italic;">No store products matching available inventory requirements found.</p>
                <?php else: ?>
                    <?php while ($prod = $catalog_result->fetch_assoc()): ?>
                        <div class="product-card">
                            <div class="product-img-mock">
                                <?php
                                $cat = strtolower($prod['category']);
                                if (strpos($cat, 'book') !== false || strpos($cat, 'paper') !== false) echo "📓";
                                elseif (strpos($cat, 'pen') !== false || strpos($cat, 'pencil') !== false) echo "✏️";
                                else echo "📐";
                                ?>
                            </div>
                            <div class="product-title"><?= htmlspecialchars($prod['product_name']) ?></div>
                            <div class="product-meta"><?= htmlspecialchars($prod['size'] ?: 'Standard Pack') ?> • Stock: <strong><?= $prod['current_stock'] ?></strong></div>
                            <div class="product-footer">
                                <span class="price">₱<?= number_format($prod['price'], 2) ?></span>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="product_id" value="<?= $prod['product_id'] ?>">
                                    <button type="submit" name="add_to_cart_action" class="btn-sm">Add to Cart</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION B: LIVE ACTIVE ORDERS COMPONENT -->
        <div id="orders" class="portal-section">
            <div class="section-title">
                <h3>Track Live Orders</h3>
            </div>
            <div class="panel" style="max-width: 600px;">
                <?php if ($live_orders_result->num_rows === 0): ?>
                    <p style="margin: 0; color: #64748b; text-align: center; font-style: italic; font-size: 0.85rem;">No active orders require packing fulfillment right now.</p>
                <?php else: ?>
                    <?php while ($ord = $live_orders_result->fetch_assoc()): ?>
                        <?php $is_ready = ($ord['status'] === 'Ready'); ?>
                        <div class="track-card <?= $is_ready ? 'ready' : '' ?>">
                            <div>
                                <strong style="font-size: 0.85rem; color:#0f172a;">#TXN-<?= $ord['transaction_id'] ?></strong>
                                <div style="font-size: 0.75rem; color:#64748b; margin-top:2px;"><?= $ord['total_pieces'] ?> item(s) • ₱<?= number_format($ord['total_amount'], 2) ?></div>
                            </div>
                            <span class="status-badge <?= $is_ready ? 'ready' : 'pending' ?>">
                                <?= $is_ready ? '✓ Ready' : '⏳ Packing' ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION C: HISTORICAL ARCHIVE ORDER LOGS COMPONENT -->
        <div id="history" class="portal-section">
            <div class="section-title">
                <h3>Completed Purchase History</h3>
            </div>
            <div class="history-list" style="max-width: 700px;">
                <?php
                // Fetch fully claimed historical records for this session context account
                $history_query = "
                    SELECT t.transaction_id, t.transaction_date, t.total_amount,
                           (SELECT SUM(quantity) FROM order_items WHERE transaction_id = t.transaction_id) as total_items
                    FROM transactions t
                    WHERE t.cashier_id = ? AND t.status = 'Completed'
                    ORDER BY t.transaction_date DESC
                ";
                $history_stmt = $conn->prepare($history_query);
                $history_stmt->bind_param("i", $user_id);
                $history_stmt->execute();
                $history_result = $history_stmt->get_result();

                if ($history_result->num_rows === 0): ?>
                    <p style="color: #64748b; font-style: italic; font-size: 0.85rem;">No past transactions found in archival historical accounts.</p>
                <?php else: ?>
                    <?php while ($hist = $history_result->fetch_assoc()): ?>
                        <div class="history-card">
                            <div class="details">
                                <strong style="color: #1e293b;">Transaction #TXN-<?= $hist['transaction_id'] ?></strong>
                                <span style="font-size: 0.75rem; color: #64748b;">Claimed on: <?= date('M d, Y H:i', strtotime($hist['transaction_date'])) ?></span>
                                <span style="font-size: 0.8rem; color: #475569; font-weight: 500;"><?= intval($hist['total_items']) ?> item(s) total package balance</span>
                            </div>
                            <div class="amount">₱<?= number_format($hist['total_amount'], 2) ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- RIGHT PANEL DRAWER MODAL SLIDE COMPONENT FOR SHOPPING CART -->
    <div id="cartDrawerPanel" style="display: none; position: fixed; top: 0; right: 0; width: 360px; height: 100%; background: white; box-shadow: -10px 0 25px rgba(0,0,0,0.15); z-index: 99999; padding: 25px; box-sizing: border-box; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 20px;">
            <h3 style="margin:0; font-size: 1.1rem; font-weight: 600;">Your Cart Basket</h3>
            <button onclick="document.getElementById('cartDrawerPanel').style.display='none';" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #94a3b8;">✕</button>
        </div>

        <?php if (empty($_SESSION['cart'])): ?>
            <p style="text-align: center; color: #64748b; margin-top: 40px; font-style: italic; font-size: 0.9rem;">Your shopping cart basket is completely empty.</p>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 25px;">
                <?php
                $running_cart_total = 0.00;
                foreach ($_SESSION['cart'] as $pid => $qty):
                    $crt_stmt = $conn->prepare("SELECT product_name, price FROM products WHERE product_id = ?");
                    $crt_stmt->bind_param("i", $pid);
                    $crt_stmt->execute();
                    $crt_item = $crt_stmt->get_result()->fetch_assoc();
                    $sub = floatval($crt_item['price']) * $qty;
                    $running_cart_total += $sub;
                ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; padding-bottom: 10px; border-bottom: 1px dashed #f1f5f9;">
                        <div style="max-width: 70%;">
                            <strong style="color: #1e293b; display: block;"><?= htmlspecialchars($crt_item['product_name']) ?></strong>
                            <span style="color: #64748b; font-size: 0.75rem;">₱<?= number_format($crt_item['price'], 2) ?> × <?= $qty ?></span>
                        </div>
                        <strong style="color: #0f172a;">₱<?= number_format($sub, 2) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="border-top: 2px solid #f1f5f9; padding-top: 15px; margin-bottom: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <span style="color: #64748b; font-size: 0.9rem;">Estimated Bill:</span>
                    <strong style="font-size: 1.3rem; color: #16a34a;">₱<?= number_format($running_cart_total, 2) ?></strong>
                </div>

                <form method="POST">
                    <button type="submit" name="checkout_cart_action" style="width: 100%; background: #16a34a; color: white; border: none; padding: 12px; font-weight: bold; border-radius: 6px; cursor: pointer; font-size: 0.9rem; transition: background 0.1s;">
                        🚀 Dispatch Order Request
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function handleClientRouting() {
            const hash = window.location.hash || '#shop';

            // 1. Hide all target panel sections from primary display layout grids
            document.querySelectorAll('.portal-section').forEach(section => {
                section.style.display = 'none';
            });

            // 2. Clear active styling states across navbar items links
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.remove('active');
                link.style.color = '#cbd5e1';
            });

            // 3. Uncover the targeted module view panel component container 
            const targetSection = document.querySelector(hash);
            if (targetSection) {
                targetSection.style.display = 'block';
            }

            // 4. Highlight active menu visual highlight anchor context
            const targetLink = document.querySelector(`a[href="${hash}"]`);
            if (targetLink) {
                targetLink.classList.add('active');
                targetLink.style.color = '#ffffff';
            }
        }

        // Bind operational document state visibility context triggers
        window.addEventListener('hashchange', handleClientRouting);
        window.addEventListener('DOMContentLoaded', handleClientRouting);
    </script>
</body>

</html>