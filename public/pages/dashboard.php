<?php
/**
 * Page: Dashboard
 * Barangay Health Outreach Management System
 */

// Fetch live metrics from database
$totalResidents = 0;
$activePrograms = 0;
$totalRegistrations = 0;
$totalServices = 0;
$recentPrograms = [];
$recentRegistrations = [];
$lowStockItems = [];

if (isset($pdo)) {
    try {
        $totalResidents = (int)$pdo->query("SELECT COUNT(*) FROM residents")->fetchColumn();
        $activePrograms = (int)$pdo->query("SELECT COUNT(*) FROM programs WHERE program_status IN ('Upcoming', 'Ongoing')")->fetchColumn();
        $totalRegistrations = (int)$pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
        $totalServices = (int)$pdo->query("SELECT COUNT(*) FROM service_records")->fetchColumn();

        $stmt = $pdo->query("
            SELECT p.*, COUNT(r.registration_id) AS reg_count
            FROM programs p
            LEFT JOIN registrations r ON p.program_id = r.program_id
            GROUP BY p.program_id
            ORDER BY p.start_date DESC
            LIMIT 4
        ");
        $recentPrograms = $stmt->fetchAll();

        $stmt = $pdo->query("
            SELECT reg.registration_date, r.first_name, r.last_name, p.program_name
            FROM registrations reg
            JOIN residents r ON reg.resident_id = r.resident_id
            JOIN programs p ON reg.program_id = p.program_id
            ORDER BY reg.registration_date DESC
            LIMIT 4
        ");
        $recentRegistrations = $stmt->fetchAll();

        // Check for low stock supplies
        $lowStockItems = $pdo->query("SELECT item_name, stock_quantity, unit, stock_status FROM inventory WHERE stock_status IN ('Low Stock', 'Out of Stock') LIMIT 3")->fetchAll();
    } catch (PDOException $e) {
        error_log("Dashboard query error: " . $e->getMessage());
    }
}
?>
<section class="page active" id="dashboard-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">OVERVIEW</p>
      <h1>Dashboard</h1>
      <p class="subtitle">Here's what's happening with your health outreach programs.</p>
    </div>
    <button class="button secondary" data-go="programs">View all programs →</button>
  </div>

  <?php if (!empty($lowStockItems)): ?>
    <div class="warning-banner">
      <div class="warning-banner-copy">
        <span style="font-size: 18px;">⚠️</span>
        <div>
          <strong>Supply Alert:</strong>
          <?php 
            $alertText = [];
            foreach ($lowStockItems as $lsi) {
                $alertText[] = htmlspecialchars($lsi['item_name']) . " (" . number_format($lsi['stock_quantity']) . " " . htmlspecialchars($lsi['unit']) . " - " . htmlspecialchars($lsi['stock_status']) . ")";
            }
            echo implode(', ', $alertText);
          ?>
          — Reorder recommended before upcoming medical missions.
        </div>
      </div>
      <button class="warning-banner-btn" data-go="inventory">Manage inventory →</button>
    </div>
  <?php endif; ?>

  <div class="stats-grid">
    <article class="stat-card">
      <div class="stat-top">
        <span>Total residents</span>
        <span class="stat-icon green">♙</span>
      </div>
      <strong id="stat-residents-count"><?= number_format($totalResidents) ?></strong>
      <small class="muted">Registered resident records</small>
    </article>

    <article class="stat-card">
      <div class="stat-top">
        <span>Active programs</span>
        <span class="stat-icon blue">▣</span>
      </div>
      <strong id="stat-programs-count"><?= str_pad((string)$activePrograms, 2, '0', STR_PAD_LEFT) ?></strong>
      <small class="muted">Currently ongoing & upcoming</small>
    </article>

    <article class="stat-card">
      <div class="stat-top">
        <span>Registrations</span>
        <span class="stat-icon orange">☷</span>
      </div>
      <strong id="stat-registrations-count"><?= number_format($totalRegistrations) ?></strong>
      <small class="muted">Across all outreach programs</small>
    </article>

    <article class="stat-card">
      <div class="stat-top">
        <span>Residents served</span>
        <span class="stat-icon purple">✚</span>
      </div>
      <strong id="stat-services-count"><?= number_format($totalServices) ?></strong>
      <small class="muted">Completed service records</small>
    </article>
  </div>

  <div class="content-grid">
    <section class="panel">
      <div class="panel-heading">
        <div>
          <h2>Recent health programs</h2>
          <p>Overview of scheduled outreach activities</p>
        </div>
        <button class="text-button" data-go="programs">View all →</button>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>PROGRAM</th>
              <th>DATE</th>
              <th>REGISTRATIONS</th>
              <th>STATUS</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($recentPrograms)): ?>
              <tr><td colspan="4" class="muted" style="text-align:center; padding: 20px;">No programs found.</td></tr>
            <?php else: ?>
              <?php foreach ($recentPrograms as $prog): 
                $progDate = new DateTime($prog['start_date']);
                $today = new DateTime('today');
                $diffDays = (int)$today->diff($progDate)->format('%r%a');
                $dateNote = '';
                if ($diffDays === 0) {
                    $dateNote = '<span style="color:#16a34a; font-weight:700;">● Today</span>';
                } elseif ($diffDays > 0) {
                    $dateNote = "<small style='color:var(--muted);'>In {$diffDays} days</small>";
                } else {
                    $dateNote = "<small style='color:var(--muted);'>Completed</small>";
                }
              ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($prog['program_name']) ?></strong>
                    <small><?= htmlspecialchars($prog['program_type'] ?? 'Outreach') ?></small>
                  </td>
                  <td>
                    <?= date('M d, Y', strtotime($prog['start_date'])) ?>
                    <br><?= $dateNote ?>
                  </td>
                  <td><?= (int)$prog['reg_count'] ?> registered</td>
                  <td>
                    <?php 
                      $statusClass = strtolower($prog['program_status']);
                    ?>
                    <span class="status <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($prog['program_status']) ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <section class="panel activity-panel">
      <div class="panel-heading">
        <div>
          <h2>Recent registrations</h2>
          <p>Latest beneficiary entries</p>
        </div>
        <button class="text-button" data-go="registrations">View all →</button>
      </div>
      <div class="activity-list">
        <?php if (empty($recentRegistrations)): ?>
          <p class="muted" style="padding: 18px; margin: 0;">No registrations yet.</p>
        <?php else: ?>
          <?php 
            $altClasses = ['', 'alt-one', 'alt-two', 'alt-three'];
            $i = 0;
            foreach ($recentRegistrations as $reg):
              $name = trim($reg['first_name'] . ' ' . $reg['last_name']);
              $parts = explode(' ', $name);
              $initials = (count($parts) >= 2)
                  ? strtoupper(substr($parts[0], 0, 1) . substr(end($parts), 0, 1))
                  : strtoupper(substr($name, 0, 2));
              $alt = $altClasses[$i % count($altClasses)];
              $i++;
          ?>
            <div class="activity-item">
              <div class="person-avatar <?= $alt ?>"><?= htmlspecialchars($initials) ?></div>
              <div class="activity-copy">
                <strong><?= htmlspecialchars($name) ?></strong>
                <span><?= htmlspecialchars($reg['program_name']) ?></span>
              </div>
              <time><?= date('M d', strtotime($reg['registration_date'])) ?></time>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <div class="notice">
    <span>ⓘ</span>
    <p><strong>Database Live:</strong> Beneficiary records and program statuses are dynamically fetched from MySQL.</p>
  </div>
</section>
