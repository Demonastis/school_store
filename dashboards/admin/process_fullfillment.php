<?php
// dashboards/custodian/process_fulfillment.php
require_once '../../config/db.php';
session_start();

$action = isset($_GET['action']) ? $_GET['action'] : '';
$transaction_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action === 'pack' && $transaction_id > 0) {
    // Transition status flag from 'Pending' to 'Ready'
    $stmt = $conn->prepare("UPDATE transactions SET status = 'Ready' WHERE transaction_id = ? AND status = 'Pending'");
    $stmt->bind_param("i", $transaction_id);
    
    if ($stmt->execute()) {
        header("Location: custodian_dashboard.php?page=dashboard&success=packed");
    } else {
        die("Error updating transaction packing status record.");
    }
    $stmt->close();
}
$conn->close();
?>
