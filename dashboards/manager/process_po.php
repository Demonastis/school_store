<?php
// dashboards/manager/process_po.php
require_once '../../config/db.php';
require_once '../../config/email_config.php'; // Include secure email credential bindings

// 1. Manually include PHPMailer core libraries (Adjust paths based on your structure)
require '../../vendor/phpmailer/phpmailer/src/Exception.php';
require '../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '../../vendor/phpmailer/phpmailer/src/SMTP.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gather secure input variables from form post
    $supplier_id    = intval($_POST['supplier_id']);
    $product_id     = intval($_POST['product_id']);
    $bulk_unit      = $_POST['bulk_unit'];
    $quantity       = intval($_POST['quantity']);
    $wholesale_cost = floatval($_POST['wholesale_cost']);
    $order_date     = $_POST['order_date'];
    $expected_date  = $_POST['expected_date'];
    
    // Calculate total order cost
    $total_amount = $quantity * $wholesale_cost;
    $created_by   = "manager_user"; 
    $status       = "Pending";

    // Start SQL Transaction to keep database changes safe
    $conn->begin_transaction();

    try {
        // Insert order details into the main purchase_orders table
        $po_stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_id, order_date, expected_delivery_date, status, total_amount, created_by, Box_unit) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $po_stmt->bind_param("isssdsi", $supplier_id, $order_date, $expected_date, $status, $total_amount, $created_by, $quantity);
        $po_stmt->execute();
        $new_po_id = $conn->insert_id;

        // Fetch supplier email and product name details for audit log and email alert
        $info_stmt = $conn->prepare("SELECT s.supplier_name, s.email, p.product_name FROM suppliers s, products p WHERE s.supplier_id = ? AND p.product_id = ?");
        $info_stmt->bind_param("ii", $supplier_id, $product_id);
        $info_stmt->execute();
        $info_res = $info_stmt->get_result()->fetch_assoc();
        
        if (!$info_res) {
            throw new Exception("Invalid relationship mapping configuration detected for vendor or item catalog identifiers.");
        }

        $supplier_name  = $info_res['supplier_name'];
        $supplier_email = $info_res['email'];
        $product_name   = $info_res['product_name'];

        // Write a descriptive audit statement inside your audit logs table
        $audit_desc = "Generated purchase order #PO-{$new_po_id} for {$quantity} {$bulk_unit}(s) of '{$product_name}' at ₱" . number_format($wholesale_cost, 2) . " per unit from vendor {$supplier_name}.";
        $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (3, 'Create Purchase Order', 'Inventory', ?)");
        $audit_stmt->bind_param("s", $audit_desc);
        $audit_stmt->execute();

        // 2. PHPMailer SMTP Pipeline Configuration Block
        $mail = new PHPMailer(true);

        // Server Settings
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host       = SMTP_HOST;                        // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                             // Enable SMTP authentication
        $mail->Username   = SMTP_USER;                        // SMTP username
        $mail->Password   = SMTP_PASS;                        // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;       // Enable TLS encryption
        $mail->Port       = SMTP_PORT;                        // TCP port to connect to

        // Recipients Context Details
        $mail->setFrom(SMTP_FROM, SMTP_NAME);
        $mail->addAddress($supplier_email, $supplier_name);   // Add supplier as target recipient
        $mail->addReplyTo('manager@store.com', 'Inventory Manager');

        // Formulate a structured HTML message specifying bulk configuration details
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = "Official Purchase Order Request: #PO-{$new_po_id} - School Supplies System";
        
        $mail->Body    = "
            <div style='font-family: sans-serif; max-width: 600px; color: #333; line-height: 1.6;'>
                <h2 style='color: #0056b3; border-bottom: 2px solid #0056b3; padding-bottom: 10px;'>Official Procurement Requirements Request</h2>
                <p>Dear <strong>{$supplier_name} Team</strong>,</p>
                <p>Please find our official order specifications outlined below for urgent fulfillment:</p>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr style='background: #f8f9fa;'><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Purchase Order Ref:</td><td style='padding: 8px; border: 1px solid #ddd;'>#PO-{$new_po_id}</td></tr>
                    <tr><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Item Requested:</td><td style='padding: 8px; border: 1px solid #ddd;'>{$product_name}</td></tr>
                    <tr style='background: #f8f9fa;'><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Bulk Volume Unit:</td><td style='padding: 8px; border: 1px solid #ddd;'>{$bulk_unit}</td></tr>
                    <tr><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Quantity Desired:</td><td style='padding: 8px; border: 1px solid #ddd;'>{$quantity} Unit(s)</td></tr>
                    <tr style='background: #f8f9fa;'><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Agreed Cost Rate:</td><td style='padding: 8px; border: 1px solid #ddd;'>₱" . number_format($wholesale_cost, 2) . " per unit</td></tr>
                    <tr style='background: #e9ecef; font-weight: bold;'><td style='padding: 8px; border: 1px solid #ddd;'>Aggregated Total Value:</td><td style='padding: 8px; border: 1px solid #ddd; color: #d63384;'>₱" . number_format($total_amount, 2) . "</td></tr>
                    <tr><td style='padding: 8px; border: 1px solid #ddd; font-weight: bold;'>Expected Delivery Cutoff:</td><td style='padding: 8px; border: 1px solid #ddd; color: #dc3545;'>{$expected_date}</td></tr>
                </table>
                
                <p>Please acknowledge receipt of this message and confirm the logistical tracking delivery timeline.</p>
                <hr style='border: none; border-top: 1px solid #ddd; margin-top: 30px;'>
                <p style='font-size: 0.85rem; color: #6c757d;'>Best Regards,<br><strong>EduManage Purchasing Department</strong></p>
            </div>
        ";

        // Plain text fallback body for email clients that do not render HTML formatting
        $mail->AltBody = "Purchase Order Request #PO-{$new_po_id}\nItem: {$product_name}\nQuantity: {$quantity} {$bulk_unit}(s)\nTotal Value: ₱" . number_format($total_amount, 2) . "\nExpected Date: {$expected_date}";

        // Dispatch Email via Secure Outbound Connection
        $mail->send();

        // Save and commit data entries ONLY if the email dispatched successfully without errors
        $conn->commit();
        
        // Redirect manager back to dashboard overview panel smoothly
        header("Location: manager_dashboard.php?page=orders&success=po_created");
        exit();

    } catch (Exception $e) {
        // Rollback all queries if database processing fails OR mail delivery throws an exception
        $conn->rollback();
        die("Procurement Transaction Processing Loop Failed. System Error Trace: " . $e->getMessage() . " (PHPMailer Debug Info: " . ($mail->ErrorInfo ?? 'None') . ")");
    }
}
?>
