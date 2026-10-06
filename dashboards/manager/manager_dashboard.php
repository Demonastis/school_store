<?php
require_once '../../config/db.php';
require '../../auth/auth.php';

// Check if user is logged in
requireLogin();
requireRole(['Manager']); // Only allow users with the 'manager' role

// Fetch Dynamic Aggregated Operational Metrics
// Total Products Calculation
$total_products_query = "SELECT COUNT(*) as total FROM products";
$total_products_result = $conn->query($total_products_query);
$total_products = $total_products_result->fetch_assoc()['total'] ?? 0;

// Low Stock Items Calculation 
$low_stock_query = "SELECT COUNT(*) as total FROM inventory WHERE current_stock <= minimum_stock";
$low_stock_result = $conn->query($low_stock_query);
$low_stock_count = $low_stock_result->fetch_assoc()['total'] ?? 0;

// Pending Purchase Orders Calculation
$pending_deliveries_query = "SELECT COUNT(*) as total FROM purchase_orders WHERE status = 'Pending'";
$pending_deliveries_result = $conn->query($pending_deliveries_query);
$pending_deliveries = $pending_deliveries_result->fetch_assoc()['total'] ?? 0;


$user_id = $_SESSION['user_id'];
$user_query = "
    SELECT user_id, username, role, profile_picture
    FROM users
    WHERE user_id = ?
";

$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();


?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - School Supplies Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../css/style.css" />
    <style>
        /* CSS Switcher Engine Rules */
        .content-section {
            display: none;
        }

        #dashboard:target,
        #products:target,
        #orders:target,
        #deliveries:target,
        #reports:target,
        #purchase:target {
            display: block !important;
        }

        /* Fallback initialization clause */
        .content-section:first-of-type {
            display: block;
        }

        :target~.content-section:first-of-type {
            display: none;
        }

        .avatar {
            width: 42px;
            height: 42px;
            min-width: 42px;
            min-height: 42px;
            border-radius: 50%;
            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: center;

            font-weight: 600;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>

<body>

    <!-- Sidebar Menu Component -->
    <?php include("managersidebar.php");?>

    <!-- Main Section App Window -->
    <main>
        <header>
            <div class="user-profile">
            </div>
        </header>

        <div class="content">
            <?php
            // 1. Define allowed modules explicitly to prevent path traversal / LFI
            $allowed_pages = [
                'dashboard',
                'deliveries',
                'stock-count',
                'labels',
                'products',
                'orders',
                'purchase',
                'reports'
            ];

            // 2. Fetch parameter and fallback safely if it's empty or invalid
            $page = isset($_GET['page']) ? $_GET['page'] : 'users';

            if (in_array($page, $allowed_pages)) {
                $module_path = "modules/" . $page . ".php";
                
                if (file_exists($module_path)) {
                    include($module_path);
                } else {
                    echo "<div class='alert alert-danger'>Module file missing.</div>";
                }
            } else {
                // If an attacker tries '?page=../../etc/passwd', they get caught here
                echo "<div class='alert alert-warning'>Access Denied: Invalid module target.</div>";
            }
            ?>
        </div>



            <script src="../../js/getSupplier.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>