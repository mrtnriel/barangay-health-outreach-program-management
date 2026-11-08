<?php
/**
 * API Endpoint: Program Registrations
 * Supports: GET (list / filter), POST (create)
 */

require_once __DIR__ . '/../response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $programId  = isset($_GET['program_id']) ? (int)$_GET['program_id'] : 0;
    $residentId = isset($_GET['resident_id']) ? (int)$_GET['resident_id'] : 0;
    $search     = trim($_GET['search'] ?? '');

    try {
        $sql = "
            SELECT 
                reg.registration_id,
                reg.attendance_status,
                reg.registration_date,
                r.resident_id,
                r.first_name,
                r.last_name,
                r.address,
                r.contact_number,
                p.program_id,
                p.program_name,
                p.program_type
            FROM registrations reg
            JOIN residents r ON reg.resident_id = r.resident_id
            JOIN programs p ON reg.program_id = p.program_id
            WHERE 1=1
        ";
        $params = [];

        if ($programId > 0) {
            $sql .= " AND reg.program_id = :prog_id";
            $params[':prog_id'] = $programId;
        }

        if ($residentId > 0) {
            $sql .= " AND reg.resident_id = :res_id";
            $params[':res_id'] = $residentId;
        }

        if ($search !== '') {
            $sql .= " AND (r.first_name LIKE :search OR r.last_name LIKE :search OR p.program_name LIKE :search OR r.address LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY reg.registration_date DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $registrations = $stmt->fetchAll();

        foreach ($registrations as &$reg) {
            $reg['resident_code'] = 'RES-' . str_pad($reg['resident_id'], 4, '0', STR_PAD_LEFT);
            $reg['resident_name'] = trim($reg['first_name'] . ' ' . $reg['last_name']);
            $reg['registration_date_formatted'] = date('M d, Y', strtotime($reg['registration_date']));
            $reg['status_class'] = match ($reg['attendance_status']) {
                'Attended'   => 'ongoing',
                'Registered' => 'upcoming',
                'Completed'  => 'completed',
                default      => 'upcoming'
            };
        }

        sendJson([
            'success' => true,
            'count'   => count($registrations),
            'data'    => $registrations
        ]);
    } catch (PDOException $e) {
        sendError('Database query failed: ' . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $data = getRequestPayload();
    $action = $data['action'] ?? 'create';

    if ($action === 'update_status') {
        $registrationId = (int)($data['registration_id'] ?? 0);
        $status = trim($data['attendance_status'] ?? 'Attended');

        if ($registrationId <= 0 || !in_array($status, ['Registered', 'Attended', 'Absent', 'Cancelled'], true)) {
            sendError('Invalid registration_id or status.', 422);
        }

        try {
            $stmt = $pdo->prepare("UPDATE registrations SET attendance_status = ? WHERE registration_id = ?");
            $stmt->execute([$status, $registrationId]);

            sendJson([
                'success' => true,
                'message' => 'Attendance status updated successfully.',
                'attendance_status' => $status,
                'status_class' => match ($status) {
                    'Attended'   => 'ongoing',
                    'Registered' => 'upcoming',
                    default      => 'completed'
                }
            ]);
        } catch (PDOException $e) {
            sendError('Failed to update status: ' . $e->getMessage(), 500);
        }
    }

    $residentId = (int)($data['resident_id'] ?? 0);
    $programId  = (int)($data['program_id'] ?? 0);
    $status     = trim($data['attendance_status'] ?? 'Registered');

    if ($residentId <= 0 || $programId <= 0) {
        sendError('Both resident_id and program_id are required.', 422);
    }

    if (!in_array($status, ['Registered', 'Attended', 'Absent', 'Cancelled'], true)) {
        $status = 'Registered';
    }

    try {
        // Check for existing duplicate registration
        $check = $pdo->prepare("SELECT registration_id FROM registrations WHERE resident_id = ? AND program_id = ?");
        $check->execute([$residentId, $programId]);
        if ($check->fetch()) {
            sendError('This resident is already registered for this program.', 409);
        }

        $stmt = $pdo->prepare("
            INSERT INTO registrations (resident_id, program_id, attendance_status)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$residentId, $programId, $status]);

        $newId = (int)$pdo->lastInsertId();

        sendJson([
            'success'         => true,
            'message'         => 'Resident registered for program successfully.',
            'registration_id' => $newId
        ], 201);
    } catch (PDOException $e) {
        sendError('Failed to register beneficiary: ' . $e->getMessage(), 500);
    }
} else {
    sendError('Method not allowed.', 405);
}
