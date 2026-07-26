<!-- MODULE 2: ALL PRODUCTS INVENTORY -->
            <div id="products" class="content-section">
                <div class="action-bar">
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