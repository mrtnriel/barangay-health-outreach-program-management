<?php
/**
 * Logout Endpoint
 * Barangay Health Outreach Management System
 */

require_once __DIR__ . '/../includes/auth.php';

// Clear session variables
$_SESSION = [];

// Clear session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

// Redirect to login with logout notice
header('Location: login.php?logged_out=1');
exit;
