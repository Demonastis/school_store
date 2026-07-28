<?php
// dashboards/admin/modules/archive_users.php
// Inherits standard database connection $conn from parent admin dashboard shell

// 1. Handle User Restoration (Unarchive Action)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restore_user_action'])) {
    $user_id = intval($_POST['user_id']);
    
    $conn->begin_transaction();
    try {
        // Toggle the archive flag back to 0 (Active)
        $restore_stmt = $conn->prepare("UPDATE users SET is_archived = 0 WHERE user_id = ?");
        $restore_stmt->bind_param("i", $user_id);
        $restore_stmt->execute();

        // Log the restoration event in the audit logs table
        $audit_desc = "Admin restored archived user profile ID #USER-{$user_id} back to active directory status.";
        $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (1, 'Restore User', 'Admin Archive Module', ?)");
        $audit_stmt->bind_param("s", $audit_desc);
        $audit_stmt->execute();

        $conn->commit();
        echo "<div style='padding:15px; margin-bottom:20px; background:#d1e7dd; color:#0f5132; border-radius:4px; font-weight:500;'>✓ User account successfully restored to active status.</div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:4px; font-weight:500;'>✕ Restoration failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// 2. Query Archived User Profiles
$archived_users_query = "
    SELECT user_id, first_name, last_name, email, username, role, created_at 
    FROM users 
    WHERE is_archived = 1 
    ORDER BY last_name ASC, first_name ASC
";
$archived_users_result = $conn->query($archived_users_query);
?>

<div class="archive-module" style="background: white; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600;">System User Archive Directory</h3>
            <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #64748b;">Review and reactivate deactivated clerk, custodian, or manager user accounts.</p>
        </div>
        <span style="background: #f1f3f5; padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: bold; color: #495057;">
            🗄️ Total Archived Users: <?= $archived_users_result->num_rows ?>
        </span>
    </div>

    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px;">Full Name</th>
                <th style="padding: 12px;">Username</th>
                <th style="padding: 12px;">Email Address</th>
                <th style="padding: 12px;">Assigned Role</th>
                <th style="padding: 12px;">Account Created</th>
                <th style="padding: 12px; text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($archived_users_result->num_rows === 0): ?>
                <tr>
                    <td colspan="6" style="padding: 24px; text-align: center; color: #868e96; font-style: italic;">
                        The user archive directory is empty. No user accounts are currently deactivated.
                    </td>
                </tr>
            <?php else: ?>
                <?php while ($user = $archived_users_result->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #e9ecef; background: #fafafa; transition: background 0.2s;" onmouseover="this.style.background='#f1f3f5'" onmouseout="this.style.background='#fafafa'">
                        <td style="padding: 12px; font-weight: 500; color: #64748b;">
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            <span style="display:block; font-size:0.75rem; color:#94a3b8;">ID: #USER-<?= $user['user_id'] ?></span>
                        </td>
                        <td style="padding: 12px; color: #64748b;"><?= htmlspecialchars($user['username']) ?></td>
                        <td style="padding: 12px; color: #64748b;"><?= htmlspecialchars($user['email']) ?></td>
                        <td style="padding: 12px;">
                            <span style="background: #e2e8f0; color: #475569; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>
                        <td style="padding: 12px; color: #64748b;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td style="padding: 12px; text-align: right;">
                            <form method="POST" style="margin: 0; display: inline-block;" onsubmit="return confirm('Are you sure you want to reactivate this user account?');">
                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                <button type="submit" name="restore_user_action" style="background: #22c55e; color: white; border: none; padding: 6px 12px; font-size: 0.8rem; font-weight: bold; border-radius: 4px; cursor: pointer; transition: background 0.1s;">
                                    ♻️ Restore User
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
