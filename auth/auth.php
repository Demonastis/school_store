<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
function requireRole($roles = []) {

    requireLogin();

    if (
        !isset($_SESSION['role']) ||
        !in_array($_SESSION['role'], $roles)
    ) {
        header("Location: unauthorized.php");
        exit;
    }
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
         if (
        isset($_SESSION['is_archived']) &&
        $_SESSION['is_archived'] == 1
    ) {
        // Destroy the session
        session_unset();
        session_destroy();

        header("Location: login.php?error=archived");
        exit;
    }

    if (
        isset($_SESSION['approval_status']) &&
        $_SESSION['approval_status'] !== 'approved'
    ) {
        // Destroy the session
        session_unset();
        session_destroy();

        header("Location: login.php?error=not_approved");
        exit;
    }
}


/**
 * Require the user to have one of the specified roles.
 */

}
?>