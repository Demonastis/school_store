<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole($roles = []) {
    if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], $roles)) {
        header("Location: unauthorized.php");
        exit;
    }
}
