<?php
/**
 * API Endpoint: Personnel & System Users
 * Supports: GET (list / search), POST (create staff/BHW account)
 */

require_once __DIR__ . '/../response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');

    try {
        $sql = "SELECT user_id, full_name, username, role, created_at FROM users WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (full_name LIKE :search OR username LIKE :search OR role LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY user_id ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $users = $stmt->fetchAll();

        foreach ($users as &$u) {
            $u['role_class'] = match ($u['role']) {
                'Admin' => 'completed',
                'Staff' => 'ongoing',
                'BHW'   => 'upcoming',
                default => 'ongoing'
            };
        }

        sendJson([
            'success' => true,
            'count'   => count($users),
            'data'    => $users
        ]);
    } catch (PDOException $e) {
        sendError('Database query failed: ' . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $data = getRequestPayload();

    $fullName = trim($data['full_name'] ?? '');
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');
    $role     = trim($data['role'] ?? 'Staff');

    $errors = [];
    if ($fullName === '') $errors[] = 'Full name is required.';
    if ($username === '') $errors[] = 'Username is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if (!in_array($role, ['Admin', 'Staff', 'BHW'], true)) $errors[] = 'Role must be Admin, Staff, or BHW.';

    if (!empty($errors)) {
        sendError('Validation failed.', 422, $errors);
    }

    try {
        // Check if username already exists
        $check = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            sendError('Username is already taken.', 409);
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $pdo->prepare("
            INSERT INTO users (full_name, username, password, role)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$fullName, $username, $hash, $role]);

        $newId = (int)$pdo->lastInsertId();

        sendJson([
            'success' => true,
            'message' => 'Personnel account created successfully.',
            'user_id' => $newId,
            'data' => [
                'user_id'    => $newId,
                'full_name'  => $fullName,
                'username'   => $username,
                'role'       => $role,
                'role_class' => match ($role) {
                    'Admin' => 'completed',
                    'Staff' => 'ongoing',
                    'BHW'   => 'upcoming',
                    default => 'ongoing'
                }
            ]
        ], 201);
    } catch (PDOException $e) {
        sendError('Failed to create personnel account: ' . $e->getMessage(), 500);
    }
} else {
    sendError('Method not allowed.', 405);
}
