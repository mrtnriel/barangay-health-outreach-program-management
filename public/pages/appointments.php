<?php
/**
 * Page: Appointments
 * Barangay Health Outreach Management System
 */

$appointments = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("
            SELECT 
                a.*,
                r.resident_id,
                r.first_name,
                r.last_name,
                r.address,
                r.contact_number
            FROM appointments a
            JOIN residents r ON a.resident_id = r.resident_id
            ORDER BY a.appointment_date ASC
        ");
        $appointments = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Appointments query error: " . $e->getMessage());
    }
}
$appointmentCount = count($appointments);
?>
<section class="page" id="appointments-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">SCHEDULING & CLINIC VISITS</p>
      <h1>Appointments</h1>
      <p class="subtitle">Manage patient consultation appointments, follow-ups, and clinic bookings.</p>
    </div>
    <button class="button primary" id="add-appointment">＋ Book appointment</button>
  </div>

  <section class="panel">
    <div class="toolbar">
      <div class="search-box">
        <span>⌕</span>
        <input id="appointment-search" type="search" placeholder="Search resident, purpose, or physician...">
      </div>
      <span class="muted" id="appointment-count"><?= $appointmentCount ?> scheduled appointments</span>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>BENEFICIARY / PATIENT</th>
            <th>PURPOSE / MEDICAL CONCERN</th>
            <th>SCHEDULED DATE & TIME</th>
            <th>ATTENDING PHYSICIAN</th>
            <th>STATUS</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody id="appointment-rows">
          <?php if (empty($appointments)): ?>
            <tr><td colspan="6" class="muted" style="text-align:center; padding: 25px;">No appointments currently scheduled. Click "Book appointment" to add one.</td></tr>
          <?php else: ?>
            <?php foreach ($appointments as $apt): 
              $code = 'RES-' . str_pad($apt['resident_id'], 4, '0', STR_PAD_LEFT);
              $name = trim($apt['first_name'] . ' ' . $apt['last_name']);
              $dateFormatted = date('M d, Y h:i A', strtotime($apt['appointment_date']));
              $statusClass = match ($apt['status']) {
                  'Completed' => 'completed',
                  'Cancelled' => 'completed',
                  default     => 'upcoming'
              };
            ?>
              <tr data-apt-row-id="<?= (int)$apt['appointment_id'] ?>">
                <td>
                  <strong><?= htmlspecialchars($name) ?></strong>
                  <small><?= htmlspecialchars($code) ?> • <?= htmlspecialchars($apt['address']) ?></small>
                </td>
                <td><?= htmlspecialchars($apt['purpose']) ?></td>
                <td><?= htmlspecialchars($dateFormatted) ?></td>
                <td><?= htmlspecialchars($apt['physician_name'] ?? 'Dr. Maria Ramos') ?></td>
                <td><span class="status <?= htmlspecialchars($statusClass) ?>" id="status-apt-<?= (int)$apt['appointment_id'] ?>"><?= htmlspecialchars($apt['status']) ?></span></td>
                <td>
                  <?php if ($apt['status'] === 'Scheduled'): ?>
                    <button type="button" class="action-btn success complete-apt-btn" data-id="<?= (int)$apt['appointment_id'] ?>">
                      ✓ Complete
                    </button>
                    <button type="button" class="action-btn danger cancel-apt-btn" data-id="<?= (int)$apt['appointment_id'] ?>">
                      ✕ Cancel
                    </button>
                  <?php else: ?>
                    <span class="muted" style="font-size: 10px;">Concluded</span>
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
