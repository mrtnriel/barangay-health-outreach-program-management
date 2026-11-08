<?php
/**
 * Navigation Sidebar Partial
 * Barangay Health Outreach Management System
 */

$user = getCurrentUser() ?? [
    'full_name' => 'Barangay Staff',
    'role'      => 'Administrator'
];
$initials = getUserInitials($user['full_name']);
?>
<aside class="sidebar">
  <div class="brand">
    <div class="brand-mark">BH</div>
    <div><strong>Barangay Health</strong><span>Outreach Management</span></div>
  </div>

  <div class="nav-label">WORKSPACE</div>
  <nav class="navigation">
    <button class="nav-link active" data-page="dashboard">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
      </span>
      Dashboard
    </button>
    <button class="nav-link" data-page="residents">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </span>
      Residents
    </button>
    <button class="nav-link" data-page="programs">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </span>
      Health Programs
    </button>
    <button class="nav-link" data-page="registrations">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="m9 14 2 2 4-4"/></svg>
      </span>
      Registrations
    </button>
    <button class="nav-link" data-page="appointments">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </span>
      Appointments
    </button>
    <button class="nav-link" data-page="services">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M12 8v8M8 12h8"/></svg>
      </span>
      Service Records
    </button>
    <button class="nav-link" data-page="personnel">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </span>
      Personnel
    </button>
    <button class="nav-link" data-page="inventory">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
      </span>
      Inventory
    </button>
    <button class="nav-link" data-page="reports">
      <span class="nav-icon">
        <svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </span>
      Reports
    </button>
  </nav>

  <div class="sidebar-bottom">
    <div class="user-avatar"><?= htmlspecialchars($initials) ?></div>
    <div class="user-info">
      <strong><?= htmlspecialchars($user['full_name']) ?></strong>
      <span><?= htmlspecialchars($user['role']) ?></span>
    </div>
    <a href="logout.php" title="Sign out" class="logout-btn">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    </a>
  </div>
</aside>
