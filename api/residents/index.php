<?php
/**
 * API Endpoint: Residents
 * Supports: GET (list / search), POST (create)
 */

require_once __DIR__ . '/../response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $search = trim($_GET['search'] ?? '');
    $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $singleId = isset($_GET['resident_id']) ? (int)$_GET['resident_id'] : 0;

    try {
        if ($singleId > 0) {
            $stmt = $pdo->prepare("SELECT * FROM residents WHERE resident_id = ?");
            $stmt->execute([$singleId]);
            $resident = $stmt->fetch();

            if (!$resident) {
                sendError('Resident not found.', 404);
            }

            $resident['resident_code'] = 'RES-' . str_pad($resident['resident_id'], 4, '0', STR_PAD_LEFT);
            $resident['full_name'] = trim($resident['first_name'] . ' ' . ($resident['middle_name'] ? $resident['middle_name'] . ' ' : '') . $resident['last_name']);
            $resident['birth_date_formatted'] = $resident['birth_date'] ? date('M d, Y', strtotime($resident['birth_date'])) : '—';

            // Calculate age
            $age = null;
            if ($resident['birth_date']) {
                $bDate = new DateTime($resident['birth_date']);
                $now = new DateTime();
                $age = $now->diff($bDate)->y;
            }
            $resident['age'] = $age;

            // Fetch registrations
            $rStmt = $pdo->prepare("
                SELECT reg.registration_id, reg.attendance_status, reg.registration_date, p.program_name, p.start_date
                FROM registrations reg
                JOIN programs p ON reg.program_id = p.program_id
                WHERE reg.resident_id = ?
                ORDER BY reg.registration_date DESC
            ");
            $rStmt->execute([$singleId]);
            $resident['registrations'] = $rStmt->fetchAll();

            // Fetch service records
            $sStmt = $pdo->prepare("
                SELECT s.service_id, s.service_description, s.service_date, p.program_name
                FROM service_records s
                JOIN registrations reg ON s.registration_id = reg.registration_id
                JOIN programs p ON reg.program_id = p.program_id
                WHERE reg.resident_id = ?
                ORDER BY s.service_date DESC
            ");
            $sStmt->execute([$singleId]);
            $resident['services'] = $sStmt->fetchAll();

            // Fetch appointments
            $aStmt = $pdo->prepare("
                SELECT appointment_id, appointment_date, purpose, physician_name, status
                FROM appointments
                WHERE resident_id = ?
                ORDER BY appointment_date DESC
            ");
            $aStmt->execute([$singleId]);
            $resident['appointments'] = $aStmt->fetchAll();

            sendJson(['success' => true, 'data' => $resident]);
        }

        if ($search !== '') {
            $stmt = $pdo->prepare("
                SELECT * FROM residents
                WHERE first_name LIKE :search 
                   OR last_name LIKE :search 
                   OR address LIKE :search
                   OR contact_number LIKE :search
                ORDER BY resident_id DESC
                LIMIT :limit
            ");
            $searchTerm = "%{$search}%";
            $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $pdo->prepare("
                SELECT * FROM residents
                ORDER BY resident_id DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
        }

        $residents = $stmt->fetchAll();

        // Format helper fields for frontend
        foreach ($residents as &$r) {
            $r['resident_code'] = 'RES-' . str_pad($r['resident_id'], 4, '0', STR_PAD_LEFT);
            $r['full_name'] = trim($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name']);
            $r['birth_date_formatted'] = $r['birth_date'] ? date('M d, Y', strtotime($r['birth_date'])) : '—';
        }

        sendJson([
            'success' => true,
            'count'   => count($residents),
            'data'    => $residents
        ]);
    } catch (PDOException $e) {
        sendError('Database query failed: ' . $e->getMessage(), 500);
    }
} elseif ($method === 'POST') {
    $data = getRequestPayload();
    $action = $data['action'] ?? 'create';

    $firstName = trim($data['firstName'] ?? $data['first_name'] ?? '');
    $lastName  = trim($data['lastName'] ?? $data['last_name'] ?? '');
    $middleName = trim($data['middleName'] ?? $data['middle_name'] ?? '');
    $birthDate = !empty($data['birthDate'] ?? $data['birth_date'] ?? null) ? ($data['birthDate'] ?? $data['birth_date']) : null;
    $sex       = trim($data['sex'] ?? '');
    $address   = trim($data['address'] ?? '');
    $contact   = trim($data['contact'] ?? $data['contact_number'] ?? '');

    $errors = [];
    if ($firstName === '') $errors[] = 'First name is required.';
    if ($lastName === '')  $errors[] = 'Last name is required.';
    if (!in_array($sex, ['Male', 'Female'], true)) $errors[] = 'Sex must be either Male or Female.';
    if ($address === '')   $errors[] = 'Address is required.';

    if (!empty($errors)) {
        sendError('Validation failed.', 422, $errors);
    }

    if ($action === 'update') {
        $residentId = (int)($data['resident_id'] ?? 0);
        if ($residentId <= 0) sendError('Invalid resident_id.', 422);

        try {
            $stmt = $pdo->prepare("
                UPDATE residents SET
                    first_name = :first_name,
                    last_name = :last_name,
                    middle_name = :middle_name,
                    birth_date = :birth_date,
                    sex = :sex,
                    address = :address,
                    contact_number = :contact_number
                WHERE resident_id = :resident_id
            ");
            $stmt->execute([
                ':first_name'     => $firstName,
                ':last_name'      => $lastName,
                ':middle_name'    => $middleName !== '' ? $middleName : null,
                ':birth_date'     => $birthDate,
                ':sex'            => $sex,
                ':address'        => $address,
                ':contact_number' => $contact !== '' ? $contact : null,
                ':resident_id'    => $residentId
            ]);

            sendJson([
                'success' => true,
                'message' => 'Resident record updated successfully.',
                'data' => [
                    'resident_id'          => $residentId,
                    'resident_code'        => 'RES-' . str_pad($residentId, 4, '0', STR_PAD_LEFT),
                    'full_name'            => trim($firstName . ' ' . $lastName),
                    'birth_date_formatted' => $birthDate ? date('M d, Y', strtotime($birthDate)) : '—',
                    'sex'                  => $sex,
                    'address'              => $address,
                    'contact_number'       => $contact ?: '—'
                ]
            ]);
        } catch (PDOException $e) {
            sendError('Failed to update resident: ' . $e->getMessage(), 500);
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO residents (first_name, last_name, middle_name, birth_date, sex, address, contact_number)
            VALUES (:first_name, :last_name, :middle_name, :birth_date, :sex, :address, :contact_number)
        ");

        $stmt->execute([
            ':first_name'     => $firstName,
            ':last_name'      => $lastName,
            ':middle_name'    => $middleName !== '' ? $middleName : null,
            ':birth_date'     => $birthDate,
            ':sex'            => $sex,
            ':address'        => $address,
            ':contact_number' => $contact !== '' ? $contact : null,
        ]);

        $newId = (int)$pdo->lastInsertId();
        $code  = 'RES-' . str_pad($newId, 4, '0', STR_PAD_LEFT);

        sendJson([
            'success'       => true,
            'message'       => 'Resident record saved successfully.',
            'resident_id'   => $newId,
            'resident_code' => $code,
            'data' => [
                'resident_id'          => $newId,
                'resident_code'        => $code,
                'first_name'           => $firstName,
                'last_name'            => $lastName,
                'full_name'            => trim($firstName . ' ' . $lastName),
                'birth_date'           => $birthDate,
                'birth_date_formatted' => $birthDate ? date('M d, Y', strtotime($birthDate)) : '—',
                'sex'                  => $sex,
                'address'              => $address,
                'contact_number'       => $contact ?: '—'
            ]
        ], 201);
    } catch (PDOException $e) {
        sendError('Failed to save resident: ' . $e->getMessage(), 500);
    }
} else {
    sendError('Method not allowed.', 405);
}
