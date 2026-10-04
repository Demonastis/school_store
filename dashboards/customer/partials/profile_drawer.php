<!-- PROFILE DRAWER -->
<div
    id="profilePanel"
    style="display:none; position:fixed; top:0; right:0; width:360px; max-width:100%; height:100%; background:#fff; box-shadow:-10px 0 25px rgba(0,0,0,.15); z-index:100000; padding:25px; box-sizing:border-box; overflow-y:auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #f1f5f9; padding-bottom:12px; margin-bottom:20px;">
        <h3 style="margin:0; font-size:1.1rem; font-weight:600;">My Profile</h3>

        <button
            type="button"
            onclick="document.getElementById('profilePanel').style.display='none';"
            style="background:none; border:none; font-size:1.2rem; cursor:pointer; color:#94a3b8;">
            ✕
        </button>
    </div>

    <div style="text-align:center; margin-bottom:24px;">
        <?php if (!empty($user['profile_picture']) && $user['profile_picture'] !== 'default.jpg'): ?>
            <img
                src="<?= htmlspecialchars($user['profile_picture']) ?>"
                alt="Profile picture"
                style="width:90px; height:90px; border-radius:50%; object-fit:cover;">
        <?php else: ?>
            <div style="width:90px; height:90px; border-radius:50%; background:#e2e8f0; display:inline-flex; align-items:center; justify-content:center; font-size:2.5rem;">
                👤
            </div>
        <?php endif; ?>

        <h3 style="margin:12px 0 4px; color:#1e293b;">
            <?= htmlspecialchars($user_name) ?>
        </h3>

        <span style="font-size:.8rem; color:#64748b;">
            <?= htmlspecialchars($user['role']) ?>
        </span>
    </div>

    <div style="display:flex; flex-direction:column; gap:12px;">
        <div>
            <label style="display:block; font-size:.75rem; color:#64748b; margin-bottom:4px;">Username</label>
            <div style="padding:10px 12px; background:#f8fafc; border-radius:6px;">
                <?= htmlspecialchars($user['username']) ?>
            </div>
        </div>

        <div>
            <label style="display:block; font-size:.75rem; color:#64748b; margin-bottom:4px;">Email</label>
            <div style="padding:10px 12px; background:#f8fafc; border-radius:6px;">
                <?= htmlspecialchars($user['email']) ?>
            </div>
        </div>

        <div>
            <label style="display:block; font-size:.75rem; color:#64748b; margin-bottom:4px;">Name</label>
            <div style="padding:10px 12px; background:#f8fafc; border-radius:6px;">
                <?= htmlspecialchars($user_name) ?>
            </div>
        </div>
        <div>
            <label style="display:block; font-size:.75rem; color:#64748b; margin-bottom:4px;">Name</label>
            <div style="padding:10px 12px; background:#f8fafc; border-radius:6px;">

                <a class="dropdown-item" href="../../auth/logout.php">Logout</a>

            </div>
        </div>
    </div>
</div>