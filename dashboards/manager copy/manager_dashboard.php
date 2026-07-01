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
                <a href="#dashboard">📁 <span>Dashboard</span></a>
            </li>
            <li class="menu-item">
                <a href="#products">📦 <span>Products Inventory</span></a>
            </li>
            <li class="menu-item">
                <a href="#orders">📝 <span>Purchase Orders</span></a>
            </li>
            <li class="menu-item">
                <a href="#deliveries">🚚 <span>Monitor Deliveries</span></a>
            </li>
            <li class="menu-item">
                <a href="#reports">📈 <span>Replenishment Reports</span></a>
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

            <!-- MODULE 1: DYNAMIC MAIN OVERVIEW DASHBOARD -->
            <div id="dashboard" class="content-section">
                <!-- Strategic Performance Figures -->
                <div class="metrics-grid">
                    <div class="card">
                        <div class="card-icon blue">📦</div>
                        <div class="card-data">
                            <p>Total Products</p>
                            <h3>
                                <?php echo number_format($total_products); ?>
                            </h3>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon orange">⚠️</div>
                        <div class="card-data">
                            <p>Low Stock Items</p>
                            <h3>
                                <?php echo number_format($low_stock_count); ?>
                            </h3>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-icon green">🚚</div>
                        <div class="card-data">
                            <p>Pending Deliveries</p>
                            <h3>
                                <?php echo number_format($pending_deliveries); ?>
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Context Control Elements -->
                <div class="action-bar">
                    <h3 style="font-size: 1.1rem; font-weight: 600;">Critical Inventory Status</h3>
                    <a href="#purchase" class="btn" style="text-decoration: none;">➕ Create Purchase Order</a>
                </div>

                <!-- Dynamic Alert Ledger Sheet -->
                <div class="table-container">
                    <div class="table-header">
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Items requiring immediate restocking
                            attention or pending review</p>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Safety Threshold</th>
                                <th>System Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Select low stock products via an inner join across products and inventory tables
                            $critical_stock_sql = "SELECT p.product_id, p.product_name, p.category, i.current_stock, i.minimum_stock 
                                                   FROM products p 
                                                   INNER JOIN inventory i ON p.product_id = i.product_id 
                                                   WHERE i.current_stock <= i.minimum_stock";
                            $critical_stock_result = $conn->query($critical_stock_sql);

                            if ($critical_stock_result && $critical_stock_result->num_rows > 0) {
                                while ($row = $critical_stock_result->fetch_assoc()) {
                                    $status_badge = ($row['current_stock'] == 0) ? '<span class="badge danger">Out of Stock</span>' : '<span class="badge warning">Low Stock</span>';
                                    echo "<tr>";
                                    echo "<td><strong>#PROD-{$row['product_id']}</strong></td>";
                                    echo "<td>" . htmlspecialchars($row['product_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                                    echo "<td>" . $row['current_stock'] . " pcs</td>";
                                    echo "<td>" . $row['minimum_stock'] . " pcs</td>";
                                    echo "<td>{$status_badge}</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center; color:var(--text-muted);'>All inventory stock metrics are currently optimal.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODULE 2: ALL PRODUCTS INVENTORY -->
            <div id="products" class="content-section">
                <div class="action-bar">
                    <h3>Comprehensive Stock Master List</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Item Description</th>
                                <th>Category</th>
                                <th>Unit Price</th>
                                <th>Units</th>
                                <th>Availability Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Refactored SQL query linking the product catalog to the live stock count tracking indexes
                            $all_products_sql = "SELECT p.product_id, p.product_name, p.category, p.price, i.current_stock, p.availability 
                         FROM products p
                         INNER JOIN inventory i ON p.product_id = i.product_id";

                            $all_products_result = $conn->query($all_products_sql);

                            if ($all_products_result && $all_products_result->num_rows > 0) {
                                while ($prod = $all_products_result->fetch_assoc()) {
                                    // Contextual badge coloring engine based on availability data states
                                    $badge_class = 'success';
                                    if ($prod['availability'] === 'Low Stock') {
                                        $badge_class = 'warning';
                                    }
                                    if ($prod['availability'] === 'Out of Stock') {
                                        $badge_class = 'danger';
                                    }

                                    echo "<tr>";
                                    echo "<td>#{$prod['product_id']}</td>";
                                    echo "<td>" . htmlspecialchars($prod['product_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($prod['category']) . "</td>";
                                    echo "<td>₱" . number_format($prod['price'], 2) . "</td>";
                                    echo "<td>" . number_format($prod['current_stock']) . " pcs</td>";
                                    echo "<td><span class='badge {$badge_class}'>" . htmlspecialchars($prod['availability']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' style='text-align:center;'>No products registered in system database logs.</td></tr>";
                            }
                            ?>
                        </tbody>

                    </table>
                </div>
            </div>

            <div id="orders" class="content-section">
                <div class="action-bar">
                    <h3>Procurement & Purchase Orders</h3>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>PO ID</th>
                                <th>Supplier Vendor</th>
                                <th>Contact Person</th>
                                <th>Vendor Contact No.</th>
                                <th>Expected Delivery</th>
                                <th>Total Cost</th>
                                <th>Order Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $po_sql = "SELECT po.purchase_order_id, s.supplier_name, s.contact_person, s.phone, 
                                  po.expected_delivery_date, po.total_amount, po.status 
                           FROM purchase_orders po
                           INNER JOIN suppliers s ON po.supplier_id = s.supplier_id";

                            $po_result = $conn->query($po_sql);
                            while ($po = $po_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><strong>#PO-{$po['purchase_order_id']}</strong></td>";
                                echo "<td>" . htmlspecialchars($po['supplier_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($po['contact_person']) . "</td>";
                                echo "<td>" . htmlspecialchars($po['phone']) . "</td>";
                                echo "<td>{$po['expected_delivery_date']}</td>";
                                echo "<td>₱" . number_format($po['total_amount'], 2) . "</td>";
                                echo "<td><span class='badge info'>" . htmlspecialchars($po['status']) . "</span></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- MODULE 6: CREATE PURCHASE ORDER FORM INTERFACE -->
            <div id="purchase" class="content-section">
                <div class="action-bar">
                    <h3>Draft New Bulk Procurement Order</h3>
                    <a href="#orders" class="btn secondary" style="text-decoration: none;">📋 View PO Registry</a>
                </div>

                <div class="table-container" style="padding: 30px; background: #ffffff;">
                    <form action="process_po.php" method="POST" style="display: flex; flex-direction: column; gap: 20px; max-width: 600px;">

                        <div>
                            <label style="display:block; font-weight:600; margin-bottom:6px;">Select Target Supplier Vendor:</label>
                            <!-- Supplier Selection -->
                            <select name="supplier_id" id="supplier_select" required ...>
                                <option value="">-- Choose Vendor Registry --</option>
                                <?php
                                $sup_res = $conn->query("SELECT supplier_id, supplier_name FROM suppliers");
                                while ($s = $sup_res->fetch_assoc()) {
                                    echo "<option value='{$s['supplier_id']}'>" . htmlspecialchars($s['supplier_name']) . "</option>";
                                }
                                ?>
                            </select>

                        </div>

                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px;">
                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Product to Order:</label>
                                <!-- Product Selection (Initially Empty) -->
                                <select name="product_id" id="product_select" required ... disabled>
                                    <option value="">-- Select Supplier First --</option>
                                </select>
                            </div>
                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Bulk Order Unit:</label>
                                <div style="display: grid; grid-template-columns: 2fr 1fr; grid-template-rows: 1fr; gap: 2px;">

                                    <input type="number" disabled name="unit" id="unit" min="1" required placeholder="qty per unit" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                                    <select name="bulk_unit" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                                        <option value="Box">Box</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Quantity Ordered:</label>
                                <input type="number" name="quantity" id="quantity" min="1" required placeholder="boxes" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Wholesale Unit Price (₱):</label>
                                <!-- Wholesale Cost (Auto-filled) -->
                                <input type="number" name="wholesale_cost" id="wholesale_cost" step="0.01" required ...>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Order Dispatch Date:</label>
                                <input type="date" name="order_date" required value="<?php echo date('Y-m-d'); ?>" style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                            </div>
                            <div>
                                <label style="display:block; font-weight:600; margin-bottom:6px;">Expected Delivery Date:</label>
                                <input type="date" name="expected_date" id="expected_date" required style="width:100%; padding:10px; border:1px solid var(--border); border-radius:6px;">
                            </div>
                        </div>

                        <button type="submit" class="btn" style="padding: 14px; justify-content: center; font-size: 1rem; margin-top: 10px;">
                            🚀 Dispatch & Transmit Purchase Order
                        </button>
                    </form>
                </div>
            </div>

        </div>
        <script src="../../js/getSupplier.js"></script>

    <!-- monitor deliveries-->