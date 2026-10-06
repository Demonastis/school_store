<?php

require_once '../../auth/auth.php';
require_once '../../config/db.php';

// Force authentication and role checks first
requireRole(['Admin']);

$user_id = $_SESSION['user_id'];
$user_query = "
    SELECT user_id, username, role, profile_picture
    FROM users
    WHERE user_id = ?
";

$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - School Supplies Management System</title>

    <!-- Bootstrap 5.3.x -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../css/style.css" />
    <style>
        .avatar {
            width: 42px;
            height: 42px;
            min-width: 42px;
            min-height: 42px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
    </style>
</head>

<body>

    <?php include("adminsidebar.php"); ?>

    <!-- Main Section App Window -->
    <main>
        <header>
            <h2>User Management</h2>
        </header>

        <div class="content">
            <?php
            // 1. Define allowed modules explicitly to prevent path traversal / LFI
            $allowed_pages = [
                'users',
                'supplier',
                'audit-trail',
                'approval-center',
                'role-assignment'
            ];

            // 2. Fetch parameter and fallback safely if it's empty or invalid
            $page = isset($_GET['page']) ? $_GET['page'] : 'users';

            if (in_array($page, $allowed_pages)) {
                $module_path = "modules/" . $page . ".php";

                if (file_exists($module_path)) {
                    include($module_path);
                } else {
                    echo "<div class='alert alert-danger'>Module file missing.</div>";
                }
            } else {
                // If an attacker tries '?page=../../etc/passwd', they get caught here
                echo "<div class='alert alert-warning'>Access Denied: Invalid module target.</div>";
            }
            ?>
        </div>
    </main>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <script>
        // Locate the btnBulkArchive click listener inside your existing script block and update it to this:
        document.addEventListener("DOMContentLoaded", function() {
            const checkAll = document.getElementById("checkAll");
            const countBadge = document.getElementById("selectedCount");

            // Unified tracking calculation function
            function updateCounter() {
                const checkedCount = document.querySelectorAll(".user-check:checked").length;
                if (countBadge) {
                    countBadge.textContent = checkedCount;
                }
            }

            // Master checkbox select/unselect control layout
            if (checkAll) {
                checkAll.addEventListener("change", function() {
                    document.querySelectorAll(".user-check").forEach(box => {
                        box.checked = this.checked;
                    });
                    updateCounter();
                });
            }

            // Monitor row context changes cleanly
            document.addEventListener("change", function(e) {
                if (e.target && e.target.classList.contains("user-check")) {
                    updateCounter();
                }
            });

            // 1. Handle Approval Center Action Trigger
            const btnApproval = document.getElementById("btnApprovalCenter");
            if (btnApproval) {
                btnApproval.addEventListener("click", function() {
                    const ids = Array.from(document.querySelectorAll(".user-check:checked")).map(box => box.value);
                    if (ids.length === 0) {
                        alert("Please select at least one user record.");
                        return;
                    }
                    window.location.href = `admin_dashboard.php?page=approval-center&queue=${ids.join(",")}`;
                });
            }

            // 2. Handle Role Assignment Action Trigger
            const btnRole = document.getElementById("btnRoleAssignment");
            if (btnRole) {
                btnRole.addEventListener("click", function() {
                    const ids = Array.from(document.querySelectorAll(".user-check:checked")).map(box => box.value);
                    if (ids.length === 0) {
                        alert("Please select at least one user record.");
                        return;
                    }
                    window.location.href = `admin_dashboard.php?page=role-assignment&queue=${ids.join(",")}`;
                });
            }

            // 3. Handle Smart Archive / Recovery Action Trigger
            const btnBulkArchive = document.getElementById("btnBulkArchive");
            if (btnBulkArchive) {
                btnBulkArchive.addEventListener("click", function() {
                    const ids = Array.from(document.querySelectorAll(".user-check:checked")).map(box => box.value);
                    if (ids.length === 0) {
                        alert("Please select at least one user record.");
                        return;
                    }

                    const currentAction = this.getAttribute("data-action"); // 'archive' or 'recover'
                    const promptVerb = (currentAction === "recover") ? "recover/restore" : "archive";

                    if (confirm(`Are you sure you want to ${promptVerb} the ${ids.length} selected user profiles?`)) {
                        const form = document.createElement("form");
                        form.method = "POST";
                        form.action = "modules/process_bulk_archive.php";

                        // Pass operational task metadata indicators
                        const actionInput = document.createElement("input");
                        actionInput.type = "hidden";
                        actionInput.name = "operation_task";
                        actionInput.value = currentAction;
                        form.appendChild(actionInput);

                        const signatureInput = document.createElement("input");
                        signatureInput.type = "hidden";
                        signatureInput.name = "bulk_archive";
                        signatureInput.value = "1";
                        form.appendChild(signatureInput);

                        // Build values indices arrays nodes payload loops
                        ids.forEach(id => {
                            const input = document.createElement("input");
                            input.type = "hidden";
                            input.name = "selected_users[]";
                            input.value = id;
                            form.appendChild(input);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });
    </script>