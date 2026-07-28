<?php
// dashboards/admin/modules/users.php
// Inherits standard database connection $conn from parent admin dashboard shell

// 1. Process New User Account Provisioning Post Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user_action'])) {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $username   = trim($_POST['username']);
    $raw_pass   = $_POST['password'] ?? '';
    $role       = $_POST['role'];

    $conn->begin_transaction();
    try {
        if (empty($raw_pass)) {
            throw new Exception("Security Constraint Violated: Account password configuration cannot be empty.");
        }

        // Securely hash raw passwords using standard industry bcrypt cryptographic encryption
        $password_hash = password_hash($raw_pass, PASSWORD_BCRYPT);

        // Validation: Verify uniqueness of username and email to prevent system collisions
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            throw new Exception("Account provisioning blocked: Username or email identifier already registered.");
        }
        $check_stmt->close();

        // Insert new user into the directory
        $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, username, password_hash, role, is_archived) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $insert_stmt->bind_param("ssssss", $first_name, $last_name, $email, $username, $password_hash, $role);
        $insert_stmt->execute();
        $insert_stmt->close();

        // Log the account creation into global audit logs table for monitoring compliance
        $audit_desc = "Admin provisioned a new user profile account for {$first_name} {$last_name} (Username: '{$username}', Role: '{$role}').";
        $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (1, 'Create User', 'Admin Directory Module', ?)");
        $audit_stmt->bind_param("s", $audit_desc);
        $audit_stmt->execute();
        $audit_stmt->close();

        $conn->commit();
        echo "<div style='padding:15px; margin-bottom:20px; background:#d1e7dd; color:#0f5132; border-radius:4px; font-weight:500;'>✓ New security user context successfully added to active directory indices.</div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:4px; font-weight:500;'>✕ Creation Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// 2. Fetch Active, Non-Archived User Profiles for Display Matrix Data
$users_query = "SELECT user_id, first_name, last_name, email, username, role, created_at FROM users WHERE is_archived = 0 ORDER BY role ASC, last_name ASC";
$users_result = $conn->query($users_query);
?>

<div class="user-management-module" style="background: white; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <!-- Top Action Context Element Line Layout -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600;">System User Directory Registry</h3>
            <p style="margin: 4px 0 0 0; font-size: 0.8rem; color: #64748b;">Manage structural system user access authorizations across operational roles.</p>
        </div>
        <button onclick="document.getElementById('userCreationModal').style.display='flex';" style="background: #2563eb; color: white; border: none; padding: 10px 18px; font-size: 0.85rem; font-weight: bold; border-radius: 4px; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            👤 Provision New User
        </button>
    </div>

    <!-- Active User Database Matrix Data Table Loop -->
    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px;">Full Profile Name</th>
                <th style="padding: 12px;">Account Credentials</th>
                <th style="padding: 12px;">Network Email Address</th>
                <th style="padding: 12px;">System Access Authorization Role</th>
                <th style="padding: 12px;">Registry Timestamp</th>
                <th style="padding: 12px; text-align: right;">Directory Management Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users_result->num_rows === 0): ?>
                <tr>
                    <td colspan="6" style="padding: 24px; text-align: center; color: #868e96; font-style: italic;">No active accounts matching directory index scope rules.</td>
                </tr>
            <?php else: ?>
                <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #e9ecef; transition: background 0.15s;" onmouseover="this.style.background='#fafafa'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 12px; font-weight: 500; color: #1e293b;">
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            <span style="display:block; font-size:0.75rem; color:#94a3b8;">UID: #USER-<?= $user['user_id'] ?></span>
                        </td>
                        <td style="padding: 12px; font-family: monospace; color: #475569;"><?= htmlspecialchars($user['username']) ?></td>
                        <td style="padding: 12px; color: #475569;"><?= htmlspecialchars($user['email']) ?></td>
                        <td style="padding: 12px;">
                            <?php 
                            $role_tint = '#64748b';
                            if ($user['role'] === 'admin') $role_tint = '#dc2626';
                            if ($user['role'] === 'manager') $role_tint = '#2563eb';
                            if ($user['role'] === 'custodian') $role_tint = '#d97706';
                            if ($user['role'] === 'cashier') $role_tint = '#059669';
                            ?>
                            <span style="background: <?= $role_tint ?>; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>
                        <td style="padding: 12px; color: #64748b;"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                        <td style="padding: 12px; text-align: right;">
                            <?php if ($user['user_id'] != 1): ?>
                                <a href="admin_dashboard.php?page=users&archive_user_id=<?= $user['user_id'] ?>" onclick="return confirm('Confirm deactivation? This profile context will be completely removed from active login endpoints.');" style="background: #ef4444; color: white; border: none; padding: 6px 12px; font-size: 0.8rem; font-weight: bold; border-radius: 4px; text-decoration: none; display: inline-block; cursor: pointer;">
                                    🗄️ Archive User
                                </a>
                            <?php else: ?>
                                <small style="color: #cbd5e1; font-style: italic;">Primary Root Master Locked</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Dialog Box Container for Provisioning Forms Execution -->
<div id="userCreationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(2px);">
    <div style="background: white; padding: 25px; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; position: relative;">
        <h3 style="margin-top: 0; margin-bottom: 5px; font-size: 1.15rem; font-weight: 600; color: #0f172a;">Provision New Identity Framework Context</h3>
        <p style="margin: 0 0 20px 0; font-size: 0.8rem; color: #64748b;">Fill in credential information variables to bind new security credentials.</p>
        
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 12px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: bold; margin-bottom: 4px;">First Name</label>
                    <input type="text" name="first_name" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: bold; margin-bottom: 4px;">Last Name</label>
                    <input type="text" name="last_name" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 12px;">
                <label style="display: block; font-size: 0.8rem; font-weight: bold; margin-bottom: 4px;">Network Contact Email</label>
                <input type="email" name="email" required style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box;">
            </div>

