<?php
$current_admin_id = $_SESSION['user_id'] ?? 1; // Pulled from your active admin session keys

// --- A. BATCH ACTIONS MANAGEMENT (POST PROCESSED REGION) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_archive'])) {
    $selected = $_POST['selected_users'] ?? [];
    $operation_task = $_POST['operation_task'] ?? 'archive'; // Reads our dynamic JS action intent flag

    // Map status requirements dynamically based on operation request intent
    $target_archive_state = ($operation_task === 'recover') ? 0 : 1;
    $audit_action_title = ($operation_task === 'recover') ? 'Bulk User Recovery' : 'Bulk User Archival';

    if (!empty($selected)) {
        $ids = array_map('intval', $selected);
        // Exclude system root admin (UID #1) as an infrastructure safeguard
        $ids = array_diff($ids, [1]);

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));

            $conn->begin_transaction();
            try {
                // 1. Bulk Update user status flags in a safe transaction block
                $stmt = $conn->prepare("
                    UPDATE users
                    SET is_archived = ?
                    WHERE user_id IN ($placeholders)
                ");

                // Merge variables to bind both target status value and array parameters securely
                $bind_params = array_merge([$target_archive_state], $ids);
                $bind_types = 'i' . $types;

                $stmt->bind_param($bind_types, ...$bind_params);
                $stmt->execute();
                $stmt->close();

                // 2. Append event timeline details straight to the audit logs
                $csv_affected_profiles = implode(', ', $ids);
                $audit_desc = "Admin batch processed a '{$operation_task}' operation targeting User IDs: [{$csv_affected_profiles}].";

                $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (?, ?, 'User Management Module', ?)");
                $audit_stmt->bind_param("iss", $current_admin_id, $audit_action_title, $audit_desc);
                $audit_stmt->execute();
                $audit_stmt->close();

                $conn->commit();

                $alert_msg = ($operation_task === 'recover') ? '✓ Selected profiles successfully restored to active lists.' : '✓ Selected profiles successfully moved into archival indices.';
                echo "<div class='alert alert-success alert-dismissible fade show border-0 shadow-sm' role='alert'>
                        {$alert_msg}
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
            } catch (Exception $e) {
                $conn->rollback();
                echo "<div class='alert alert-danger alert-dismissible fade show border-0 shadow-sm' role='alert'>
                        ✕ Processing Error: " . htmlspecialchars($e->getMessage()) . "
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                      </div>";
            }
        }
    }
}

// --- B. USER CREATION HANDLER ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user_action'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $raw_pass   = $_POST['password'] ?? '';
    $role       = $_POST['role'] ?? '';

    $conn->begin_transaction();
    try {
        if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($raw_pass) || empty($role)) {
            throw new Exception("All profile directory fields are strictly mandatory.");
        }

        $password_hash = password_hash($raw_pass, PASSWORD_BCRYPT);

        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            throw new Exception("Username or email identifier already registered.");
        }
        $check_stmt->close();

        $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, username, password_hash, role, is_archived) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $insert_stmt->bind_param("ssssss", $first_name, $last_name, $email, $username, $password_hash, $role);
        $insert_stmt->execute();
        $insert_stmt->close();

        $audit_desc = "Admin provisioned a new user profile account for {$first_name} {$last_name} (Username: '{$username}', Role: '{$role}').";
        $audit_stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, module, description) VALUES (?, 'Create User', 'Admin Directory Module', ?)");
        $audit_stmt->bind_param("is", $current_admin_id, $audit_desc);
        $audit_stmt->execute();
        $audit_stmt->close();

        $conn->commit();
        echo "<div class='alert alert-success alert-dismissible fade show border-0 shadow-sm' role='alert'>
                ✓ New security user context successfully added to active directory indices.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    } catch (Exception $e) {
        $conn->rollback();
        echo "<div class='alert alert-danger alert-dismissible fade show border-0 shadow-sm' role='alert'>
                ✕ Creation Error: " . htmlspecialchars($e->getMessage()) . "
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
              </div>";
    }
}

// --- C. DYNAMIC FILTERS AND PAGINATION ENGINE MANAGEMENT ---
$search  = isset($_GET['search']) ? trim($_GET['search']) : '';
$role    = isset($_GET['role']) ? trim($_GET['role']) : '';
$status  = isset($_GET['status']) ? trim($_GET['status']) : '';
$archive = isset($_GET['archive']) ? trim($_GET['archive']) : '0'; // Defaults nicely to active view states

