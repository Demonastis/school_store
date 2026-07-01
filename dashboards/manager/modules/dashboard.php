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
                    <a href="manager_dashboard.php?page=purchase" class="btn" style="text-decoration: none;">➕ Create Purchase Order</a>
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