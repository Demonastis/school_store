<?php
require_once '../../config/db.php';

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
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - School Supplies Management System</title>
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
    </style>
</head>

<body>

    <!-- Sidebar Menu Component -->
    <aside>
        <div class="brand">
            EduManage
            <span>Manager Workspace</span>
        </div>
        <ul class="menu">
            <li class="menu-item">
                <a href="manager_dashboard.php?page=dashboard">📁 <span>Dashboard</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=products">📦 <span>Products Inventory</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=orders">📝 <span>Purchase Orders</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=purchase">🚀 <span>Create PO</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=deliveries">🚚 <span>Monitor Deliveries</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=reports">📈 <span>Replenishment Reports</span></a>
            </li>
            
        </ul>

    </aside>

    <!-- Main Section App Window -->
    <main>
        <header>
            <h2>Overview Dashboard</h2>
            <div class="user-profile">
                <div class="user-info" style="text-align: right;">
                    <h4 style="font-size: 0.9rem;">M. Reynolds</h4>
                    <p style="font-size: 0.75rem; color: var(--text-muted);">Inventory Manager</p>
                </div>
                <div class="avatar">MR</div>
            </div>
        </header>

        <div class="content">
            <?php
            // Get the page from the URL, default to 'dashboard'
            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

            // Create the path to the module file
            $module_path = "modules/" . $page . ".php";

            // Security check: only include if file exists
            if (file_exists($module_path)) {
                include($module_path);
            } else {
                echo "<h3>404: Module not found</h3>";
            }
            ?>



            <script src="../../js/getSupplier.js"></script>

            <!-- monitor deliveries-->