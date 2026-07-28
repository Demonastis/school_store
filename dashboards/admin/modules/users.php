<?php
// dashboards/admin/modules/users.php
// Inherits standard database connection $conn from parent admin dashboard shell
// Assumes a standard session is active for tracking the logged-in administrator

// Ensure an admin session context exists; fall back to ID 1 if not defined
$current_admin_id = $_SESSION['admin_id'] ?? 1;

// 1. Process New User Account Provisioning Post Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user_action'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $raw_pass   = $_POST['password'] ?? '';
    $role       = $_POST['role'] ?? '';

    $conn->begin_transaction();
    try {
        // Validate all required inputs are present
        if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($raw_pass) || empty($role)) {
            throw new Exception("Security Constraint Violated: All profile directory fields are strictly mandatory.");
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

        // Log the account creation into global audit logs table using the current admin's session ID
        $audit_desc = "Admin provisioned a new user profile account for {$first_name} {$last_name} (Username: '{$username}', Role: '{$role}').";
        $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (?, 'Create User', 'Admin Directory Module', ?)");
        $audit_stmt->bind_param("is", $current_admin_id, $audit_desc);
        $audit_stmt->execute();
        $audit_stmt->close();

        $conn->commit();
        echo "<div style='padding:15px; margin-bottom:20px; background:#d1e7dd; color:#0f5132; border-radius:4px; font-weight:500;'>✓ New security user context successfully added to active directory indices.</div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:4px; font-weight:500;'>✕ Creation Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// 2. Process User Archiving Request (FIXED: Added missing backend logic)
if (isset($_GET['archive_user_id'])) {
    $archive_id = (int)$_GET['archive_user_id'];
    
    $conn->begin_transaction();
    try {
        // Prevent archiving the core system master root administrator
        if ($archive_id === 1) {
            throw new Exception("Security Constraint Violated: Primary Root Master profile cannot be decommissioned.");
        }

        // Fetch user data before archiving for audit trail context tracking
        $user_stmt = $conn->prepare("SELECT first_name, last_name, username FROM users WHERE user_id = ?");
        $user_stmt->bind_param("i", $archive_id);
        $user_stmt->execute();
        $user_res = $user_stmt->get_result()->fetch_assoc();
        $user_stmt->close();

        if ($user_res) {
            // Flag record as archived instead of hard-deleting
            $archive_stmt = $conn->prepare("UPDATE users SET is_archived = 1 WHERE user_id = ?");
            $archive_stmt->bind_param("i", $archive_id);
            $archive_stmt->execute();
            $archive_stmt->close();

            // Log the decommission action to compliance data arrays
            $audit_desc = "Admin archived and disabled active user directory access for user: {$user_res['first_name']} {$user_res['last_name']} (Username: '{$user_res['username']}').";
            $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (?, 'Archive User', 'Admin Directory Module', ?)");
            $audit_stmt->bind_param("is", $current_admin_id, $audit_desc);
            $audit_stmt->execute();
            $audit_stmt->close();

            $conn->commit();
            echo "<div style='padding:15px; margin-bottom:20px; background:#d1e7dd; color:#0f5132; border-radius:4px; font-weight:500;'>✓ User registry profile safely archived and stripped of application endpoint route clearance.</div>";
        } else {
            throw new Exception("Targeted directory profile record index entry could not be resolved.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div style='padding:15px; margin-bottom:20px; background:#f8d7da; color:#721c24; border-radius:4px; font-weight:500;'>✕ Archiving Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// 3. Fetch Active, Non-Archived User Profiles for Display Matrix Data
$users_query = "SELECT user_id, first_name, last_name, email, username, role, created_at FROM users WHERE is_archived = 0 ORDER BY role ASC, last_name ASC";
$users_result = $conn->query($users_query);
?>

<div class="user-management-module" style="background: white; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0; font-family: system-ui, -apple-system, sans-serif;">
    <!-- Top Action Context Element Line Layout -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div>
            <h3 style="margin: 0; font-size: 1.2rem; font-weight: 600; color: #0f172a;">System User Directory Registry</h3>
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
                <th style="padding: 12px; color: #475569;">Full Profile Name</th>
                <th style="padding: 12px; color: #475569;">Account Credentials</th>
                <th style="padding: 12px; color: #475569;">Network Email Address</th>
                <th style="padding: 12px; color: #475569;">System Access Authorization Role</th>
                <th style="padding: 12px; color: #475569;">Registry Timestamp</th>
                <th style="padding: 12px; text-align: right; color: #475569;">Directory Management Actions</th>
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
<div id="userCreationModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center; z-index: 9999;">
    <div style="background: white; border-radius: 8px; width: 100%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; font-family: system-ui, -apple-system, sans-serif;">
        <!-- Modal Header Layout -->
        <div style="padding: 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600; color: #0f172a;">Account Provisioning Form Wizard</h4>
            <button onclick="document.getElementById('userCreationModal').style.display='none';" style="background: none; border: none; font-size: 1.25rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        
        <!-- Modal Form Body Context -->
        <form method="POST" style="margin: 0; padding: 20px;">
            <input type="hidden" name="create_user_action" value="1">
            
            <div style="display: flex; gap: 15px; margin-bottom: 15px;">
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">First Name *</label>
                    <input type="text" name="first_name" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Last Name *</label>
                    <input type="text" name="last_name" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Network Email Address *</label>
                <input type="email" name="email" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">System Username *</label>
                <input type="text" name="username" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Access Password *</label>
                <input type="password" name="password" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">System Access Authorization Role *</label>
                <select name="role" required style="width: 100%; box-sizing: border-box; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; font-size: 0.9rem;">
                    <option value="cashier">Cashier</option>
                    <option value="custodian">Custodian</option>
                    <option value="manager">Manager</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <!-- Form Action Triggers -->
            <div style="display: flex; justify-content: flex-end; gap: 10px; padding-top: 15px; border-top: 1px solid #e2e8f0;">
                <button type="button" onclick="document.getElementById('userCreationModal').style.display='none';" style="background: white; color: #475569; border: 1px solid #cbd5e1; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="submit" style="background: #2563eb; color: white; border: none; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 4px; cursor: pointer;">Execute Provisioning Process</button>
            </div>
        </form>
    </div>
</div>
