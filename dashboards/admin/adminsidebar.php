<aside>
    <div class="brand">
        EduManage
        <span>System Administrator Panel</span>
    </div>
    <ul class="menu">
        <li class="menu-item active">
            <a href="admin_dashboard.php?page=users"><?php include("../../icons/box-icon.html") ?> <span>User Management</span></a>
        </li>
        <li class="menu-item">
            <a href="admin_dashboard.php?page=audit-trail"><?php include("../../icons/truck-icon.html") ?> <span>Audit Trail</span></a>
        </li>
        <li class="menu-item">
            <a href="admin_dashboard.php?page=archive"><?php include("../../icons/cycle-icon.html") ?> <span>Archive</span></a>
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