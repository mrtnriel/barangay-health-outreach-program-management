<?php
/**
 * Page: Residents
 * Barangay Health Outreach Management System
 */

$residents = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM residents ORDER BY resident_id DESC");
        $residents = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Residents query error: " . $e->getMessage());
    }
}
$residentCount = count($residents);
?>
<section class="page" id="residents-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">RECORD MANAGEMENT</p>
      <h1>Residents</h1>
      <p class="subtitle">Manage resident profiles, demographic info, age categories, and beneficiary records.</p>
    </div>
    <button class="button primary" id="add-resident">＋ Add resident</button>
  </div>

  <section class="panel">
    <div class="toolbar">
      <div class="search-box">
        <span>⌕</span>
        <input id="resident-search" type="search" placeholder="Search by name, address, ID, or age...">
      </div>
      <span class="muted" id="resident-count"><?= $residentCount ?> residents</span>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>RESIDENT</th>
            <th>DATE OF BIRTH</th>
            <th>AGE & CATEGORY</th>
            <th>SEX</th>
            <th>ADDRESS</th>
            <th>CONTACT</th>
            <th>ACTIONS</th>
          </tr>
        </thead>
        <tbody id="resident-rows">
          <?php if (empty($residents)): ?>
            <tr><td colspan="7" class="muted" style="text-align:center; padding: 25px;">No resident records found. Click "Add resident" to create one.</td></tr>
          <?php else: ?>
            <?php foreach ($residents as $r): 
              $code = 'RES-' . str_pad($r['resident_id'], 4, '0', STR_PAD_LEFT);
              $fullName = trim($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name']);
              $bdate = !empty($r['birth_date']) ? date('M d, Y', strtotime($r['birth_date'])) : '—';
              
              // Age and Demographic Category calculation
              $ageText = '—';
              $badgeHtml = '';
              if (!empty($r['birth_date'])) {
                  $birthDateTime = new DateTime($r['birth_date']);
                  $now = new DateTime();
                  $age = $now->diff($birthDateTime)->y;
                  $ageText = $age . ' yrs';

                  if ($age >= 60) {
                      $badgeHtml = '<span class="badge senior">60+ • Senior Citizen</span>';
                  } elseif ($age <= 12) {
                      $badgeHtml = '<span class="badge pediatric">Child / Pediatric</span>';
                  } else {
                      $badgeHtml = '<span class="badge adult">Adult</span>';
                  }
              }
            ?>
              <tr data-resident-row-id="<?= (int)$r['resident_id'] ?>">
                <td>
                  <strong><?= htmlspecialchars($fullName) ?></strong>
                  <small><?= htmlspecialchars($code) ?></small>
                </td>
                <td><?= htmlspecialchars($bdate) ?></td>
                <td>
                  <strong><?= $ageText ?></strong>
                  <?= $badgeHtml ? '<br>' . $badgeHtml : '' ?>
                </td>
                <td><?= htmlspecialchars($r['sex']) ?></td>
                <td><?= htmlspecialchars($r['address']) ?></td>
                <td><?= htmlspecialchars($r['contact_number'] ?: '—') ?></td>
                <td>
                  <button type="button" class="action-btn view-resident-btn" data-id="<?= (int)$r['resident_id'] ?>">
                    <span>🔍</span> Profile
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>
