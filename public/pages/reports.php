<?php
/**
 * Page: Reports
 * Barangay Health Outreach Management System
 */

$totalRegs = 0;
$attendedCount = 0;
$maleCount = 0;
$femaleCount = 0;
$zoneCount = 0;
$programSummaries = [];

if (isset($pdo)) {
    try {
        $totalRegs = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
        $attendedCount = (int)$pdo->query("SELECT COUNT(*) FROM registrations WHERE attendance_status = 'Attended'")->fetchColumn();
        $maleCount = (int)$pdo->query("SELECT COUNT(*) FROM residents WHERE sex = 'Male'")->fetchColumn();
        $femaleCount = (int)$pdo->query("SELECT COUNT(*) FROM residents WHERE sex = 'Female'")->fetchColumn();
        $zoneCount = (int)$pdo->query("SELECT COUNT(DISTINCT address) FROM residents")->fetchColumn();

        $stmt = $pdo->query("
            SELECT 
                p.program_name,
                p.start_date,
                p.program_status,
                COUNT(r.registration_id) AS total_registered,
                SUM(CASE WHEN r.attendance_status = 'Attended' THEN 1 ELSE 0 END) AS total_attended
            FROM programs p
            LEFT JOIN registrations r ON p.program_id = r.program_id
            GROUP BY p.program_id
            ORDER BY p.start_date DESC
        ");
        $programSummaries = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Reports query error: " . $e->getMessage());
    }
}

$attendanceRate = $totalRegs > 0 ? round(($attendedCount / $totalRegs) * 100, 1) : 0;
?>
<section class="page" id="reports-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">SYSTEM REPORTS</p>
      <h1>Reports & Analytics</h1>
      <p class="subtitle">Review program participation, demographic summaries, and outreach accomplishment reports.</p>
    </div>
    <div style="display:flex; gap:8px;">
      <button class="button secondary" id="export-csv-btn">📥 Export CSV</button>
      <button class="button primary" onclick="window.print()">🖨 Print summary</button>
    </div>
  </div>

  <div class="stats-grid">
    <article class="stat-card">
      <div class="stat-top"><span>Attendance Rate</span><span class="stat-icon green">📈</span></div>
      <strong><?= $attendanceRate ?>%</strong>
      <small class="muted"><?= $attendedCount ?> attended / <?= $totalRegs ?> registered</small>
    </article>
    <article class="stat-card">
      <div class="stat-top"><span>Male Beneficiaries</span><span class="stat-icon blue">♙</span></div>
      <strong><?= $maleCount ?></strong>
      <small class="muted"><?= ($maleCount + $femaleCount) > 0 ? round(($maleCount / ($maleCount + $femaleCount)) * 100) : 0 ?>% of resident roster</small>
    </article>
    <article class="stat-card">
      <div class="stat-top"><span>Female Beneficiaries</span><span class="stat-icon orange">♙</span></div>
      <strong><?= $femaleCount ?></strong>
      <small class="muted"><?= ($maleCount + $femaleCount) > 0 ? round(($femaleCount / ($maleCount + $femaleCount)) * 100) : 0 ?>% of resident roster</small>
    </article>
    <article class="stat-card">
      <div class="stat-top"><span>Active Zones</span><span class="stat-icon purple">📍</span></div>
      <strong><?= $zoneCount ?></strong>
      <small class="muted">Resident distribution areas</small>
    </article>
  </div>

  <section class="panel">
    <div class="panel-heading">
      <div>
        <h2>Program Accomplishment Summary</h2>
        <p>Comprehensive overview of health mission deliverables</p>
      </div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>PROGRAM</th>
            <th>SCHEDULE</th>
            <th>TOTAL REGISTERED</th>
            <th>ACTUAL ATTENDED / SERVED</th>
            <th>STATUS</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($programSummaries)): ?>
            <tr><td colspan="5" class="muted" style="text-align:center; padding: 25px;">No program data available.</td></tr>
          <?php else: ?>
            <?php foreach ($programSummaries as $ps): 
              $statusClass = strtolower($ps['program_status']);
            ?>
              <tr>
                <td><strong><?= htmlspecialchars($ps['program_name']) ?></strong></td>
                <td><?= date('M d, Y', strtotime($ps['start_date'])) ?></td>
                <td><?= (int)$ps['total_registered'] ?> registered</td>
                <td><?= (int)$ps['total_attended'] ?> served</td>
                <td><span class="status <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($ps['program_status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>
