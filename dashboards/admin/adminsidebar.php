<aside>
      <div class="brand">
          EduManage
          <span>System Administrator Panel</span>
      </div>
      <ul class="menu">
          <li class="menu-item active">
              <a href="admin_dashboard.php?page=users"><?php include ("../../icons/box-icon.html") ?> <span>User Management</span></a>
          </li>
          <li class="menu-item">
              <a href="admin_dashboard.php?page=audit-trail"><?php include ("../../icons/truck-icon.html") ?> <span>Audit Trail</span></a>
          </li>
          <li class="menu-item">
              <a href="admin_dashboard.php?page=archive"><?php include ("../../icons/cycle-icon.html") ?> <span>Archive</span></a>
          </li>
      </ul>
      <div class="btn-group dropend mb-2">
          <a role="button" class="user-profile dropdown-toggle text-white text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="avatar ">JR</div>
              <div class="user-info" style="text-align: left;">
                  <span style="font-size: 0.9rem;">J. Legaspi</span>
                  <span style="font-size: 0.75rem; color: var(--text-muted);">System Admin</span>
              </div>
          </a>
          <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="../../auth/logout.php">Logout</a></li>
  </aside>