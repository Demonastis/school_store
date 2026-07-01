<?php
// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if a user is authorized to access a page.
 * If unauthorized, it redirects them to the login screen or back to their dashboard.
 *
 * @param array $allowed_roles Array of role strings allowed to access the page (e.g., ['Admin', 'Manager'])
 */
function check_access($allowed_roles) {
    // 1. Check if the user is even logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        // Destroy any corrupted session remnants and force a clean login
        session_unset();
        session_destroy();
        header("Location: http://" . $_SERVER['HTTP_HOST'] . "/school_store/login.php?error=Please log in first");
        exit();
    }

    // 2. Validate if the logged-in user's role matches the page's permitted roles
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        // Redirect to a landing area or login page with an access denied warning
        header("Location: http://" . $_SERVER['HTTP_HOST'] . "/school_store/login.php?error=Access Denied: Unauthorized Role");
        exit();
    }
}
?>
