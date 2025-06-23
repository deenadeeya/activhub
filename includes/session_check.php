<?php
// Centralized session timeout check
// Include this file at the top of all protected pages

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_ic']) || !isset($_SESSION['user_role'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Session timeout check (30 minutes of inactivity)
$timeout_duration = 1800; // 30 minutes in seconds

if (isset($_SESSION['LAST_ACTIVITY'])) {
    if ((time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        // Session timed out
        session_unset();
        session_destroy();
        header("Location: ../auth/login.php?timeout=1");
        exit();
    }
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Optional: Regenerate session ID periodically for security
if (!isset($_SESSION['SESSION_CREATED'])) {
    $_SESSION['SESSION_CREATED'] = time();
} elseif (time() - $_SESSION['SESSION_CREATED'] > 1800) {
    // Regenerate session ID every 30 minutes
    session_regenerate_id(true);
    $_SESSION['SESSION_CREATED'] = time();
}
?>
