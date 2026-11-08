<?php
/**
 * Page: Programs
 * Barangay Health Outreach Management System
 */

$programs = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("
            SELECT p.*, COUNT(r.registration_id) AS reg_count
            FROM programs p
            LEFT JOIN registrations r ON p.program_id = r.program_id
            GROUP BY p.program_id
            ORDER BY p.start_date DESC
        ");
        $programs = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Programs query error: " . $e->getMessage());
    }
}
$programCount = count($programs);
?>
<section class="page" id="programs-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">PROGRAM MANAGEMENT</p>
      <h1>Health Programs</h1>
      <p class="subtitle">Create and monitor barangay health outreach activities and schedules.</p>
    </div>
    <button class="button primary" id="add-program">＋ Create program</button>
  </div>

  <section class="panel">
    <div class="toolbar">
      <div class="search-box">
        <span>⌕</span>
        <input id="program-search" type="search" placeholder="Search programs by name, venue, or type...">
      </div>
      <span class="muted"><?= $programCount ?> outreach programs</span>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>PROGRAM NAME</th>
            <th>TYPE</th>
            <th>SCHEDULE</th>
            <th>VENUE</th>
            <th>REGISTRATIONS</th>
            <th>STATUS</th>
          </tr>
        </thead>
        <tbody id="program-rows">
          <?php if (empty($programs)): ?>
            <tr><td colspan="6" class="muted" style="text-align:center; padding: 25px;">No outreach programs found.</td></tr>
          <?php else: ?>
            <?php foreach ($programs as $p): 
              $statusClass = strtolower($p['program_status']);
              $sched = date('M d, Y', strtotime($p['start_date']));
            ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($p['program_name']) ?></strong>
                  <?php if (!empty($p['description'])): ?>
                    <small><?= htmlspecialchars($p['description']) ?></small>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['program_type'] ?? 'Outreach') ?></td>
                <td><?= htmlspecialchars($sched) ?></td>
                <td><?= htmlspecialchars($p['venue'] ?? 'Barangay Health Center') ?></td>
                <td><?= (int)$p['reg_count'] ?> registered</td>
                <td><span class="status <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($p['program_status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>
