<?php
/**
 * Page: Registrations
 * Barangay Health Outreach Management System
 */

$registrations = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("
            SELECT 
                reg.registration_id,
                reg.attendance_status,
                reg.registration_date,
                r.resident_id,
                r.first_name,
                r.last_name,
                r.address,
                p.program_id,
                p.program_name
            FROM registrations reg
            JOIN residents r ON reg.resident_id = r.resident_id
            JOIN programs p ON reg.program_id = p.program_id
            ORDER BY reg.registration_date DESC
        ");
        $registrations = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Registrations query error: " . $e->getMessage());
    }
}
$registrationCount = count($registrations);
?>
<section class="page" id="registrations-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">BENEFICIARY MONITORING</p>
      <h1>Registrations</h1>
      <p class="subtitle">Track residents registered for and attending community outreach programs.</p>
    </div>
    <button class="button primary" id="add-registration">＋ New registration</button>
  </div>

  <section class="panel">
    <div class="toolbar">
      <div class="search-box">
        <span>⌕</span>
        <input id="registration-search" type="search" placeholder="Search resident, program, or address...">
      </div>
      <span class="muted" id="registration-count"><?= $registrationCount ?> registered beneficiaries</span>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>BENEFICIARY</th>
            <th>PROGRAM</th>
            <th>DATE REGISTERED</th>
            <th>ATTENDANCE STATUS</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody id="registration-rows">
          <?php if (empty($registrations)): ?>
            <tr><td colspan="5" class="muted" style="text-align:center; padding: 25px;">No beneficiary registrations found.</td></tr>
          <?php else: ?>
            <?php foreach ($registrations as $reg): 
              $code = 'RES-' . str_pad($reg['resident_id'], 4, '0', STR_PAD_LEFT);
              $name = trim($reg['first_name'] . ' ' . $reg['last_name']);
              $dateFormatted = date('M d, Y', strtotime($reg['registration_date']));
              $statusClass = match ($reg['attendance_status']) {
                  'Attended'   => 'ongoing',
                  'Registered' => 'upcoming',
                  'Completed'  => 'completed',
                  default      => 'upcoming'
              };
            ?>
              <tr data-reg-row-id="<?= (int)$reg['registration_id'] ?>">
                <td>
                  <strong><?= htmlspecialchars($name) ?></strong>
                  <small><?= htmlspecialchars($code) ?> • <?= htmlspecialchars($reg['address']) ?></small>
                </td>
                <td><?= htmlspecialchars($reg['program_name']) ?></td>
                <td><?= htmlspecialchars($dateFormatted) ?></td>
                <td><span class="status <?= htmlspecialchars($statusClass) ?>" id="status-reg-<?= (int)$reg['registration_id'] ?>"><?= htmlspecialchars($reg['attendance_status']) ?></span></td>
                <td>
                  <?php if ($reg['attendance_status'] === 'Registered'): ?>
                    <button type="button" class="action-btn success mark-attended-btn" data-id="<?= (int)$reg['registration_id'] ?>">
                      ✓ Mark Attended
                    </button>
                  <?php else: ?>
                    <span class="muted" style="font-size: 10px; color:#16a34a; font-weight:600;">✓ Present</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>
