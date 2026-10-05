<?php
session_start();
require_once '../../../config/db.php';
require_once '../../../auth/auth.php';

// Route protection validation guard
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized transactional interface context execution.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_user_id = (int)$_POST['user_id'];
    $decision = $_POST['decision']; // 'approved' or 'rejected'
    $queue = $_POST['queue'];
    $currentIndex = (int)$_POST['index'];
    $admin_id = $_SESSION['user_id'];

    if (!in_array($decision, ['approved', 'rejected'])) {
        die("Invalid processing parameters context specified.");
    }

    // Begin database modification transaction structure
    $conn->begin_transaction();

    try {
        // 1. Mutate targeted account authorization flags
        $updateQuery = "UPDATE users SET approval_status = ?, approved_by = ?, approved_at = NOW() WHERE user_id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("sii", $decision, $admin_id, $target_user_id);
        $stmt->execute();

        // 2. Append event history inside log tables
        $action = "User account " . ucfirst($decision);
        $module = "User Management";
        $description = "Admin ID {$admin_id} changed account registration lifecycle status of User ID {$target_user_id} to '{$decision}'.";
        
        $logQuery = "INSERT INTO audit_logs (user_id, action, module, description) VALUES (?, ?, ?, ?)";
        $logStmt = $conn->prepare($logQuery);
        $logStmt->bind_param("isss", $admin_id, $action, $module, $description);
        $logStmt->execute();

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Database workflow transaction processing error state experienced: " . $e->getMessage());
    }

    // Advance queue context calculation pointer to pull the next record configuration layout
    $nextIndex = $currentIndex + 1;
    header("Location: ../admin_dashboard.php?page=approval-center&queue={$queue}&index={$nextIndex}");
    exit;
}
