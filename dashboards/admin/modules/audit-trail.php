<?php 
require_once '../../config/db.php';

// 1. Pagination Parameters Configuration
$records_per_page = 10; // Adjust this number to show more or fewer rows per page
$current_page = isset($_GET['log_page']) ? intval($_GET['log_page']) : 1;
if ($current_page < 1) { $current_page = 1; }

// Calculate SQL extraction cursor offset index point
$offset = ($current_page - 1) * $records_per_page;

// 2. Count Total Records inside the Audit Table to determine pagination limits
$count_query = "SELECT COUNT(*) as total FROM audit_logs";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_rows / $records_per_page);

// 3. Fetch ONLY the specific window segment matching the current page
$log_query = "
    SELECT a.log_id, u.first_name, u.last_name, u.role, a.action, a.module, a.description, a.created_at 
    FROM audit_logs a 
    LEFT JOIN users u ON a.user_id = u.user_id 
    ORDER BY a.created_at DESC 
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($log_query);
$stmt->bind_param("ii", $records_per_page, $offset);
$stmt->execute();
$logs_result = $stmt->get_result();

// Get the current page base URL parameters to maintain layout states safely
$current_params = $_GET;
unset($current_params['log_page']); // Clear log page parameter to prevent duplicate string loops
$query_string = http_build_query($current_params);
$base_url = "admin_dashboard.php?" . ($query_string ? $query_string . "&" : "");
?>

<div class="table-container" style="background: white; padding: 15px; border-radius: 8px; border: 1px solid #e0e0e0;">
    <!-- Record Status Informational Bar Details -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; font-size: 0.85rem; color: #6c757d;">
        <span>Showing records <strong><?= min($offset + 1, $total_rows) ?></strong> to <strong><?= min($offset + $records_per_page, $total_rows) ?></strong> of <strong><?= $total_rows ?></strong> entries</span>
        <span>Page <strong><?= $current_page ?></strong> of <strong><?= $total_pages ?: 1 ?></strong></span>
    </div>

    <table class="table table-striped" style="width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
        <thead>
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 10px;">Name</th>
                <th style="padding: 10px;">Role</th>
                <th style="padding: 10px;">Module</th>
                <th style="padding: 10px;">Description</th>
                <th style="padding: 10px;">Timestamp</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs_result->num_rows === 0): ?>
                <tr>
                    <td colspan="5" style="padding: 20px; text-align: center; color: #868e96;">No audit records found inside system storage index.</td>
                </tr>
            <?php else: ?>
                <?php while ($log = $logs_result->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid #e9ecef; transition: background 0.1s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 12px;"><?= htmlspecialchars($log['first_name'] . " " . $log['last_name']) ?: '<em style="color:#bbb;">System Automatons</em>' ?></td>
                        <td style="padding: 12px; font-weight: bold; color: #495057;"><?= htmlspecialchars($log['role'] ?: 'System Engine') ?></td>
                        <td style="padding: 12px;"><span style="background: #e9ecef; padding: 3px 6px; border-radius: 4px; font-size: 0.8rem;"><?= htmlspecialchars($log['module']) ?></span></td>
                        <td style="padding: 12px; width: 40%; color: #333; line-height: 1.4;"><?= htmlspecialchars($log['description']) ?></td>
                        <td style="padding: 12px; color: #6c757d;"><?= date('M d, Y H:i', strtotime($log['created_at'])) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Numeric Pagination Pad Grid Link Component -->
    <?php if ($total_pages > 1): ?>
        <div class="pagination-pad" style="display: flex; justify-content: center; align-items: center; gap: 5px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #f1f3f5;">
            
            <!-- First and Previous Arrow Segment Triggers -->
            <a href="<?= $base_url ?>log_page=1" style="padding: 6px 12px; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #0d6efd; font-size: 0.85rem; <?= $current_page == 1 ? 'pointer-events: none; color: #dee2e6;' : '' ?>">« First</a>
            <a href="<?= $base_url ?>log_page=<?= $current_page - 1 ?>" style="padding: 6px 12px; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #0d6efd; font-size: 0.85rem; <?= $current_page == 1 ? 'pointer-events: none; color: #dee2e6;' : '' ?>">‹ Prev</a>

            <!-- Dynamic Number Pad Loop Segment -->
            <?php 
            // Calculate a floating window range so we don't display 100 buttons at once
            $start_pad = max(1, $current_page - 2);
            $end_pad = min($total_pages, $current_page + 2);
            
            for ($i = $start_pad; $i <= $end_pad; $i++): 
                $is_active = ($i === $current_page);
                $bg_color = $is_active ? '#0d6efd' : 'transparent';
                $text_color = $is_active ? '#fff' : '#0d6efd';
                $border_color = $is_active ? '#0d6efd' : '#dee2e6';
            ?>
                <a href="<?= $base_url ?>log_page=<?= $i ?>" style="padding: 6px 12px; border: 1px solid <?= $border_color ?>; background: <?= $bg_color ?>; color: <?= $text_color ?>; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: <?= $is_active ? 'bold' : 'normal' ?>;">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- Next and Last Arrow Segment Triggers -->
            <a href="<?= $base_url ?>log_page=<?= $current_page + 1 ?>" style="padding: 6px 12px; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #0d6efd; font-size: 0.85rem; <?= $current_page >= $total_pages ? 'pointer-events: none; color: #dee2e6;' : '' ?>">Next ›</a>
            <a href="<?= $base_url ?>log_page=<?= $total_pages ?>" style="padding: 6px 12px; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #0d6efd; font-size: 0.85rem; <?= $current_page >= $total_pages ? 'pointer-events: none; color: #dee2e6;' : '' ?>">Last »</a>
            
        </div>
    <?php endif; ?>
</div>
