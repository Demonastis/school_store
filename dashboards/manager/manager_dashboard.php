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
                <a href="manager_dashboard.php?page=dashboard"><?php include ("../../icons/folder-icon.html") ?> <span>Dashboard</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=products"><?php include ("../../icons/box-icon.html") ?> <span>Products Inventory</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=orders"><?php include ("../../icons/file-icon.html") ?> <span>Purchase Orders</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=purchase"><?php include ("../../icons/layer-icon.html") ?> <span>Create PO</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=deliveries"><?php include ("../../icons/truck-icon.html") ?> <span>Monitor Deliveries</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=reports"><?php include ("../../icons/graph-icon.html") ?> <span>Replenishment Reports</span></a>
            </li>
        </ul>
        <div class="btn-group dropend mb-2">
            <a role="button" class="user-profile dropdown-toggle text-white text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar ">MR</div>
                <div class="user-info" style="text-align: left;">
                    <span style="font-size: 0.9rem;">M. Reynolds</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Inventory Manager</span>
                </div>
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../../auth/logout.php">Logout</a></li>
                <!-- Dropdown menu links -->
            </ul>
        </div>
    </aside>

    <!-- Main Section App Window -->
    <main>
        <header>
            <h2><?php 

            $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
            // echo "page is: " . $page; 
            if ($page == 'dashboard') {
                echo 'Overview Dashboard';
            } else if ($page == 'products') {
                echo 'Comprehensive Stock Master List';
            } else if ($page == 'orders') {  
                echo 'Procurement & Purchase Orders';
            } else if ($page == 'purchase') {  
                echo 'Draft New Bulk Procurement Order';
            } else if ($page == 'deliveries') {  
                echo 'Logistics & Delivery Pipeline Status';
            }else {echo ' 404: Module not found ';
            }
            ?> </h2>
            <div class="user-profile">
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
                echo "";
            }
            ?>



            <script src="../../js/getSupplier.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>d
