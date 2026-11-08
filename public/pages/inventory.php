<?php
/**
 * Page: Inventory
 * Barangay Health Outreach Management System
 */

$inventory = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM inventory ORDER BY item_id DESC");
        $inventory = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Inventory query error: " . $e->getMessage());
    }
}
$inventoryCount = count($inventory);
?>
<section class="page" id="inventory-page">
  <div class="page-heading">
    <div>
      <p class="eyebrow">SUPPLY MANAGEMENT</p>
      <h1>Medicine & Supply Inventory</h1>
      <p class="subtitle">Monitor distribution, quick stock adjustments, and stock levels of essential medicines.</p>
    </div>
    <button class="button primary" id="add-inventory">＋ Add item</button>
  </div>

  <section class="panel">
    <div class="toolbar">
      <div class="search-box">
        <span>⌕</span>
        <input id="inventory-search" type="search" placeholder="Search medicine, supplies, or category...">
      </div>
      <span class="muted" id="inventory-count"><?= $inventoryCount ?> tracked items</span>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>ITEM NAME</th>
            <th>CATEGORY</th>
            <th>AVAILABLE STOCK</th>
            <th>UNIT</th>
            <th>STOCK STATUS</th>
            <th>QUICK DISPENSE / RESTOCK</th>
          </tr>
        </thead>
        <tbody id="inventory-rows">
          <?php if (empty($inventory)): ?>
            <tr><td colspan="6" class="muted" style="text-align:center; padding: 25px;">No inventory items recorded yet. Click "Add item" to register supplies.</td></tr>
          <?php else: ?>
            <?php foreach ($inventory as $item): 
              $statusClass = match ($item['stock_status']) {
                  'Adequate'     => 'ongoing',
                  'Low Stock'    => 'upcoming',
                  'Out of Stock' => 'completed',
                  default        => 'ongoing'
              };
            ?>
              <tr data-item-row-id="<?= (int)$item['item_id'] ?>">
                <td><strong><?= htmlspecialchars($item['item_name']) ?></strong></td>
                <td><?= htmlspecialchars($item['category']) ?></td>
                <td><strong id="stock-val-<?= (int)$item['item_id'] ?>"><?= number_format((int)$item['stock_quantity']) ?></strong></td>
                <td><?= htmlspecialchars($item['unit']) ?></td>
                <td><span class="status <?= htmlspecialchars($statusClass) ?>" id="stock-status-<?= (int)$item['item_id'] ?>"><?= htmlspecialchars($item['stock_status']) ?></span></td>
                <td>
                  <button type="button" class="stock-btn quick-stock-btn" data-id="<?= (int)$item['item_id'] ?>" data-delta="10" title="Restock +10">+10</button>
                  <button type="button" class="stock-btn quick-stock-btn" data-id="<?= (int)$item['item_id'] ?>" data-delta="-10" title="Dispense -10" style="color:#b91c1c;">-10</button>
                  <button type="button" class="stock-btn quick-stock-btn" data-id="<?= (int)$item['item_id'] ?>" data-delta="50" title="Restock +50">+50</button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</section>
