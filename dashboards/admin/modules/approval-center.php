<?php
// Secure inclusion check
if (!defined('Admin')) { requireRole(['Admin']); }

// Grab user list parameters
$queue_input = isset($_GET['queue']) ? $_GET['queue'] : '';
$userIds = array_filter(explode(',', $queue_input), 'is_numeric');

if (empty($userIds)) {
    echo "<div class='alert alert-info'>No user records are currently queued for review. <a href='admin_dashboard.php?page=users' class='alert-link'>Return to user view</a></div>";
    return;
}

// Track current queue sequence index
$currentIndex = isset($_GET['index']) ? (int)$_GET['index'] : 0;

// Enforce boundary safety
if ($currentIndex < 0 || $currentIndex >= count($userIds)) {
    echo "<div class='alert alert-success'>🎉 Processing queue complete! All actions recorded. <a href='admin_dashboard.php?page=users' class='alert-link'>Return to records list</a></div>";
    return;
}

$target_user_id = $userIds[$currentIndex];

// Fetch matching profile details
$query = "SELECT user_id, first_name, last_name, username, email, role, created_at, profile_picture FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $target_user_id);
$stmt->execute();
$current_profile = $stmt->get_result()->fetch_assoc();

if (!$current_profile) {
    // Gracefully handle deleted entries mid-queue
    $nextIdx = $currentIndex + 1;
    echo "<script>window.location.href='admin_dashboard.php?page=approval-center&queue={$queue_input}&index={$nextIdx}';</script>";
    exit;
}
?>

<div class="card shadow border-0 mx-auto my-4" style="max-width: 600px;">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Batch Account Moderation</h5>
        <span class="badge bg-primary">Record <?php echo ($currentIndex + 1); ?> of <?php echo count($userIds); ?></span>
    </div>
    <div class="card-body text-center p-4">
        <!-- Profile Avatar View -->
        <div class="mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%; overflow: hidden; border: 3px solid #f8f9fa;">
            <img src="../../uploads/profile_pictures/<?php echo htmlspecialchars($current_profile['profile_picture']); ?>" class="w-100 h-100 object-fit-cover" alt="Avatar">
        </div>

        <h3 class="mb-1"><?php echo htmlspecialchars($current_profile['first_name'] . ' ' . $current_profile['last_name']); ?></h3>
        <p class="text-muted mb-3">@<?php echo htmlspecialchars($current_profile['username']); ?> &middot; <span class="badge bg-secondary"><?php echo htmlspecialchars($current_profile['role']); ?></span></p>

        <table class="table table-sm table-bordered text-start mb-4">
            <tr>
                <th class="bg-light w-25">Email:</th>
                <td><?php echo htmlspecialchars($current_profile['email']); ?></td>
            </tr>
            <tr>
                <th class="bg-light">Registered:</th>
                <td><?php echo date('F j, Y, g:i a', strtotime($current_profile['created_at'])); ?></td>
            </tr>
        </table>

        <!-- Actions Processing Form -->
        <form action="modules/process_approval.php" method="POST" class="d-flex justify-content-center gap-3">
            <!-- Queue state inputs to forward variables reliably -->
            <input type="hidden" name="user_id" value="<?php echo $target_user_id; ?>">
            <input type="hidden" name="queue" value="<?php echo htmlspecialchars($queue_input); ?>">
            <input type="hidden" name="index" value="<?php echo $currentIndex; ?>">

            <button type="submit" name="decision" value="rejected" class="btn btn-danger btn-lg px-4 shadow-sm">
                ❌ Reject & Block
            </button>
            <button type="submit" name="decision" value="approved" class="btn btn-success btn-lg px-4 shadow-sm">
                ✅ Approve Access
            </button>
        </form>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <a href="admin_dashboard.php?page=users" class="btn btn-sm btn-outline-secondary">🚪 Leave Review</a>
        <div>
            <?php if ($currentIndex > 0): ?>
                <a href="admin_dashboard.php?page=approval-center&queue=<?php echo $queue_input; ?>&index=<?php echo ($currentIndex - 1); ?>" class="btn btn-sm btn-secondary">◀ Back</a>
            <?php endif; ?>
            
            <a href="admin_dashboard.php?page=approval-center&queue=<?php echo $queue_input; ?>&index=<?php echo ($currentIndex + 1); ?>" class="btn btn-sm btn-light">Skip ▶</a>
        </div>
    </div>
</div>
