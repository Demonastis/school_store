<?php
session_start();
require_once '../../config/db.php';
require_once '../../auth/auth.php';

// Route protection validation guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized transactional interface context execution.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];
    $queue = $_POST['queue'];
    $currentIndex = (int)$_POST['index'];
    $admin_id = $_SESSION['user_id'];

    // Enforce role consistency against your schema definitions
    $allowed_roles = ['Admin', 'Manager', 'Custodian', 'Cashier'];
    if (!in_array($new_role, $allowed_roles)) {
        die("Invalid role parameters specified.");
    }

    // Begin database transaction
    $conn->begin_transaction();

    try {
        // 1. Update the target user's system role
        $updateQuery = "UPDATE users SET role = ? WHERE user_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("si", $new_role, $target_user_id);
        $stmt->execute();

        // 2. Append event history inside log tables
        $action = "Role Assignment";
        $module = "User Management";
        $description = "Admin ID {$admin_id} updated the authorization role of User ID {$target_user_id} to '{$new_role}'.";
        
        $logQuery = "INSERT INTO audit_logs (user_id, action, module, description) VALUES (?, ?, ?, ?)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("isss", $admin_id, $action, $module, $description);
        $logStmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Database workflow transaction processing error: " . $e->getMessage());
    }

    // Advance queue pointer to pull the next user record
    $nextIndex = $currentIndex + 1;
    header("Location: ../admin_dashboard.php?page=role-assignment&queue={$queue}&index={$nextIndex}");
    exit;
}
    