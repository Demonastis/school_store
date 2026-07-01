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