$limit = 10;
$page_num = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page_num < 1) {
    $page_num = 1;
}
$offset = ($page_num - 1) * $limit;

// --- 1. RUN LIGHTWEIGHT COUNT QUERY FIRST ---
$count_sql = "SELECT COUNT(*) as total FROM users WHERE 1=1";
$count_params = [];
$count_types = '';

if ($archive !== '') {
    $count_sql .= " AND is_archived = ?";
    $count_params[] = (int)$archive;
    $count_types .= 'i';
}
if ($role !== '') {
    $count_sql .= " AND role = ?";
    $count_params[] = $role;
    $count_types .= 's';
}
if ($status !== '') {
    $count_sql .= " AND approval_status = ?";
    $count_params[] = $status;
    $count_types .= 's';
}
if ($search !== '') {
    $count_sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ?)";
    $term = "%{$search}%";
    array_push($count_params, $term, $term, $term, $term);
    $count_types .= 'ssss';
}

$count_stmt = $conn->prepare($count_sql);
if (!empty($count_params)) {
    $count_stmt->bind_param($count_types, ...$count_params);
}
$count_stmt->execute();
$total_rows = $count_stmt->get_result()->fetch_assoc()['total'];
$count_stmt->close();

$total_pages = ceil($total_rows / $limit);
if ($total_pages < 1) {
    $total_pages = 1;
}

// --- 2. BUILD AND COMPILE COMPACT MAIN SELECTION STRING ---
$sql = "
SELECT
    user_id,
    first_name,
    last_name,
    email,
    username,
    role,
    approval_status,
    is_archived,
    created_at
FROM users
WHERE 1=1
";

$params = [];
$types  = '';

if ($archive !== '') {
    $sql .= " AND is_archived = ?";
    $params[] = (int)$archive;
    $types .= 'i';
}
if ($role !== '') {
    $sql .= " AND role = ?";
    $params[] = $role;
    $types .= 's';
}
if ($status !== '') {
    $sql .= " AND approval_status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($search !== '') {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR username LIKE ? OR email LIKE ?)";
    $term = "%{$search}%";
    array_push($params, $term, $term, $term, $term);
    $types .= 'ssss';
}

$sql .= " ORDER BY last_name ASC";

