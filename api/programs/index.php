<?php
/**
 * API Endpoint: Health Programs
 * Supports: GET (list / filter), POST (create)
 */

require_once __DIR__ . '/../response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');

    try {
        $sql = "
            SELECT 
                p.*,
                COUNT(r.registration_id) AS registration_count
            FROM programs p
            LEFT JOIN registrations r ON p.program_id = r.program_id
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (p.program_name LIKE :search OR p.venue LIKE :search OR p.program_type LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        if ($status !== '' && in_array($status, ['Upcoming', 'Ongoing', 'Completed', 'Cancelled'], true)) {
            $sql .= " AND p.program_status = :status";
            $params[':status'] = $status;
        }

        $sql .= " GROUP BY p.program_id ORDER BY p.start_date DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $programs = $stmt->fetchAll();

        foreach ($programs as &$p) {
            $p['start_date_formatted'] = date('M d, Y', strtotime($p['start_date']));
            $p['status_class'] = strtolower($p['program_status']);
        }

        sendJson([
            'success' => true,
            'count'   => count($programs),
            'data'    => $programs
        ]);
    } catch (PDOException $e) {
        sendError('Database query failed: ' . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $data = getRequestPayload();

    $programName = trim($data['program_name'] ?? $data['name'] ?? '');
    $description = trim($data['description'] ?? '');
    $programType = trim($data['program_type'] ?? $data['type'] ?? 'General Outreach');
    $venue       = trim($data['venue'] ?? 'Barangay Health Center');
    $startDate   = trim($data['start_date'] ?? '');
    $endDate     = !empty($data['end_date']) ? trim($data['end_date']) : null;
    $status      = trim($data['program_status'] ?? $data['status'] ?? 'Upcoming');

    $errors = [];
    if ($programName === '') $errors[] = 'Program name is required.';
    if ($startDate === '')   $errors[] = 'Start date/schedule is required.';
    if (!in_array($status, ['Upcoming', 'Ongoing', 'Completed', 'Cancelled'], true)) {
        $status = 'Upcoming';
    }

    if (!empty($errors)) {
        sendError('Validation failed.', 422, $errors);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO programs (program_name, description, program_type, venue, start_date, end_date, program_status)
            VALUES (:name, :desc, :type, :venue, :start, :end, :status)
        ");

        $stmt->execute([
            ':name'   => $programName,
            ':desc'   => $description !== '' ? $description : null,
            ':type'   => $programType,
            ':venue'  => $venue,
            ':start'  => date('Y-m-d H:i:s', strtotime($startDate)),
            ':end'    => $endDate ? date('Y-m-d H:i:s', strtotime($endDate)) : null,
            ':status' => $status
        ]);

        $newId = (int)$pdo->lastInsertId();

        sendJson([
            'success'    => true,
            'message'    => 'Health program created successfully.',
            'program_id' => $newId,
            'data' => [
                'program_id'           => $newId,
                'program_name'         => $programName,
                'description'          => $description,
                'program_type'         => $programType,
                'venue'                => $venue,
                'start_date'           => $startDate,
                'start_date_formatted' => date('M d, Y', strtotime($startDate)),
                'program_status'       => $status,
                'registration_count'   => 0
            ]
        ], 201);
    } catch (PDOException $e) {
        sendError('Failed to create health program: ' . $e->getMessage(), 500);
    }
} else {
    sendError('Method not allowed.', 405);
}
