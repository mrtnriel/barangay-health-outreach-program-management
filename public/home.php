<?php
/**
 * Public Landing Page
 * Barangay Health Outreach Management System
 * 
 * Features Overview, Services, Programs, Advisories, and Schedule tabs
 * in complete visual alignment with the website's original design system.
 */

// Fetch live programs from database for the Programs tab
$programsList = [];
if (isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT * FROM programs ORDER BY start_date ASC");
        $programsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Programs query error on portal: " . $e->getMessage());
    }
}

$validTabs = ['overview', 'services', 'programs', 'advisories', 'schedule'];
$activeTab = $_GET['tab'] ?? 'overview';
if (!in_array($activeTab, $validTabs, true)) {
    $activeTab = 'overview';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Barangay Health Outreach Management System</title>
  <link rel="stylesheet" href="assets/css/style.css">
  
  <style>
    /* -------------------------------------------------------------
       Portal Specific Layout & Dynamic Backdrop
       Consolidated with style.css tokens:
       --green, --green-dark, --green-soft, --ink, --muted, --line, --canvas, --surface
    -------------------------------------------------------------- */
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    /* Subtle ambient lighting shifting per active tab */
    .portal-backdrop {
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      transition: background 0.3s ease;
    }
    body[data-active-tab="overview"] .portal-backdrop {
      background:
        radial-gradient(ellipse 70% 60% at 85% 25%, rgba(36, 91, 69, 0.07) 0%, transparent 60%),
        radial-gradient(ellipse 50% 50% at 15% 85%, rgba(36, 91, 69, 0.04) 0%, transparent 50%),
        linear-gradient(135deg, #f7f9f7 0%, #f2f6f3 50%, #edf3ef 100%);
    }
    body[data-active-tab="services"] .portal-backdrop {
      background:
        radial-gradient(ellipse 80% 50% at 50% 15%, rgba(36, 91, 69, 0.08) 0%, transparent 65%),
        linear-gradient(135deg, #f5f8f6 0%, #edf4ef 50%, #f7f9f7 100%);
    }
    body[data-active-tab="programs"] .portal-backdrop {
      background:
        radial-gradient(ellipse 75% 55% at 80% 20%, rgba(36, 91, 69, 0.09) 0%, transparent 65%),
        linear-gradient(135deg, #f7faf8 0%, #f0f6f2 50%, #edf4ef 100%);
    }
    body[data-active-tab="advisories"] .portal-backdrop {
      background:
        radial-gradient(ellipse 75% 50% at 30% 20%, rgba(173, 120, 58, 0.05) 0%, transparent 60%),
        linear-gradient(135deg, #fafbf9 0%, #f4f7f4 50%, #f1f5f2 100%);
    }
    body[data-active-tab="schedule"] .portal-backdrop {
      background:
        radial-gradient(ellipse 80% 60% at 50% 20%, rgba(36, 91, 69, 0.07) 0%, transparent 60%),
        linear-gradient(135deg, #f7f9f8 0%, #edf3ef 50%, #f4f8f5 100%);
    }

    .portal-wave-svg {
      position: absolute;
      top: -8%;
      right: -10%;
      width: 70vw;
      max-width: 950px;
      height: 115vh;
      opacity: 0.55;
      transform: rotate(-2deg);
    }

    /* Portal Topbar Navigation */
    .portal-nav {
      position: relative;
      z-index: 20;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 44px;
      background: var(--surface);
      border-bottom: 1px solid var(--line);
    }
    .portal-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--ink);
    }
    .portal-brand strong {
      font-size: 13.5px;
      letter-spacing: -0.2px;
      display: block;
      color: var(--ink);
    }
    .portal-brand span {
      display: block;
      color: var(--muted);
      font-size: 10px;
      margin-top: 2px;
    }

    .portal-nav-links {
      display: flex;
      align-items: center;
      gap: 4px;
      list-style: none;
      margin: 0;
      padding: 0;
    }
    .portal-nav-link {
      display: inline-block;
      text-decoration: none;
      color: #5a665e;
      font-size: 12.5px;
      font-weight: 600;
      padding: 8px 16px;
      border-radius: 4px;
      border: 1px solid transparent;
      cursor: pointer;
      background: transparent;
      font-family: inherit;
      transition: background 0.15s, color 0.15s, border-color 0.15s;
    }
    .portal-nav-link:hover {
      background: #f1f5f2;
      color: var(--green);
    }
    .portal-nav-link.active {
      background: var(--green-soft);
      color: var(--green-dark);
      border-color: #d1e2d7;
      font-weight: 650;
    }

    /* Main Container */
    .portal-main {
      position: relative;
      z-index: 10;
      flex: 1;
      width: 100%;
      max-width: 1320px;
      margin: 0 auto;
      padding: 38px 44px 48px;
    }

    .portal-tab-content {
      display: none;
      animation: tabFade 0.22s ease-out;
    }
    .portal-tab-content.active {
      display: block;
    }
    @keyframes tabFade {
      from { opacity: 0; transform: translateY(4px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Section Page Headings (matching dashboard style) */
    .section-header {
      margin-bottom: 26px;
    }
    .section-header .eyebrow {
      margin-bottom: 8px;
    }
    .section-header h1, .section-header h2 {
      margin: 0;
      font-size: 27px;
      letter-spacing: -0.9px;
      font-weight: 680;
      color: var(--ink);
    }
    .section-header .subtitle {
      margin: 8px 0 0;
      color: var(--muted);
      font-size: 12px;
      max-width: 720px;
    }

    /* Overview Layout */
    .overview-hero-layout {
      max-width: 860px;
      margin: 0 auto;
      text-align: center;
      padding: 20px 0 28px;
    }
    .overview-hero-layout .eyebrow {
      margin-bottom: 12px;
    }
    .hero-big-title {
      font-size: clamp(32px, 3.8vw, 48px);
      font-weight: 680;
      line-height: 1.12;
      letter-spacing: -1px;
      color: var(--ink);
      text-transform: uppercase;
      margin: 0 0 16px;
    }
    .hero-big-title span {
      color: var(--green);
      display: block;
    }
    .hero-big-desc {
      font-size: 14.5px;
      line-height: 1.6;
      color: #556259;
      margin: 0 auto 30px;
      max-width: 660px;
    }

    .hero-pills-row {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin-bottom: 38px;
    }
    .hero-pill-btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--surface);
      border: 1px solid var(--line);
      padding: 9px 16px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
      color: var(--ink);
      cursor: pointer;
      transition: all 0.15s;
    }
    .hero-pill-btn:hover {
      background: var(--green-soft);
      border-color: #cbdad0;
      color: var(--green-dark);
      transform: translateY(-1px);
    }
    .hero-pill-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--green);
    }

    /* Overview Feature Grid */
    .overview-cards-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 30px;
      text-align: left;
    }
    .overview-card-item {
      background: var(--surface);
      border: 1px solid var(--line);
      padding: 20px 18px;
      border-radius: 4px;
      display: flex;
      flex-direction: column;
      transition: border-color 0.15s, transform 0.15s;
    }
    .overview-card-item:hover {
      border-color: #cbdad0;
      transform: translateY(-1px);
    }
    .card-icon-box {
      width: 34px;
      height: 34px;
      border-radius: 4px;
      background: var(--green-soft);
      color: var(--green);
      display: grid;
      place-items: center;
      font-size: 16px;
      margin-bottom: 12px;
    }
    .overview-card-item h3 {
      font-size: 13.5px;
      font-weight: 650;
      color: var(--ink);
      margin: 0 0 6px;
    }
    .overview-card-item p {
      font-size: 11px;
      color: var(--muted);
      line-height: 1.45;
      margin: 0;
      flex: 1;
    }

    /* Staff Banner */
    .staff-portal-cta-banner {
      background: var(--surface);
      border: 1px solid var(--line);
      border-left: 4px solid var(--green);
      padding: 20px 24px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      text-align: left;
    }
    .staff-cta-copy strong {
      font-size: 14px;
      color: var(--ink);
      display: block;
      margin-bottom: 4px;
      font-weight: 680;
    }
    .staff-cta-copy p {
      font-size: 11.5px;
      color: var(--muted);
      margin: 0;
    }

    /* Services Grid */
    .services-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }
    .service-card {
      background: var(--surface);
      border: 1px solid var(--line);
      padding: 20px 18px;
      border-radius: 4px;
      display: flex;
      flex-direction: column;
      transition: border-color 0.15s, transform 0.15s;
    }
    .service-card:hover {
      border-color: #cbdad0;
      transform: translateY(-1px);
    }
    .service-icon-wrap {
      width: 34px;
      height: 34px;
      border-radius: 4px;
      background: var(--green-soft);
      color: var(--green);
      display: grid;
      place-items: center;
      margin-bottom: 14px;
      font-size: 16px;
    }
    .service-card h3 {
      font-size: 14px;
      letter-spacing: -0.2px;
      margin: 0 0 7px;
      color: var(--ink);
      font-weight: 680;
    }
    .service-card p {
      font-size: 11.5px;
      color: #556259;
      line-height: 1.45;
      margin: 0 0 14px;
      flex: 1;
    }
    .service-meta-footer {
      border-top: 1px solid var(--line);
      padding-top: 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 10.5px;
    }

    .service-process-panel {
      background: var(--surface);
      border: 1px solid var(--line);
      padding: 20px 22px;
      border-radius: 4px;
    }
    .process-heading {
      font-size: 13px;
      font-weight: 680;
      margin: 0 0 12px;
      color: var(--ink);
    }
    .process-steps-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
    }
    .process-step {
      background: #fafbfa;
      border: 1px solid var(--line);
      padding: 12px 14px;
      border-radius: 4px;
    }
    .step-number {
      font-size: 9.5px;
      font-weight: 700;
      color: var(--green);
      letter-spacing: 0.6px;
      text-transform: uppercase;
      margin-bottom: 4px;
    }
    .step-text {
      font-size: 11px;
      font-weight: 650;
      color: var(--ink);
      margin: 0 0 3px;
    }
    .step-detail {
      font-size: 10px;
      color: var(--muted);
      margin: 0;
      line-height: 1.4;
    }

    /* Programs Page */
    .programs-stats-bar {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 15px;
      margin-bottom: 20px;
    }
    .programs-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }
    .program-card-item {
      background: var(--surface);
      border: 1px solid var(--line);
      padding: 20px;
      border-radius: 4px;
      display: flex;
      flex-direction: column;
      transition: border-color 0.15s;
    }
    .program-card-item:hover { border-color: #cbdad0; }
    .program-card-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 10px;
      margin-bottom: 10px;
    }
    .program-title {
      font-size: 15px;
      font-weight: 680;
      letter-spacing: -0.3px;
      color: var(--ink);
      margin: 0 0 6px;
    }
    .program-desc {
      font-size: 11.5px;
      color: #556259;
      line-height: 1.45;
      margin: 0 0 14px;
      flex: 1;
    }
    .program-info-list {
      background: #fafbfa;
      border: 1px solid var(--line);
      padding: 9px 12px;
      border-radius: 4px;
      font-size: 10.5px;
      margin-bottom: 12px;
    }
    .program-info-row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 3px 0;
      color: #4b5850;
    }
    .program-info-row svg {
      width: 13px;
      height: 13px;
      color: var(--green);
      flex-shrink: 0;
    }

    /* Advisories */
    .advisories-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
    }
    .advisory-card {
      background: var(--surface);
      border: 1px solid var(--line);
      padding: 18px;
      border-radius: 4px;
      display: flex;
      flex-direction: column;
    }
    .advisory-date {
      font-size: 9.5px;
      color: var(--muted);
      margin-bottom: 6px;
      display: block;
      font-weight: 600;
    }
    .advisory-card h4 {
      font-size: 13px;
      font-weight: 650;
      margin: 0 0 6px;
      color: var(--ink);
    }
    .advisory-card p {
      font-size: 11px;
      color: #556259;
      line-height: 1.45;
      margin: 0 0 12px;
      flex: 1;
    }

    /* Timetable Grid */
    .timetable-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 12px;
    }
    .timetable-card {
      background: var(--surface);
      border: 1px solid var(--line);
      border-radius: 4px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    .day-header {
      background: #fafbfa;
      border-bottom: 1px solid var(--line);
      padding: 10px 12px;
      text-align: center;
    }
    .day-header strong {
      display: block;
      font-size: 12px;
      font-weight: 650;
      color: var(--ink);
    }
    .day-header span {
      font-size: 9.5px;
      color: var(--muted);
    }
    .day-body {
      padding: 12px;
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .session-time {
      font-size: 9.5px;
      font-weight: 700;
      color: var(--green);
      display: block;
      margin-bottom: 2px;
    }
    .session-title {
      font-size: 11px;
      font-weight: 600;
      color: var(--ink);
      display: block;
      line-height: 1.25;
    }
    .session-assigned {
      color: var(--muted);
      font-size: 9.5px;
      display: block;
      margin-top: 2px;
    }

    /* Portal Footer */
    .portal-footer {
      position: relative;
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 44px;
      background: var(--surface);
      border-top: 1px solid var(--line);
      font-size: 11px;
      color: var(--muted);
    }
    .footer-right-links {
      display: flex;
      gap: 14px;
    }

    @media (max-width: 1050px) {
      .portal-nav { padding: 14px 22px; }
      .portal-main { padding: 26px 22px; }
      .overview-cards-grid { grid-template-columns: repeat(2, 1fr); }
      .services-grid, .advisories-grid { grid-template-columns: repeat(2, 1fr); }
      .timetable-grid { grid-template-columns: repeat(3, 1fr); }
      .process-steps-row { grid-template-columns: repeat(2, 1fr); }
      .portal-footer { padding: 14px 22px; }
    }
    @media (max-width: 700px) {
      .portal-nav {
        padding: 10px 14px;
        flex-wrap: wrap;
        gap: 8px;
      }
      .portal-nav-links {
        order: 3;
        width: 100%;
        overflow-x: auto;
        padding-bottom: 3px;
        gap: 2px;
      }
      .portal-nav-link {
        padding: 6px 10px;
        font-size: 11px;
        white-space: nowrap;
      }
      .portal-main { padding: 18px 14px 30px; }
      .overview-cards-grid, .programs-grid, .timetable-grid, .services-grid, .advisories-grid, .programs-stats-bar, .process-steps-row {
        grid-template-columns: 1fr;
      }
      .staff-portal-cta-banner {
        flex-direction: column;
        align-items: flex-start;
      }
      .portal-footer {
        flex-direction: column;
        gap: 8px;
        text-align: center;
        padding: 14px 14px;
      }
    }
  </style>
</head>
<body data-active-tab="<?= htmlspecialchars($activeTab) ?>">

  <!-- Dynamic Ambient Backdrop -->
  <div class="portal-backdrop">
    <svg class="portal-wave-svg" viewBox="0 0 1000 1000" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <defs>
        <linearGradient id="sageWaveGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" stop-color="#245b45" stop-opacity="0.10" />
          <stop offset="50%" stop-color="#3d8264" stop-opacity="0.06" />
          <stop offset="100%" stop-color="#245b45" stop-opacity="0.02" />
        </linearGradient>
      </defs>
      <g stroke-linecap="round">
        <path d="M 850, -50 C 720, 200 890, 420 780, 650 C 700, 810 520, 890 350, 950" stroke="url(#sageWaveGrad)" stroke-width="2" />
        <path d="M 880, -20 C 750, 220 920, 440 810, 670 C 730, 830 550, 910 380, 970" stroke="url(#sageWaveGrad)" stroke-width="1.5" />
        <path d="M 820, -80 C 690, 180 860, 400 750, 630 C 670, 790 490, 870 320, 930" stroke="url(#sageWaveGrad)" stroke-width="1.2" />
        <path d="M 450, 980 C 650, 880 820, 750 860, 580 C 900, 420 830, 250 890, 50" stroke="url(#sageWaveGrad)" stroke-width="1.8" />
      </g>
    </svg>
  </div>

  <!-- Top Navigation Bar -->
  <header class="portal-nav">
    <a href="javascript:void(0)" onclick="switchTab('overview')" class="portal-brand">
      <div class="brand-mark">BH</div>
      <div>
        <strong>Barangay Health</strong>
        <span>Outreach Management System</span>
      </div>
    </a>

    <!-- Main Navigation Tabs -->
    <nav aria-label="Portal Navigation">
      <ul class="portal-nav-links">
        <li>
          <button type="button" class="portal-nav-link <?= $activeTab === 'overview' ? 'active' : '' ?>" id="nav-btn-overview" onclick="switchTab('overview')">
            Overview
          </button>
        </li>
        <li>
          <button type="button" class="portal-nav-link <?= $activeTab === 'services' ? 'active' : '' ?>" id="nav-btn-services" onclick="switchTab('services')">
            Services
          </button>
        </li>
        <li>
          <button type="button" class="portal-nav-link <?= $activeTab === 'programs' ? 'active' : '' ?>" id="nav-btn-programs" onclick="switchTab('programs')">
            Programs
          </button>
        </li>
        <li>
          <button type="button" class="portal-nav-link <?= $activeTab === 'advisories' ? 'active' : '' ?>" id="nav-btn-advisories" onclick="switchTab('advisories')">
            Advisories
          </button>
        </li>
        <li>
          <button type="button" class="portal-nav-link <?= $activeTab === 'schedule' ? 'active' : '' ?>" id="nav-btn-schedule" onclick="switchTab('schedule')">
            Schedule
          </button>
        </li>
      </ul>
    </nav>

    <!-- Sign In Action -->
    <div class="portal-nav-actions">
      <a href="login.php" class="button primary">
        <span>Sign In</span>
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="5" y1="12" x2="19" y2="12"></line>
          <polyline points="12 5 19 12 12 19"></polyline>
        </svg>
      </a>
    </div>
  </header>

  <!-- Main Content Area -->
  <main class="portal-main">

    <!-- =========================================================
         TAB 1: OVERVIEW PAGE
    ========================================================== -->
    <section class="portal-tab-content <?= $activeTab === 'overview' ? 'active' : '' ?>" id="tab-overview" role="tabpanel">
      <div class="overview-hero-layout">
        <p class="eyebrow">COMMUNITY HEALTHCARE PLATFORM</p>
        <h1 class="hero-big-title">
          Barangay Health
          <span>Outreach System</span>
        </h1>
        <p class="hero-big-desc">
          A centralized digital public healthcare management platform empowering health workers, organizing community medical missions, managing resident wellness records, and providing accessible primary care across the barangay.
        </p>

        <div class="hero-pills-row">
          <button type="button" class="hero-pill-btn" onclick="switchTab('services')">
            <span class="hero-pill-dot"></span>
            <span>Healthcare Services</span>
          </button>
          <button type="button" class="hero-pill-btn" onclick="switchTab('programs')">
            <span class="hero-pill-dot"></span>
            <span>Outreach Missions</span>
          </button>
          <button type="button" class="hero-pill-btn" onclick="switchTab('advisories')">
            <span class="hero-pill-dot"></span>
            <span>Public Advisories</span>
          </button>
          <button type="button" class="hero-pill-btn" onclick="switchTab('schedule')">
            <span class="hero-pill-dot"></span>
            <span>Station Timetable</span>
          </button>
        </div>
      </div>

      <div class="overview-cards-grid">
        <div class="overview-card-item">
          <div class="card-icon-box">🩺</div>
          <h3>Primary Care &amp; Triage</h3>
          <p>Daily general consultations, vitals checking, diagnostic triage, and physician referrals for all registered residents.</p>
        </div>
        <div class="overview-card-item">
          <div class="card-icon-box">📅</div>
          <h3>Outreach Missions</h3>
          <p>Scheduled community health drives, free minor surgeries, pediatric clinics, and dental care across all zones.</p>
        </div>
        <div class="overview-card-item">
          <div class="card-icon-box">💊</div>
          <h3>Medicine Supplies</h3>
          <p>Free maintenance medicine dispensing for hypertension, diabetes, maternal vitamins, and common childhood illnesses.</p>
        </div>
        <div class="overview-card-item">
          <div class="card-icon-box">🚑</div>
          <h3>Emergency Support</h3>
          <p>Immediate wound stabilization, oxygen assistance, and 24/7 coordination with the municipal ambulance team.</p>
        </div>
      </div>

      <div class="staff-portal-cta-banner">
        <div class="staff-cta-copy">
          <strong>Authorized Barangay Healthcare Personnel?</strong>
          <p>Access the administrative workspace to manage resident dossiers, log consultations, track inventory, and register outreach beneficiaries.</p>
        </div>
        <a href="login.php" class="button primary">
          <span>Go to Staff Sign In</span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </a>
      </div>
    </section>

    <!-- =========================================================
         TAB 2: SERVICES PAGE
    ========================================================== -->
    <section class="portal-tab-content <?= $activeTab === 'services' ? 'active' : '' ?>" id="tab-services" role="tabpanel">
      <div class="section-header">
        <p class="eyebrow">COMMUNITY HEALTH OFFERINGS</p>
        <h2>Primary Health &amp; Clinical Services</h2>
        <p class="subtitle">Comprehensive, free, and subsidized medical assistance provided directly to all registered barangay residents.</p>
      </div>

      <div class="services-grid">
        <div class="service-card">
          <div class="service-icon-wrap">🩺</div>
          <h3>General Medical Consultation</h3>
          <p>Primary diagnostic check-up, vital signs screening, basic illness management, and referrals to the Municipal District Hospital.</p>
          <div class="service-meta-footer">
            <span class="badge adult">All Residents</span>
            <span class="muted" style="font-size:10px;">Mon &ndash; Fri &bull; 8AM &ndash; 5PM</span>
          </div>
        </div>

        <div class="service-card">
          <div class="service-icon-wrap">👶</div>
          <h3>Maternal &amp; Child Healthcare</h3>
          <p>Comprehensive prenatal care, postpartum checkups, family planning counseling, and infant nutritional assessment by registered midwives.</p>
          <div class="service-meta-footer">
            <span class="badge pediatric">Mothers &amp; Infants</span>
            <span class="muted" style="font-size:10px;">Tuesdays &bull; 8AM &ndash; 4PM</span>
          </div>
        </div>

        <div class="service-card">
          <div class="service-icon-wrap">💉</div>
          <h3>Routine &amp; Booster Immunization</h3>
          <p>Expanded Program on Immunization (EPI) vaccines including BCG, Polio, Pentavalent, Measles-Rubella, and adult booster shots.</p>
          <div class="service-meta-footer">
            <span class="badge pediatric">Pediatric / Seniors</span>
            <span class="muted" style="font-size:10px;">Wednesdays &bull; 8AM &ndash; 12PM</span>
          </div>
        </div>

        <div class="service-card">
          <div class="service-icon-wrap">💊</div>
          <h3>Maintenance Medicine Dispensing</h3>
          <p>Distribution of free maintenance medicines for hypertension, diabetes, antibiotics, fever, and common childhood illnesses.</p>
          <div class="service-meta-footer">
            <span class="badge senior">Seniors / Chronic</span>
            <span class="muted" style="font-size:10px;">Thursdays &bull; 8AM &ndash; 5PM</span>
          </div>
        </div>

        <div class="service-card">
          <div class="service-icon-wrap">🦷</div>
          <h3>Dental Check &amp; Oral Hygiene</h3>
          <p>Oral health assessment, basic tooth extraction, fluoride application for children, and preventative oral hygiene guidance.</p>
          <div class="service-meta-footer">
            <span class="badge adult">Youth &amp; Adults</span>
            <span class="muted" style="font-size:10px;">Fridays &bull; 9AM &ndash; 3PM</span>
          </div>
        </div>

        <div class="service-card">
          <div class="service-icon-wrap">🚑</div>
          <h3>Emergency First Aid &amp; Triage</h3>
          <p>Immediate wound dressing, stabilization, oxygen support, and 24/7 coordination with the Barangay Disaster &amp; Ambulance Team.</p>
          <div class="service-meta-footer">
            <span class="status ongoing">Emergency</span>
            <span class="muted" style="font-size:10px;">24/7 On-Call Duty</span>
          </div>
        </div>
      </div>

      <div class="service-process-panel">
        <h3 class="process-heading">How to Access Services at the Barangay Health Station</h3>
        <div class="process-steps-row">
          <div class="process-step">
            <div class="step-number">Step 01</div>
            <div class="step-text">Registration &amp; Triage</div>
            <p class="step-detail">Present valid ID or Barangay Resident Certificate at the triage counter.</p>
          </div>
          <div class="process-step">
            <div class="step-number">Step 02</div>
            <div class="step-text">Vitals &amp; Assessment</div>
            <p class="step-detail">BHW records blood pressure, temperature, weight, and initial complaints.</p>
          </div>
          <div class="process-step">
            <div class="step-number">Step 03</div>
            <div class="step-text">Physician Consultation</div>
            <p class="step-detail">Consultation with the visiting municipal doctor or registered nurse.</p>
          </div>
          <div class="process-step">
            <div class="step-number">Step 04</div>
            <div class="step-text">Medicine Dispensing</div>
            <p class="step-detail">Claim prescribed medicines and schedule follow-up appointments.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- =========================================================
         TAB 3: PROGRAMS PAGE
    ========================================================== -->
    <section class="portal-tab-content <?= $activeTab === 'programs' ? 'active' : '' ?>" id="tab-programs" role="tabpanel">
      <div class="section-header">
        <p class="eyebrow">COMMUNITY OUTREACH MISSIONS</p>
        <h2>Health Outreach Programs &amp; Missions</h2>
        <p class="subtitle">Active community medical missions, immunization drives, and nutritional outreach programs organized for residents.</p>
      </div>

      <div class="programs-stats-bar">
        <div class="stat-card">
          <div class="stat-top"><span>Total Scheduled</span></div>
          <strong><?= count($programsList) ?></strong>
          <small class="muted">Outreach drives planned</small>
        </div>
        <div class="stat-card">
          <div class="stat-top"><span>Ongoing Missions</span></div>
          <strong><?= count(array_filter($programsList, fn($p) => ($p['program_status'] ?? '') === 'Ongoing')) ?></strong>
          <small style="color:var(--green); font-weight:600;">Currently serving residents</small>
        </div>
        <div class="stat-card">
          <div class="stat-top"><span>Upcoming Programs</span></div>
          <strong><?= count(array_filter($programsList, fn($p) => ($p['program_status'] ?? '') === 'Upcoming')) ?></strong>
          <small style="color:#ad783a; font-weight:600;">Registration is open</small>
        </div>
      </div>

      <div class="programs-grid">
        <?php if (!empty($programsList)): ?>
          <?php foreach ($programsList as $prog): ?>
            <?php
              $status = htmlspecialchars($prog['program_status'] ?? 'Upcoming');
              $statusClass = strtolower($status);
              $startDate = !empty($prog['start_date']) ? date('M j, Y &bull; g:i A', strtotime($prog['start_date'])) : 'TBD';
            ?>
            <div class="program-card-item">
              <div class="program-card-top">
                <span class="badge adult"><?= htmlspecialchars($prog['program_type'] ?? 'Outreach Mission') ?></span>
                <span class="status <?= $statusClass ?>"><?= $status ?></span>
              </div>
              
              <h3 class="program-title"><?= htmlspecialchars($prog['program_name']) ?></h3>
              <p class="program-desc"><?= htmlspecialchars($prog['description'] ?? 'Community healthcare program providing essential wellness services.') ?></p>

              <div class="program-info-list">
                <div class="program-info-row">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <span><strong>Schedule:</strong> <?= $startDate ?></span>
                </div>
                <div class="program-info-row">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <span><strong>Venue:</strong> <?= htmlspecialchars($prog['venue'] ?? 'Barangay Health Center') ?></span>
                </div>
              </div>

              <div style="font-size:10.5px; color:var(--muted);">
                Participation: Open to all verified barangay residents. Please bring valid proof of residence.
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state" style="grid-column: 1 / -1; background:#fff; border:1px solid var(--line); border-radius:4px;">
            <div class="empty-icon">🏥</div>
            <h2>No Active Programs Found</h2>
            <p>New community health programs will be announced shortly.</p>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- =========================================================
         TAB 4: ADVISORIES PAGE
    ========================================================== -->
    <section class="portal-tab-content <?= $activeTab === 'advisories' ? 'active' : '' ?>" id="tab-advisories" role="tabpanel">
      <div class="section-header">
        <p class="eyebrow">COMMUNITY BULLETINS</p>
        <h2>Health Bulletins &amp; Public Advisories</h2>
        <p class="subtitle">Official community health notices, disease prevention reminders, and seasonal advisories from the Health Committee.</p>
      </div>

      <div class="warning-banner">
        <div class="warning-banner-copy">
          <span style="font-size:16px;">⚠️</span>
          <div>
            <strong>High Priority: Enhanced Dengue Prevention (4S Strategy)</strong>
            <div style="font-size:11px; margin-top:2px;">Residents are urged to conduct daily 4:00 PM Search &amp; Destroy of mosquito breeding sites. Seek early consultation for fevers persisting beyond 2 days.</div>
          </div>
        </div>
      </div>

      <div class="advisories-grid">
        <div class="advisory-card">
          <span class="advisory-date">Updated: November 2026</span>
          <h4>Routine Infant Immunization Drive</h4>
          <p>Parents and guardians are reminded to complete routine infant vaccinations against measles, polio, and diphtheria. Free vaccines available every Wednesday morning.</p>
          <span class="badge pediatric">Maternal &amp; Child Health</span>
        </div>

        <div class="advisory-card">
          <span class="advisory-date">Updated: October 2026</span>
          <h4>Senior Citizen Wellness &amp; Flu Shots</h4>
          <p>Annual influenza vaccination is now available for registered senior citizens and immunocompromised residents. Please bring your Senior Citizen ID.</p>
          <span class="badge senior">Senior Wellness</span>
        </div>

        <div class="advisory-card">
          <span class="advisory-date">Updated: October 2026</span>
          <h4>Water Safety &amp; Leptospirosis Warning</h4>
          <p>Avoid wading in flooded streets. If contact with floodwater is unavoidable, visit the health station immediately for preventive Doxycycline prophylaxis.</p>
          <span class="badge adult">Public Safety</span>
        </div>

        <div class="advisory-card">
          <span class="advisory-date">Updated: September 2026</span>
          <h4>Free Blood Sugar &amp; BP Screening</h4>
          <p>Free non-fasting blood glucose and hypertension screening is conducted every Thursday morning at the barangay hall lobby for adults 35 and older.</p>
          <span class="badge adult">NCD Prevention</span>
        </div>

        <div class="advisory-card">
          <span class="advisory-date">Updated: September 2026</span>
          <h4>Anti-Rabies Animal Vaccination</h4>
          <p>Protect your household by registering domestic pets for annual free rabies vaccinations coordinated with the Municipal Veterinary Office.</p>
          <span class="badge adult">Community Safety</span>
        </div>

        <div class="advisory-card">
          <span class="advisory-date">Emergency Notice</span>
          <h4>Emergency Ambulance Coordination</h4>
          <p>For urgent medical transfers or maternal emergencies during off-hours, contact the Barangay Emergency Desk directly at 0917-123-4567 or Local 104.</p>
          <span class="badge danger">Emergency Dispatch</span>
        </div>
      </div>
    </section>

    <!-- =========================================================
         TAB 5: SCHEDULE PAGE
    ========================================================== -->
    <section class="portal-tab-content <?= $activeTab === 'schedule' ? 'active' : '' ?>" id="tab-schedule" role="tabpanel">
      <div class="section-header">
        <p class="eyebrow">STATION TIMETABLE</p>
        <h2>Barangay Health Center Schedule</h2>
        <p class="subtitle">Weekly clinical schedule, physician consultation hours, visiting specialist dates, and community emergency service availability.</p>
      </div>

      <div class="notice" style="margin-top:0; margin-bottom:20px; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:10px;">
          <span>🕒</span>
          <div>
            <strong>Station Hours: Monday &ndash; Friday &bull; 8:00 AM &ndash; 5:00 PM</strong>
            <p>General checkups, vital signs monitoring, and maintenance medicine claims are accommodated during regular station hours.</p>
          </div>
        </div>
        <span class="status ongoing" style="white-space:nowrap;">● 24/7 Triage Available</span>
      </div>

      <div class="timetable-grid">
        <div class="timetable-card">
          <div class="day-header">
            <strong>Monday</strong>
            <span>General Triage</span>
          </div>
          <div class="day-body">
            <div>
              <span class="session-time">8:00 AM &ndash; 12:00 PM</span>
              <span class="session-title">General Consultation</span>
              <span class="session-assigned">Visiting Doctor / Staff</span>
            </div>
            <div>
              <span class="session-time">1:00 PM &ndash; 5:00 PM</span>
              <span class="session-title">Follow-up &amp; Records</span>
              <span class="session-assigned">Health Workers (BHW)</span>
            </div>
          </div>
        </div>

        <div class="timetable-card">
          <div class="day-header">
            <strong>Tuesday</strong>
            <span>Maternal Care</span>
          </div>
          <div class="day-body">
            <div>
              <span class="session-time">8:00 AM &ndash; 12:00 PM</span>
              <span class="session-title">Prenatal &amp; Postnatal</span>
              <span class="session-assigned">Registered Midwife</span>
            </div>
            <div>
              <span class="session-time">1:00 PM &ndash; 4:00 PM</span>
              <span class="session-title">Family Planning</span>
              <span class="session-assigned">Midwife &amp; Nurse</span>
            </div>
          </div>
        </div>

        <div class="timetable-card">
          <div class="day-header">
            <strong>Wednesday</strong>
            <span>Immunization</span>
          </div>
          <div class="day-body">
            <div>
              <span class="session-time">8:00 AM &ndash; 12:00 PM</span>
              <span class="session-title">Under-5 EPI Vaccines</span>
              <span class="session-assigned">Public Health Nurse</span>
            </div>
            <div>
              <span class="session-time">1:00 PM &ndash; 5:00 PM</span>
              <span class="session-title">TB-DOTS Dispensing</span>
              <span class="session-assigned">Designated Nurse</span>
            </div>
          </div>
        </div>

        <div class="timetable-card">
          <div class="day-header">
            <strong>Thursday</strong>
            <span>Seniors &amp; NCD</span>
          </div>
          <div class="day-body">
            <div>
              <span class="session-time">8:00 AM &ndash; 12:00 PM</span>
              <span class="session-title">Hypertension &amp; Diabetes</span>
              <span class="session-assigned">Municipal Physician</span>
            </div>
            <div>
              <span class="session-time">1:00 PM &ndash; 5:00 PM</span>
              <span class="session-title">Maintenance Supplies</span>
              <span class="session-assigned">Pharmacy Officer</span>
            </div>
          </div>
        </div>

        <div class="timetable-card">
          <div class="day-header">
            <strong>Friday</strong>
            <span>Dental &amp; Sanitation</span>
          </div>
          <div class="day-body">
            <div>
              <span class="session-time">9:00 AM &ndash; 12:00 PM</span>
              <span class="session-title">Dental Consultation</span>
              <span class="session-assigned">Visiting Dentist</span>
            </div>
            <div>
              <span class="session-time">1:00 PM &ndash; 5:00 PM</span>
              <span class="session-title">Station Sanitation</span>
              <span class="session-assigned">All Station Personnel</span>
            </div>
          </div>
        </div>
      </div>
    </section>

  </main>

  <!-- Portal Global Footer -->
  <footer class="portal-footer">
    <div>
      &copy; <?= date('Y') ?> Barangay Health Outreach Management System.
    </div>
    <div class="footer-right-links">
      <a href="javascript:void(0)" onclick="switchTab('services')">Services</a>
      <span>&bull;</span>
      <a href="javascript:void(0)" onclick="switchTab('programs')">Programs</a>
      <span>&bull;</span>
      <a href="javascript:void(0)" onclick="switchTab('advisories')">Advisories</a>
      <span>&bull;</span>
      <a href="javascript:void(0)" onclick="switchTab('schedule')">Schedule</a>
      <span>&bull;</span>
      <a href="login.php">Staff Sign In</a>
    </div>
  </footer>

  <script>
    const validTabs = ['overview', 'services', 'programs', 'advisories', 'schedule'];

    function switchTab(tabName) {
      if (!validTabs.includes(tabName)) {
        tabName = 'overview';
      }

      document.body.setAttribute('data-active-tab', tabName);

      validTabs.forEach(t => {
        const btn = document.getElementById('nav-btn-' + t);
        const panel = document.getElementById('tab-' + t);

        if (btn) {
          if (t === tabName) {
            btn.classList.add('active');
          } else {
            btn.classList.remove('active');
          }
        }

        if (panel) {
          if (t === tabName) {
            panel.classList.add('active');
          } else {
            panel.classList.remove('active');
          }
        }
      });

      if (history.pushState) {
        history.pushState(null, '', '#' + tabName);
      }

      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    window.addEventListener('popstate', function() {
      const hash = window.location.hash.replace('#', '');
      if (validTabs.includes(hash)) {
        switchTab(hash);
      }
    });

    window.addEventListener('DOMContentLoaded', function() {
      const hash = window.location.hash.replace('#', '');
      if (validTabs.includes(hash)) {
        switchTab(hash);
      }
    });
  </script>
</body>
</html>
