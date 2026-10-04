<?php
require_once __DIR__ . '/bootstrap.php';

require_once __DIR__ . '/actions/cart_actions.php';
require_once __DIR__ . '/actions/checkout_actions.php';

require_once __DIR__ . '/data/catalog.php';
require_once __DIR__ . '/data/orders.php';
require_once __DIR__ . '/data/history.php';
require_once __DIR__ . '/data/cart.php';
?>
<!DOCTYPE html>
<html lang="en">
<?php require __DIR__ . '/partials/head.php'; ?>
<body>
    <?php require __DIR__ . '/partials/navbar.php'; ?>

    <main>
        <?php require __DIR__ . '/partials/alerts.php'; ?>
        <?php require __DIR__ . '/partials/welcome.php'; ?>
        <?php require __DIR__ . '/partials/shop.php'; ?>
        <?php require __DIR__ . '/partials/orders.php'; ?>
        <?php require __DIR__ . '/partials/history.php'; ?>
    </main>

    <?php require __DIR__ . '/partials/cart_drawer.php'; ?>
    <?php require __DIR__ . '/partials/profile_drawer.php'; ?>

    <script src="customer_dashboard.js"></script>
</body>
</html>
