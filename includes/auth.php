<?php
/**
 * Authentication and Session Management Helper
 * Barangay Health Outreach Management System
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is currently authenticated
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Get details of the currently authenticated user
 */
function getCurrentUser(): ?array {
    if (!isLoggedIn()) {
        return null;
    }

    return [
        'user_id'   => $_SESSION['user_id'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? 'Barangay Staff',
        'username'  => $_SESSION['username'] ?? 'staff',
        'role'      => $_SESSION['role'] ?? 'Staff',
    ];
}

/**
 * Get user initials for avatar displays (e.g. "BS" for "Barangay Staff")
 */
function getUserInitials(?string $name = null): string {
    $name = $name ?? (getCurrentUser()['full_name'] ?? 'Barangay Staff');
    $parts = explode(' ', trim($name));
    if (count($parts) >= 2) {
        return strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
    }
    return strtoupper(substr($name, 0, 2));
}

/**
 * Enforce authentication requirement
 */
function requireAuth(string $redirectUrl = 'login.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirectUrl");
        exit;
    }
}

/**
 * Enforce authorized roles
 */
function requireRole(array $allowedRoles): void {
    $user = getCurrentUser();
    if (!$user || !in_array($user['role'], $allowedRoles, true)) {
        http_response_code(403);
        die("Access Denied: You do not have permission to view this page.");
    }
}
