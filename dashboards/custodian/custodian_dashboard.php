<?php
require_once '../../config/db.php';
?>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custodian Dashboard - School Supplies Management System</title>
    
    <link rel="stylesheet" href="../../css/style.css"/>
</head>
<body>

    <!-- Sidebar Menu Component -->
    <aside>
        <div class="brand">
            EduManage
            <span>Custodian Panel</span>
        </div>
        <ul class="menu">
            <li class="menu-item active">
                <a href="custodian_dashboard.php?page=dashboard">📦 <span>Order Fulfillment</span></a>
            </li>
            <li class="menu-item">
                <a href="custodian_dashboard.php?page=deliveries">🚚 <span>Inbound Deliveries</span></a>
            </li>
            <li class="menu-item">
                <a href="custodian_dashboard.php?page=stock-count">🔄 <span>Stock Counts</span></a>
            </li>
            <li class="menu-item">
                <a href="custodian_dashboard.php?page=labels">🏷️ <span>Print Labels</span></a>
            </li>
        </ul>
    </aside>

    <!-- Main Section App Window -->
    <main>
        <header>
            <h2>Order Fulfillment Center</h2>
            <div class="user-profile">
                <div class="user-info" style="text-align: right;">
                    <h4 style="font-size: 0.9rem;">J. Santos</h4>
                    <p style="font-size: 0.75rem; color: var(--text-muted);">Warehouse Custodian</p>
                </div>
                <div class="avatar">JS</div>
            </div>
        </header>

        <div class="content">
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

            
