<?php
/**
 * API Endpoint: Dashboard Statistics & Recents
 * Supports: GET
 */

require_once __DIR__ . '/../response.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET') {
    sendError('Method not allowed.', 405);
}

try {
    // 1. Stat metrics
    $totalResidents = (int)$pdo->query("SELECT COUNT(*) FROM residents")->fetchColumn();
    $activePrograms = (int)$pdo->query("SELECT COUNT(*) FROM programs WHERE program_status IN ('Upcoming', 'Ongoing')")->fetchColumn();
    $totalRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
    $totalServices = (int)$pdo->query("SELECT COUNT(*) FROM service_records")->fetchColumn();

    // 2. Recent Programs (Top 4)
    $stmt = $pdo->query("
        SELECT 
            p.*,
            COUNT(r.registration_id) AS registration_count
        FROM programs p
        LEFT JOIN registrations r ON p.program_id = r.program_id
        GROUP BY p.program_id
        ORDER BY p.start_date DESC
        LIMIT 4
    ");
    $recentPrograms = $stmt->fetchAll();
    foreach ($recentPrograms as &$p) {
        $p['start_date_formatted'] = date('M d, Y', strtotime($p['start_date']));
        $p['status_class'] = strtolower($p['program_status']);
    }

    // 3. Recent Registrations (Top 5)
    $stmt = $pdo->query("
        SELECT 
            reg.registration_id,
            reg.registration_date,
            r.first_name,
            r.last_name,
            p.program_name
        FROM registrations reg
        JOIN residents r ON reg.resident_id = r.resident_id
        JOIN programs p ON reg.program_id = p.program_id
        ORDER BY reg.registration_date DESC
        LIMIT 5
    ");
    $recentRegistrations = $stmt->fetchAll();
    foreach ($recentRegistrations as &$reg) {
        $reg['resident_name'] = trim($reg['first_name'] . ' ' . $reg['last_name']);
        $initials = '';
        $parts = explode(' ', $reg['resident_name']);
        if (count($parts) >= 2) {
            $initials = strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1));
        } else {
            $initials = strtoupper(substr($reg['resident_name'], 0, 2));
        }
        $reg['initials'] = $initials;
        $reg['date_formatted'] = date('M d', strtotime($reg['registration_date']));
    }

    sendJson([
        'success' => true,
        'data' => [
            'metrics' => [
                'total_residents'     => $totalResidents,
                'active_programs'     => $activePrograms,
                'total_registrations' => $totalRegistrations,
                'total_services'      => $totalServices
            ],
            'recent_programs'      => $recentPrograms,
            'recent_registrations' => $recentRegistrations
        ]
    ]);
} catch (PDOException $e) {
    sendError('Failed to fetch dashboard metrics: ' . $e->getMessage(), 500);
}
