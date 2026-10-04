<?php
session_start();
require_once '../../config/db.php';

/**
 * Authentication fallback.
 *
 * Replace this fallback with the application's real authentication middleware
 * when it becomes available.
 */
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 5;
}

$user_id = (int) $_SESSION['user_id'];

/**
 * Load the current user's profile from the users table.
 * The supplied schema defines first/last name, profile picture,
 * email, username, and role on this table.
 */
$user_stmt = $conn->prepare(
    "SELECT user_id, first_name, last_name, profile_picture, email, username, role
     FROM users
     WHERE user_id = ?
       AND is_archived = 0
     LIMIT 1"
);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();

$user = $user_stmt->get_result()->fetch_assoc();

if (!$user) {
    session_unset();
    session_destroy();
    header("Location: ../../login.php?error=" . urlencode("User account not found."));
    exit();
}

$user_name = $user['first_name'] . " " . $user['last_name'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
