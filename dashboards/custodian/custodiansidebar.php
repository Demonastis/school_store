<aside>
        <div class="brand">
            EduManage
            <span>Custodian Panel</span>
        </div>
        <ul class="menu">
            <li class="menu-item active">
                <a href="custodian_dashboard.php?page=dashboard"><?php include ("../../icons/box-icon.html") ?> <span>Order Fulfillment</span></a>
            </li>
            <li class="menu-item">
                <a href="custodian_dashboard.php?page=deliveries"><?php include ("../../icons/truck-icon.html") ?> <span>Inbound Deliveries</span></a>
            </li>
            <li class="menu-item">
                <a href="custodian_dashboard.php?page=stock-count"><?php include ("../../icons/cycle-icon.html") ?> <span>Stock Counts</span></a>
            </li>
            <li class="menu-item">
                <a href="custodian_dashboard.php?page=labels"><?php include ("../../icons/print-icon.html") ?> <span>Print Labels</span></a>
            </li>
        </ul>
        <div class="btn-group dropend mb-2">
            <a role="button" class="user-profile dropdown-toggle text-white text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar ">JR</div>
                <div class="user-info" style="text-align: left;">
                    <span style="font-size: 0.9rem;">J. Legaspi</span>
                    <span style="font-size: 0.75rem; color: var(--text-muted);">Warehouse Custodian</span>
                </div>
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="../../auth/logout.php">Logout</a></li>
    </aside>