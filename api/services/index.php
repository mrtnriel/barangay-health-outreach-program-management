<?php
/**
 * API Endpoint: Service Records
 * Supports: GET (list / filter), POST (create)
 */

require_once __DIR__ . '/../response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');

    try {
        $sql = "
            SELECT 
                s.service_id,
                s.service_description,
                s.service_date,
                s.recorded_at,
                reg.registration_id,
                reg.attendance_status,
                r.resident_id,
                r.first_name,
                r.last_name,
                r.address,
                p.program_id,
                p.program_name
            FROM service_records s
            JOIN registrations reg ON s.registration_id = reg.registration_id
            JOIN residents r ON reg.resident_id = r.resident_id
            JOIN programs p ON reg.program_id = p.program_id
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (r.first_name LIKE :search OR r.last_name LIKE :search OR p.program_name LIKE :search OR s.service_description LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY s.service_date DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();

        foreach ($records as &$rec) {
            $rec['resident_code'] = 'RES-' . str_pad($rec['resident_id'], 4, '0', STR_PAD_LEFT);
            $rec['resident_name'] = trim($rec['first_name'] . ' ' . $rec['last_name']);
            $rec['service_date_formatted'] = date('M d, Y h:i A', strtotime($rec['service_date']));
        }

        sendJson([
            'success' => true,
            'count'   => count($records),
            'data'    => $records
        ]);
    } catch (PDOException $e) {
        sendError('Database query failed: ' . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $data = getRequestPayload();

    $registrationId = (int)($data['registration_id'] ?? 0);
    $description    = trim($data['service_description'] ?? $data['description'] ?? '');
    $serviceDate    = !empty($data['service_date']) ? trim($data['service_date']) : date('Y-m-d H:i:s');

    if ($registrationId <= 0) {
        sendError('registration_id is required.', 422);
    }
    if ($description === '') {
        sendError('service_description is required.', 422);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO service_records (registration_id, service_description, service_date)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $registrationId,
            $description,
            date('Y-m-d H:i:s', strtotime($serviceDate))
        ]);

        $newId = (int)$pdo->lastInsertId();

        // Also update registration attendance_status to 'Attended'
        $update = $pdo->prepare("UPDATE registrations SET attendance_status = 'Attended' WHERE registration_id = ?");
        $update->execute([$registrationId]);

        sendJson([
            'success'    => true,
            'message'    => 'Service record saved successfully.',
            'service_id' => $newId
        ], 201);
    } catch (PDOException $e) {
        sendError('Failed to record service: ' . $e->getMessage(), 500);
    }
} else {
    sendError('Method not allowed.', 405);
}
