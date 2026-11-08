<?php
/**
 * Page: Personnel
 * Barangay Health Outreach Management System
 */

$personnel = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT user_id, full_name, username, role, created_at FROM users ORDER BY user_id ASC");
        $personnel = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Personnel query error: " . $e->getMessage());
    }
}
$personnelCount = count($personnel);
?>
<section class="page" id="personnel-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">STAFF & WORKER DIRECTORY</p>
      <h1>Personnel & Health Workers</h1>
      <p class="subtitle">Directory of municipal health officers, clinic staff, and Barangay Health Workers (BHWs).</p>
    </div>
    <?php if (($currentUser['role'] ?? '') === 'Admin'): ?>
      <button class="button primary" id="add-personnel">＋ Add personnel</button>
    <?php endif; ?>
  </div>

  <section class="panel">
    <div class="toolbar">
      <div class="search-box">
        <span>⌕</span>
        <input id="personnel-search" type="search" placeholder="Search by name, username, or role...">
      </div>
      <span class="muted" id="personnel-count"><?= $personnelCount ?> active personnel</span>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>FULL NAME</th>
            <th>USERNAME</th>
            <th>SYSTEM ROLE</th>
            <th>DATE REGISTERED</th>
            <th>ACCOUNT STATUS</th>
          </tr>
        </thead>
        <tbody id="personnel-rows">
          <?php if (empty($personnel)): ?>
            <tr><td colspan="5" class="muted" style="text-align:center; padding: 25px;">No personnel records found.</td></tr>
          <?php else: ?>
            <?php foreach ($personnel as $u): 
              $roleClass = match ($u['role']) {
                  'Admin' => 'completed',
                  'Staff' => 'ongoing',
                  'BHW'   => 'upcoming',
                  default => 'ongoing'
              };
              $regDate = !empty($u['created_at']) ? date('M d, Y', strtotime($u['created_at'])) : 'Active';
            ?>
              <tr>
                <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                <td><code style="background:#f4f6f4; padding:3px 6px; font-size:11px;"><?= htmlspecialchars($u['username']) ?></code></td>
                <td><span class="status <?= htmlspecialchars($roleClass) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                <td><?= htmlspecialchars($regDate) ?></td>
                <td><span style="color:var(--green); font-size:11px; font-weight:600;">● Active</span></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>
