<?php
/**
 * Page: Service Records
 * Barangay Health Outreach Management System
 */

$serviceRecords = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("
            SELECT 
                s.service_id,
                s.service_description,
                s.service_date,
                s.recorded_at,
                r.resident_id,
                r.first_name,
                r.last_name,
                p.program_name
            FROM service_records s
            JOIN registrations reg ON s.registration_id = reg.registration_id
            JOIN residents r ON reg.resident_id = r.resident_id
            JOIN programs p ON reg.program_id = p.program_id
            ORDER BY s.service_date DESC
        ");
        $serviceRecords = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Service records query error: " . $e->getMessage());
    }
}
$serviceCount = count($serviceRecords);
?>
<section class="page" id="services-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">SERVICE DOCUMENTATION</p>
      <h1>Service Records</h1>
      <p class="subtitle">Document clinical consultations, treatments, and medicines dispensed during outreach.</p>
    </div>
    <button class="button primary" id="add-service">＋ Record service</button>
  </div>

  <section class="panel">
    <div class="toolbar">
      <div class="search-box">
        <span>⌕</span>
        <input id="service-search" type="search" placeholder="Search service descriptions, programs, or beneficiaries...">
      </div>
      <span class="muted"><?= $serviceCount ?> service entries</span>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>BENEFICIARY</th>
            <th>OUTREACH PROGRAM</th>
            <th>SERVICE DETAILS & NOTES</th>
            <th>DATE SERVED</th>
          </tr>
        </thead>
        <tbody id="service-rows">
          <?php if (empty($serviceRecords)): ?>
            <tr><td colspan="4" class="muted" style="text-align:center; padding: 25px;">No service records found.</td></tr>
          <?php else: ?>
            <?php foreach ($serviceRecords as $rec): 
              $code = 'RES-' . str_pad($rec['resident_id'], 4, '0', STR_PAD_LEFT);
              $name = trim($rec['first_name'] . ' ' . $rec['last_name']);
              $dateFormatted = date('M d, Y h:i A', strtotime($rec['service_date']));
            ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($name) ?></strong>
                  <small><?= htmlspecialchars($code) ?></small>
                </td>
                <td><?= htmlspecialchars($rec['program_name']) ?></td>
                <td><?= htmlspecialchars($rec['service_description']) ?></td>
                <td><?= htmlspecialchars($dateFormatted) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>
