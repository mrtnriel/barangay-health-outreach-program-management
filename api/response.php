<?php
/**
 * Shared API Response Utilities
 * Barangay Health Outreach Management System
 */

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
}

// Include Database Configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Send JSON response and exit
 */
function sendJson(array $data, int $statusCode = 200): void {
    if (!headers_sent()) {
        http_response_code($statusCode);
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Send standard JSON error response and exit
 */
function sendError(string $message, int $statusCode = 400, array $details = []): void {
    sendJson([
        'success' => false,
        'error'   => $message,
        'details' => $details
    ], $statusCode);
}

/**
 * Parse incoming JSON or Form Data payload
 */
function getRequestPayload(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        return is_array($json) ? $json : [];
    }
    return $_POST;
}
