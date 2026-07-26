<div id="orders" class="content-section">
                <div class="action-bar">
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