<?php
session_start();
require_once '../../../config/db.php';
require_once '../../../auth/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized transactional interface context execution.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_archive'])) {
    
    if (empty($_POST['selected_users'])) {
        header("Location: ../admin_dashboard.php?page=users&error=no_users_selected");
        exit;
    }

    $selected_users = $_POST['selected_users'];
    $admin_id = $_SESSION['user_id'];
    
    // Evaluate operational task parameter intent ('archive' vs 'recover'), defaulting safely to archive
    $operation_task = isset($_POST['operation_task']) ? $_POST['operation_task'] : 'archive';
    $target_archive_value = ($operation_task === 'recover') ? 0 : 1;
    $log_action_label = ($operation_task === 'recover') ? "Bulk User Recovery" : "Bulk User Archival";

    $sanitized_ids = array_filter($selected_users, 'is_numeric');
    if (empty($sanitized_ids)) {
        header("Location: ../admin_dashboard.php?page=users&error=invalid_user_parameters");
        exit;
    }

    // Protect main administrator from being targeted
    $sanitized_ids = array_diff($sanitized_ids, [1]);
    if (empty($sanitized_ids)) {
        header("Location: ../admin_dashboard.php?page=users&error=cannot_modify_root_admin");
        exit;
    }

    $placeholders = implode(',', array_fill(0, count($sanitized_ids), '?'));
    $conn->begin_transaction();

    try {
        // 1. Update status flag to 1 (Archived) OR 0 (Active / Recovered)
        $updateQuery = "UPDATE users SET is_archived = ? WHERE user_id IN ($placeholders)";
        $stmt = $conn->prepare($updateQuery);
        
        // Merge variables to bind both target value and array parameters securely
        $bind_params = array_merge([$target_archive_value], $sanitized_ids);
        $types = 'i' . str_repeat('i', count($sanitized_ids));
        
        $stmt->bind_param($types, ...$bind_params);
        $stmt->execute();

        // 2. Append event history trail tracks inside your system audit logs
        $csv_affected_profiles = implode(', ', $sanitized_ids);
        $description = "Admin ID {$admin_id} executed a [{$operation_task}] operation for User IDs: [{$csv_affected_profiles}].";
        
        $logQuery = "INSERT INTO audit_logs (user_id, action, module, description) VALUES (?, ?, ?, ?)";
        $module = "User Manamentge";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("isss", $admin_id, $log_action_label, $module, $description);
        $logStmt->execute();

        $conn->commit();
        
        $msg_status = ($operation_task === 'recover') ? 'users_recovered' : 'users_archived';
        header("Location: ../admin_dashboard.php?page=users&success={$msg_status}");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        die("Critical infrastructure transaction exception experienced: " . $e->getMessage());
    }
} else {
    header("Location: ../admin_dashboard.php?page=users");
    exit;
}
    