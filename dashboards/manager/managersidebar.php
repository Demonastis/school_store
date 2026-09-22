<aside>
        <div class="brand">
            EduManage
            <span>Manager Workspace</span>
        </div>
        <ul class="menu">
            <li class="menu-item">
                <a href="manager_dashboard.php?page=dashboard"><?php include("../../icons/folder-icon.html") ?> <span>Dashboard</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=products"><?php include("../../icons/box-icon.html") ?> <span>Products Inventory</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=orders"><?php include("../../icons/file-icon.html") ?> <span>Purchase Orders</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=purchase"><?php include("../../icons/layer-icon.html") ?> <span>Create PO</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=deliveries"><?php include("../../icons/truck-icon.html") ?> <span>Monitor Deliveries</span></a>
            </li>
            <li class="menu-item">
                <a href="manager_dashboard.php?page=reports"><?php include("../../icons/graph-icon.html") ?> <span>Replenishment Reports</span></a>
            </li>
        </ul>
        <div class="btn-group dropend mb-2">
            <a role="button" class="user-profile dropdown-toggle text-white text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar">
                    <?php if (!empty($user['profile_picture'])): ?>

                        <img
                            src="../../uploads/profile_pictures/<?= htmlspecialchars($user['profile_picture']) ?>"
                            alt="Profile Picture">

                    <?php else: ?>

                        <?= strtoupper(substr($user['username'], 0, 2)) ?>

                    <?php endif; ?>
                </div>

                <div class="user-info" style="text-align: left;">

                    <span style="font-size: 0.9rem;">
                        <?= htmlspecialchars($user['username']) ?>
                    </span>

                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                        <?= htmlspecialchars($user['role']) ?>
                    </span>

                </div>
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item">Profile</a></li>
                <li><a class="dropdown-item" href="../../auth/logout.php">Logout</a></li>
                <!-- Dropdown menu links -->
            </ul>
        </div>
    </aside>