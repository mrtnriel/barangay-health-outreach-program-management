<?php
/**
 * API Endpoint: Appointments
 * Supports: GET (list / search), POST (create)
 */

require_once __DIR__ . '/../response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');

    try {
        $sql = "
            SELECT 
                a.*,
                r.resident_id,
                r.first_name,
                r.last_name,
                r.address,
                r.contact_number
            FROM appointments a
            JOIN residents r ON a.resident_id = r.resident_id
            WHERE 1=1
        ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (r.first_name LIKE :search OR r.last_name LIKE :search OR a.purpose LIKE :search OR a.physician_name LIKE :search)";
            $params[':search'] = "%{$search}%";
        }

        $sql .= " ORDER BY a.appointment_date ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $appointments = $stmt->fetchAll();

        foreach ($appointments as &$apt) {
            $apt['resident_code'] = 'RES-' . str_pad($apt['resident_id'], 4, '0', STR_PAD_LEFT);
            $apt['resident_name'] = trim($apt['first_name'] . ' ' . $apt['last_name']);
            $apt['date_formatted'] = date('M d, Y h:i A', strtotime($apt['appointment_date']));
            $apt['status_class'] = match ($apt['status']) {
                'Completed' => 'completed',
                'Cancelled' => 'completed',
                default     => 'upcoming'
            };
        }

        sendJson([
            'success' => true,
            'count'   => count($appointments),
            'data'    => $appointments
        ]);
    } catch (PDOException $e) {
        sendError('Database query failed: ' . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $data = getRequestPayload();
    $action = $data['action'] ?? 'create';

    if ($action === 'update_status') {
        $appointmentId = (int)($data['appointment_id'] ?? 0);
        $status = trim($data['status'] ?? 'Completed');

        if ($appointmentId <= 0 || !in_array($status, ['Scheduled', 'Completed', 'Cancelled'], true)) {
            sendError('Invalid appointment_id or status.', 422);
        }

        try {
            $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
            $stmt->execute([$status, $appointmentId]);

            sendJson([
                'success' => true,
                'message' => "Appointment marked as {$status}.",
                'status' => $status,
                'status_class' => match ($status) {
                    'Completed' => 'completed',
                    'Cancelled' => 'completed',
                    default     => 'upcoming'
                }
            ]);
        } catch (PDOException $e) {
            sendError('Failed to update appointment: ' . $e->getMessage(), 500);
        }
    }

    $residentId    = (int)($data['resident_id'] ?? 0);
    $date          = trim($data['appointment_date'] ?? '');
    $purpose       = trim($data['purpose'] ?? '');
    $physicianName = trim($data['physician_name'] ?? 'Dr. Maria Ramos');

    $errors = [];
    if ($residentId <= 0) $errors[] = 'Please select a resident.';
    if ($date === '') $errors[] = 'Appointment date and time is required.';
    if ($purpose === '') $errors[] = 'Purpose of appointment is required.';

    if (!empty($errors)) {
        sendError('Validation failed.', 422, $errors);
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO appointments (resident_id, appointment_date, purpose, physician_name, status)
            VALUES (?, ?, ?, ?, 'Scheduled')
        ");
        $stmt->execute([
            $residentId,
            date('Y-m-d H:i:s', strtotime($date)),
            $purpose,
            $physicianName ?: 'Dr. Maria Ramos'
        ]);

        $newId = (int)$pdo->lastInsertId();

        // Get resident info for response
        $rStmt = $pdo->prepare("SELECT first_name, last_name, address FROM residents WHERE resident_id = ?");
        $rStmt->execute([$residentId]);
        $resident = $rStmt->fetch();

        $fullName = $resident ? trim($resident['first_name'] . ' ' . $resident['last_name']) : 'Resident';

        sendJson([
            'success'        => true,
            'message'        => 'Appointment booked successfully.',
            'appointment_id' => $newId,
            'data' => [
                'appointment_id' => $newId,
                'resident_id'    => $residentId,
                'resident_code'  => 'RES-' . str_pad($residentId, 4, '0', STR_PAD_LEFT),
                'resident_name'  => $fullName,
                'date_formatted' => date('M d, Y h:i A', strtotime($date)),
                'purpose'        => $purpose,
                'physician_name' => $physicianName ?: 'Dr. Maria Ramos',
                'status'         => 'Scheduled',
                'status_class'   => 'upcoming'
            ]
        ], 201);
    } catch (PDOException $e) {
        sendError('Failed to book appointment: ' . $e->getMessage(), 500);
    }
} else {
    sendError('Method not allowed.', 405);
}
