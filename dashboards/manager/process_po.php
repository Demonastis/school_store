<?php
require_once '../../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Gather secure input variables from the form post
    $supplier_id    = intval($_POST['supplier_id']);
    $product_id     = intval($_POST['product_id']);
    $bulk_unit      = $_POST['bulk_unit'];
    $quantity       = intval($_POST['quantity']);
    $wholesale_cost = floatval($_POST['wholesale_cost']);
    $order_date     = $_POST['order_date'];
    $expected_date  = $_POST['expected_date'];
    
    // Calculate total order cost
    $total_amount = $quantity * $wholesale_cost;
    $created_by   = "manager_user"; // Static representation of the logged-in session role
    $status       = "Pending";

    // Start SQL Transaction to keep database changes safe
    $conn->begin_transaction();

    try {
        // Insert order details into the main purchase_orders table
        $po_stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_id, order_date, expected_delivery_date, status, total_amount, created_by, Box_unit) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $po_stmt->bind_param("isssdsi", $supplier_id, $order_date, $expected_date, $status, $total_amount, $created_by, $quantity);
        $po_stmt->execute();
        $new_po_id = $conn->insert_id;

        // Fetch supplier email and product name details for the system audit log and email alert
        $info_stmt = $conn->prepare("SELECT s.supplier_name, s.email, p.product_name FROM suppliers s, products p WHERE s.supplier_id = ? AND p.product_id = ?");
        $info_stmt->bind_param("ii", $supplier_id, $product_id);
        $info_stmt->execute();
        $info_res = $info_stmt->get_result()->fetch_assoc();
        
        $supplier_name = $info_res['supplier_name'];
        $supplier_email = $info_res['email'];
        $product_name  = $info_res['product_name'];

        // Write a descriptive audit statement inside your audit logs table
        $audit_desc = "Generated purchase order #PO-{$new_po_id} for {$quantity} {$bulk_unit}(s) of '{$product_name}' at ₱" . number_format($wholesale_cost, 2) . " per unit from vendor {$supplier_name}.";
        $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (3, 'Create Purchase Order', 'Inventory', ?)");
        $audit_stmt->bind_param("s", $audit_desc);
        $audit_stmt->execute();

        // Save and commit all data entries
        $conn->commit();
        /*
        // 2. Generate Email Notification to the Supplier
        $to      = $supplier_email;
        $subject = "Official Purchase Order Request: #PO-{$new_po_id} - School Supplies System";
        
        // Formulate a structured text body specifying bulk configuration details
        $message = "Dear " . htmlspecialchars($supplier_name) . " Team,\n\n";
        $message .= "Please find our official procurement item requirements request outlined below:\n\n";
        $message .= "--------------------------------------------------\n";
        $message .= "Purchase Order Reference: #PO-{$new_po_id}\n";
        $message .= "Item Requested: " . $product_name . "\n";
        $message .= "Bulk Volume Unit: " . $bulk_unit . "\n";
        $message .= "Quantity Desired: " . $quantity . " Unit(s)\n";
        $message .= "Agreed Cost Rate: ₱" . number_format($wholesale_cost, 2) . " per unit\n";
        $message .= "Aggregated Total Cost Value: ₱" . number_format($total_amount, 2) . "\n";
        $message .= "Expected Delivery Cutoff: " . $expected_date . "\n";
        $message .= "--------------------------------------------------\n\n";
        $message .= "Please acknowledge receipt of this message and confirm the delivery timeline.\n\n";
        $message .= "Best Regards,\nEduManage Purchasing Department";

        // Native PHP mail system configuration parameters
        $headers = "From: purchasing@schoolstore.edu.ph" . "\r\n" .
                   "Reply-To: manager@store.com" . "\r\n" .
                   "X-Mailer: PHP/" . phpversion();

        // Dispatch email
        @mail($to, $subject, $message, $headers);
        */

        // Redirect manager back to dashboard overview panel smoothly
        header("Location: manager_dashboard.php?success=1#orders");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Procurement Transaction Processing Loop Failed: " . $e->getMessage());
    }
}
?>
