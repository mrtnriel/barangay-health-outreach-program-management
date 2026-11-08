<?php
/**
 * API Endpoint: Change Password
 * Supports: POST
 */

require_once __DIR__ . '/../response.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Method not allowed.', 405);
}

if (!isLoggedIn()) {
    sendError('Unauthorized. Please log in first.', 401);
}

$data = getRequestPayload();
$currentPassword = $data['current_password'] ?? '';
$newPassword     = $data['new_password'] ?? '';
$confirmPassword = $data['confirm_password'] ?? '';

if ($currentPassword === '' || $newPassword === '') {
    sendError('Both current and new password are required.', 422);
}

if (strlen($newPassword) < 6) {
    sendError('New password must be at least 6 characters.', 422);
}

if ($newPassword !== $confirmPassword) {
    sendError('New password and confirmation do not match.', 422);
}

$user = getCurrentUser();
$userId = $user['user_id'];

try {
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $currentHash = $stmt->fetchColumn();

    if (!$currentHash || !password_verify($currentPassword, $currentHash)) {
        sendError('Incorrect current password.', 403);
    }

    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
    $uStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $uStmt->execute([$newHash, $userId]);

    sendJson([
        'success' => true,
        'message' => 'Password changed successfully.'
    ]);
} catch (PDOException $e) {
    sendError('Database error: ' . $e->getMessage(), 500);
}