// SECURELY ATTACH LIMIT CRITERIAS RIGHT BEFORE CONTEXT COMPILATION PREPARATION
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Execute prepared query safely
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users_result = $stmt->get_result(); // Safely targets your data <tbody> rendering context
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
    <div class="user-toolbar">

        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body p-3">
                <form method="GET" id="filterForm" class="row g-2 align-items-end">
                    <input type="hidden" name="page" value="users">

                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Search Directory</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white text-muted border-end-0">
                                <svg xmlns="http://w3.org" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                                </svg>
                            </span>
                            <input
                                type="text"
                                name="search"
                                class="form-control form-control-sm border-start-0"
                                placeholder="Search name, email, username"
                                value="<?= htmlspecialchars($search) ?>">
                        </div>
                    </div>

                    <div class="col-6 col-sm-4 col-md-2 col-lg-2">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Role Filter</label>
                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Roles</option>
                            <option value="Admin" <?= ($role === 'Admin') ? 'selected' : '' ?>>Admin</option>
                            <option value="Manager" <?= ($role === 'Manager') ? 'selected' : '' ?>>Manager</option>
                            <option value="Custodian" <?= ($role === 'Custodian') ? 'selected' : '' ?>>Custodian</option>
                            <option value="Cashier" <?= ($role === 'Cashier') ? 'selected' : '' ?>>Cashier</option>
                        </select>
                    </div>

                    <div class="col-6 col-sm-4 col-md-2 col-lg-2">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Archive State</label>
                        <select name="archive" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="0" <?= ($archive === '0') ? 'selected' : '' ?>>Active</option>
                            <option value="1" <?= ($archive === '1') ? 'selected' : '' ?>>Archived</option>
                            <option value="" <?= ($archive === '') ? 'selected' : '' ?>>All</option>
                        </select>
                    </div>

                    <div class="col-6 col-sm-4 col-md-2 col-lg-2">
                        <label class="form-label small fw-bold text-secondary text-uppercase mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="" <?= ($status === '') ? 'selected' : '' ?>>All Status</option>
                            <option value="pending" <?= ($status === 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="approved" <?= ($status === 'approved') ? 'selected' : '' ?>>Approved</option>
                            <option value="rejected" <?= ($status === 'rejected') ? 'selected' : '' ?>>Rejected</option>
                        </select>
                    </div>

                    <div class="col-6 col-sm-12 col-md-2 col-lg-3 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-medium">
                            Search
                        </button>
                        <a href="admin_dashboard.php?page=users" class="btn btn-sm btn-outline-secondary px-3" title="Clear Filters">
                            Reset
                        </a>
                    </div>
                </form>
            </div>


        </div>



    </div>


    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
        <thead>
            <tr style="
    background:#f8fafc;
    border-bottom:2px solid #e2e8f0;
">
                <th width="40">
                    <input type="checkbox" id="checkAll">
                </th>

                <th>User</th>

                <th>Role</th>

                <th>Approval Status</th>

                <th>Record Status</th>

                <th>Created</th>

            </tr>
        </thead>
        <tbody>

            <?php if ($users_result->num_rows === 0): ?>

                <tr>

                    <td colspan="6"
                        style="padding:30px; text-align:center; color:#94a3b8;">
                        No users found.
                    </td>
                </tr>

            <?php else: ?>

                <?php while ($user = $users_result->fetch_assoc()): ?>


                    <tr class="user-row"
                        style="
                    border-bottom:1px solid #e2e8f0;
                    transition:.15s;
                    <?= ((int)$user['is_archived'] === 1) ? 'background-color: #f8fafc; opacity: 0.65;' : '' ?>
                ">

                        <td style="padding:12px;">
                            <?php if ((int)$user['user_id'] !== 1): ?>
                                <input
                                    type="checkbox"
                                    class="user-check"
                                    name="selected_users[]"
                                    value="<?= $user['user_id'] ?>">
                            <?php endif; ?>
                        </td>

                        <td style="padding:12px;">
                            <div style="display:flex; align-items:flex-start; gap:12px;">
                                <div style="
                            width:42px;
                            height:42px;
                            border-radius:50%;
                            background:#e2e8f0;
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            font-weight:bold;
                            color:#475569;
                            flex-shrink:0;
                        ">
                                    <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
                                </div>

                                <div>
                                    <div style="font-weight:600; color:#0f172a;">
                                        <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                                    </div>

                                    <div style="font-size:.8rem; color:#64748b;">
                                        @<?= htmlspecialchars($user['username']) ?>
                                    </div>

                                    <div style="font-size:.8rem; color:#94a3b8;">
                                        <?= htmlspecialchars($user['email']) ?>
                                    </div>

                                    <div style="font-size:.75rem; color:#cbd5e1;">
                                        UID #<?= $user['user_id'] ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td style="padding:12px;">
                            <?php
                            $roleColor = '#64748b';
                            switch ($user['role']) {
                                case 'Admin':
                                    $roleColor = '#dc2626';
                                    break;
                                case 'Manager':
                                    $roleColor = '#2563eb';
                                    break;
                                case 'Custodian':
                                    $roleColor = '#d97706';
                                    break;
                                case 'Cashier':
                                    $roleColor = '#059669';
                                    break;
                            }
                            ?>
                            <span style="background:<?= $roleColor ?>; color:white; padding:4px 10px; border-radius:999px; font-size:.75rem; font-weight:600;">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </td>

                        <td style="padding:12px;">
                            <?php
                            $status = strtolower($user['approval_status']);
                            $statusColor = '#f59e0b';
                            if ($status === 'approved') {
                                $statusColor = '#16a34a';
                            }
                            if ($status === 'rejected') {
                                $statusColor = '#dc2626';
                            }
                            ?>
                            <span style="background:<?= $statusColor ?>15; color:<?= $statusColor ?>; border:1px solid <?= $statusColor ?>30; padding:4px 10px; border-radius:999px; font-size:.75rem; font-weight:600;">
                                <?= ucfirst($status) ?>
                            </span>
                        </td>

                        <td style="padding:12px;">
                            <?php if ((int)$user['is_archived'] === 1): ?>
                                <span style="background:#ef444420; color:#dc2626; border:1px solid #ef444440; padding:4px 10px; border-radius:999px; font-size:.75rem; font-weight:600; white-space:nowrap;">
                                    📦 Archived
                                </span>
                            <?php else: ?>
                                <span style="background:#22c55e15; color:#16a34a; border:1px solid #22c55e30; padding:4px 10px; border-radius:999px; font-size:.75rem; font-weight:600; white-space:nowrap;">
                                    🟢 Active
                                </span>
                            <?php endif; ?>
                        </td>

                        <td style="padding:12px; color:#64748b; white-space:nowrap;">
                            <?= date('M d, Y', strtotime($user['created_at'])) ?>
                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php endif; ?>

        </tbody>

    </table>
    <?php
    // Build query string parameter persistence to pass filters through page changes safely
    $url_query = $_GET;
    unset($url_query['p']); // Drop page index context before appending new parameters
    $query_string = http_build_query($url_query);
    $base_url = "admin_dashboard.php?" . ($query_string ? $query_string . "&" : "");
    ?>

    <div class="d-flex justify-content-between align-items-center mt-3 bg-white p-3 rounded border border-light shadow-sm">
        <!-- Records Counter Metrics Text -->
        <div class="small text-secondary">
            Showing records <strong><?= min($offset + 1, $total_rows) ?></strong> to <strong><?= min($offset + $limit, $total_rows) ?></strong> of <strong><?= $total_rows ?></strong> matching user accounts.
        </div>

        <!-- Bootstrap 5 Navigation Buttons -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Table page navigation">
                <ul class="pagination pagination-sm mb-0">
                    <!-- Previous Button Block -->
                    <li class="page-item <?= ($page_num <= 1) ? 'disabled' : '' ?>">
                        <a class="page-item page-link border text-dark" href="<?= $base_url ?>p=<?= $page_num - 1 ?>">Previous</a>
                    </li>

                    <!-- Individual Numerical Page Blocks Loop -->
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page_num === $i) ? 'active' : '' ?>">
                            <a class="page-link border <?= ($page_num === $i) ? 'bg-dark border-dark text-white' : 'text-dark bg-white' ?>" href="<?= $base_url ?>p=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Next Button Block -->
                    <li class="page-item <?= ($page_num >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-item page-link border text-dark" href="<?= $base_url ?>p=<?= $page_num + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
    <div style="margin-top:15px">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

            <!-- Left Side: Batch Management Actions -->
            <div class="d-flex flex-wrap gap-2">
                <!-- Open Approval Center -->
                <button type="button" id="btnApprovalCenter" class="btn btn-primary d-inline-flex align-items-center gap-2 shadow-sm">
                    <span>⚡</span> Open Approval Center
                    <span class="badge bg-white text-primary rounded-pill shadow-inner" id="selectedCount">0</span>
                </button>

                <!-- SMART ACTION BUTTON: Switches between Archive and Recover based on filter state -->
                <?php if ($archive === '1'): ?>
                    <!-- Recover Selected Button Configuration -->
                    <button type="button" id="btnBulkArchive" data-action="recover" class="btn btn-outline-success d-inline-flex align-items-center gap-2">
                        <svg xmlns="http://w3.org" width="16" height="16" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z" />
                            <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.445 2.218a.25.25 0 0 0 0 .384l2.146 1.876a.25.25 0 0 0 .41-.192z" />
                        </svg>
                        Recover Selected
                    </button>
                <?php else: ?>
                    <!-- Standard Archive Selected Button Configuration -->
                    <button type="button" id="btnBulkArchive" data-action="archive" class="btn btn-outline-danger d-inline-flex align-items-center gap-2">
                        <svg xmlns="http://w3.org" width="16" height="16" fill="currentColor" class="bi bi-archive-fill" viewBox="0 0 16 16">
                            <path d="M12.643 15C13.979 15 15 13.845 15 12.5V5H1v7.5C1 13.845 2.021 15 3.357 15zM5.5 7h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1M.8 1a.8.8 0 0 0-.8.8V3a.8.8 0 0 0 .8.8h14.4A.8.8 0 0 0 16 3V1.8a.8.8 0 0 0-.8-.8z" />
                        </svg>
                        Archive Selected
                    </button>
                <?php endif; ?>
            </div>

            <!-- Right Side: Role Assignment -->
            <div>
                <button type="button" id="btnRoleAssignment" class="btn btn-dark d-inline-flex align-items-center gap-2 shadow-sm">
                    <svg xmlns="http://w3.org" width="16" height="16" fill="currentColor" class="bi bi-person-gear" viewBox="0 0 16 16">
                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 1 0 4m.002 6a4.99 4.99 0 0 1 2.148-1.52A5.02 5.02 0 0 0 8 10a5 5 0 0 0-5 5v1h4v-1a.5.5 0 0 1 .002-.12M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1.5a.5.5 0 0 0 .5.5H14a.5.5 0 0 0 0-1h-1.5V11a.5.5 0 0 0-.5-.5" />
                    </svg>
                    Role Assignment
                </button>
            </div>

        </div>
    </div>



</div>

<?php include("userCreationModal.php"); ?>