<?php
/**
 * Main Application View / Layout
 * Barangay Health Outreach Management System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Enforce authentication
requireAuth('login.php');

$currentUser = getCurrentUser();
$initials = getUserInitials($currentUser['full_name']);

$pageTitle = 'Barangay Health Outreach System';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<main class="main">
  <!-- Topbar -->
  <header class="topbar">
    <div class="breadcrumb">
      Workspace <span>/</span> <strong id="breadcrumb-title">Dashboard</strong>
    </div>
    <div class="topbar-right">
      <span class="today" id="today"></span>
      <div class="profile-wrapper">
        <div class="profile-avatar" id="top-profile-avatar" title="<?= htmlspecialchars($currentUser['full_name']) ?>"><?= htmlspecialchars($initials) ?></div>
        <div class="profile-menu" id="profile-dropdown-menu">
          <div class="profile-menu-header">
            <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong>
            <span>Signed in as <?= htmlspecialchars($currentUser['role']) ?> (@<?= htmlspecialchars($currentUser['username']) ?>)</span>
          </div>
          <button type="button" class="profile-menu-item" id="open-change-password">
            <span>🔑</span> Change password
          </button>
          <div class="profile-menu-divider"></div>
          <a href="logout.php" class="profile-menu-item" style="color: #b91c1c;">
            <span>⎋</span> Sign out
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- Modular Workspace Pages -->
  <?php
  require_once __DIR__ . '/pages/dashboard.php';
  require_once __DIR__ . '/pages/residents.php';
  require_once __DIR__ . '/pages/programs.php';
  require_once __DIR__ . '/pages/registrations.php';
  require_once __DIR__ . '/pages/appointments.php';
  require_once __DIR__ . '/pages/services.php';
  require_once __DIR__ . '/pages/personnel.php';
  require_once __DIR__ . '/pages/inventory.php';
  require_once __DIR__ . '/pages/reports.php';
  ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
