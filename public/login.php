<?php
/**
 * Dedicated Login Page & Authentication Handler
 * Barangay Health Outreach Management System
 * 
 * Standalone dedicated authentication interface visually aligned
 * with the website's original design system, theme tokens, and typography.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Redirect to dashboard if already authenticated
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$loggedOut = isset($_GET['logged_out']);
$rememberedUser = $_COOKIE['bhs_remembered_username'] ?? '';

// Authentication processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = !empty($_POST['remember']);

    if ($username === '' || $password === '') {
        $error = 'Please enter both your username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['user_id']   = (int)$user['user_id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];

                // Cookie persistence for Remember Me
                if ($remember) {
                    setcookie('bhs_remembered_username', $username, time() + (86400 * 30), '/', '', false, true);
                } else {
                    setcookie('bhs_remembered_username', '', time() - 3600, '/', '', false, true);
                }

                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'A database error occurred. Please contact the administrator.';
            error_log("Login error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - Barangay Health Outreach System</title>
  <link rel="stylesheet" href="assets/css/style.css">
  
  <style>
    /* -------------------------------------------------------------
       Dedicated Login View Layout
       Uses style.css tokens:
       --green: #245b45, --green-dark: #194633, --green-soft: #eaf2ed
       --ink: #202923, --muted: #7b857e, --line: #e8ece9, --canvas: #f7f9f7
    -------------------------------------------------------------- */
    body {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    /* Soft sage ambient canvas */
    .login-backdrop {
      position: fixed;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      background:
        radial-gradient(ellipse 65% 55% at 85% 20%, rgba(36, 91, 69, 0.08) 0%, transparent 60%),
        radial-gradient(ellipse 55% 50% at 15% 85%, rgba(36, 91, 69, 0.04) 0%, transparent 55%),
        linear-gradient(135deg, #f7f9f7 0%, #edf3ef 50%, #f4f8f5 100%);
    }

    .login-wave-svg {
      position: absolute;
      top: -8%;
      right: -8%;
      width: 65vw;
      max-width: 900px;
      height: 120vh;
      opacity: 0.55;
      transform: rotate(-2deg);
    }

    /* Top Navigation Bar with Return Link */
    .login-topbar {
      position: relative;
      z-index: 10;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 16px 44px;
      background: var(--surface);
      border-bottom: 1px solid var(--line);
    }

    .topbar-tag {
      font-size: 11px;
      color: var(--muted);
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .topbar-tag-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--green);
    }

    /* Centered Viewport */
    .login-stage {
      position: relative;
      z-index: 10;
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 34px 20px 48px;
    }

    /* The Login Card (Matches Reference Image) */
    .login-card {
      width: 100%;
      max-width: 430px;
      background: var(--surface);
      border: 1px solid var(--line);
      padding: 36px 32px 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
      border-radius: 4px;
      position: relative;
    }

    .card-top-stripe {
      position: absolute;
      top: -1px;
      left: -1px;
      right: -1px;
      height: 3px;
      background: var(--green);
      border-top-left-radius: 4px;
      border-top-right-radius: 4px;
    }

    .card-brand-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
    }

    .card-heading-group {
      margin-bottom: 20px;
    }
    .card-heading-group h1 {
      font-size: 21px;
      letter-spacing: -0.5px;
      margin: 0 0 5px;
      color: var(--ink);
      font-weight: 680;
    }
    .card-heading-group p {
      margin: 0;
      color: var(--muted);
      font-size: 12px;
    }

    /* Password Eye Toggle */
    .toggle-visibility-btn {
      position: absolute;
      right: 8px;
      top: 50%;
      transform: translateY(-50%);
      width: 28px;
      height: 28px;
      background: none;
      border: none;
      color: var(--muted);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      border-radius: 3px;
      padding: 0;
      transition: color 0.15s, background 0.15s;
    }
    .toggle-visibility-btn:hover {
      color: var(--ink);
      background: #f0f2f0;
    }
    .toggle-visibility-btn svg {
      width: 16px;
      height: 16px;
      stroke: currentColor;
      stroke-width: 2;
      fill: none;
    }

    /* Options Row */
    .form-options-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-top: 12px;
      margin-bottom: 20px;
      font-size: 11px;
    }
    .remember-label {
      display: flex;
      align-items: center;
      gap: 7px;
      cursor: pointer;
      user-select: none;
      color: #556259;
      font-weight: 500;
    }
    .remember-checkbox {
      accent-color: var(--green);
      cursor: pointer;
      width: 14px;
      height: 14px;
    }

    /* Inline Help Notice */
    .inline-notice {
      display: none;
      background: #f4f7f5;
      border: 1px solid #d6e2d9;
      padding: 10px 12px;
      border-radius: 4px;
      margin-bottom: 14px;
      font-size: 11px;
      color: #3b4e42;
      line-height: 1.45;
    }

    /* Submit Button (Full width with arrow) */
    .btn-login-submit {
      width: 100%;
      padding: 12px;
      font-size: 13px;
      font-weight: 600;
    }
    .btn-spinner {
      display: none;
      width: 14px;
      height: 14px;
      border: 2px solid rgba(255, 255, 255, 0.4);
      border-radius: 50%;
      border-top-color: #ffffff;
      animation: spin 0.8s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .btn-login-submit.is-loading .btn-spinner { display: inline-block; }
    .btn-login-submit.is-loading .btn-text-label,
    .btn-login-submit.is-loading .btn-arrow-icon { display: none; }
    .btn-login-submit:disabled { opacity: 0.8; cursor: not-allowed; }

    /* Quick Test Accounts */
    .demo-credentials-section {
      margin-top: 24px;
      padding-top: 18px;
      border-top: 1px solid var(--line);
    }
    .demo-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    .demo-header-row span {
      font-size: 10px;
      font-weight: 700;
      color: var(--muted);
      letter-spacing: 0.6px;
      text-transform: uppercase;
    }
    .demo-header-row small {
      font-size: 10px;
      color: var(--green);
      font-weight: 600;
    }

    /* Footer */
    .login-footer {
      position: relative;
      z-index: 10;
      text-align: center;
      padding: 16px 20px;
      font-size: 11px;
      color: var(--muted);
      border-top: 1px solid var(--line);
      background: var(--surface);
    }

    @media (max-width: 700px) {
      .login-topbar { padding: 12px 16px; }
      .login-stage { padding: 20px 14px 34px; }
      .login-card { padding: 26px 20px; }
    }
  </style>
</head>
<body>

  <!-- Ambient Backdrop -->
  <div class="login-backdrop">
    <svg class="login-wave-svg" viewBox="0 0 1000 1000" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
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

  <!-- Topbar with Back to Website Button -->
  <header class="login-topbar">
    <a href="index.php" class="button secondary" title="Return to public portal landing page">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="19" y1="12" x2="5" y2="12"></line>
        <polyline points="12 19 5 12 12 19"></polyline>
      </svg>
      <span>Back to Website</span>
    </a>

    <div class="topbar-tag">
      <span class="topbar-tag-dot"></span>
      <span>Official Healthcare Workspace</span>
    </div>
  </header>

  <!-- Centered Login Stage -->
  <main class="login-stage">
    <div class="login-card">
      <div class="card-top-stripe"></div>

      <!-- Brand Header -->
      <div class="card-brand-header">
        <div class="brand-mark">BH</div>
        <div>
          <strong>Barangay Health</strong>
          <span>Outreach Management</span>
        </div>
      </div>

      <!-- Heading -->
      <div class="card-heading-group">
        <h1>Sign in to Workspace</h1>
        <p>Enter your authorized healthcare credentials to continue.</p>
      </div>

      <!-- Logout Alert (Matching Screenshot) -->
      <?php if ($loggedOut): ?>
        <div class="alert info">You have been signed out successfully.</div>
      <?php endif; ?>

      <!-- Error Alert -->
      <?php if (!empty($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- Client-side Validation Alert -->
      <div class="alert error" id="client-error" style="display:none;"></div>

      <!-- Authentication Form -->
      <form id="loginForm" method="POST" action="login.php" novalidate>
        
        <!-- Username Field -->
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <div class="input-wrap">
            <span class="input-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
            </span>
            <input 
              type="text" 
              name="username" 
              id="username" 
              class="input-field" 
              required 
              autocomplete="username"
              value="<?= htmlspecialchars($rememberedUser) ?>"
              placeholder="e.g. admin or staff"
              <?= empty($rememberedUser) ? 'autofocus' : '' ?>
            >
          </div>
        </div>

        <!-- Password Field with Visibility Toggle -->
        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-wrap">
            <span class="input-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </span>
            <input 
              type="password" 
              name="password" 
              id="password" 
              class="input-field has-toggle" 
              required 
              autocomplete="current-password"
              placeholder="Enter password"
              <?= !empty($rememberedUser) ? 'autofocus' : '' ?>
            >
            <button 
              type="button" 
              class="toggle-visibility-btn" 
              id="passwordToggleBtn" 
              aria-label="Show password"
              title="Toggle password visibility"
              onclick="togglePassword()"
            >
              <svg id="eyeShowIcon" viewBox="0 0 24 24">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
              <svg id="eyeHideIcon" viewBox="0 0 24 24" style="display:none;">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Inline Notice for Forgot Password -->
        <div class="inline-notice" id="forgotNotice">
          <strong>Password Reset Assistance:</strong> To protect community health records, password resets are processed by the Barangay Health Administrator or RHU Desk (Local Ext. 104).
        </div>

        <!-- Options: Remember Me & Forgot Password -->
        <div class="form-options-row">
          <label class="remember-label" for="remember">
            <input 
              type="checkbox" 
              name="remember" 
              id="remember" 
              class="remember-checkbox" 
              value="1"
              <?= !empty($rememberedUser) ? 'checked' : '' ?>
            >
            <span>Remember username</span>
          </label>

          <button type="button" class="text-button" onclick="toggleForgotNotice()">
            Forgot password?
          </button>
        </div>

        <!-- Submit Button (Sign in ->) -->
        <button type="submit" class="button primary btn-login-submit" id="submitBtn">
          <span class="btn-spinner" aria-hidden="true"></span>
          <span class="btn-text-label">Sign in</span>
          <svg class="btn-arrow-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </button>
      </form>

      <!-- Quick Test Accounts (Matches Screenshot) -->
      <div class="demo-credentials-section">
        <div class="demo-header-row">
          <span>QUICK TEST ACCOUNTS</span>
          <small>Click to auto-fill</small>
        </div>
        <div class="demo-chips">
          <div class="demo-chip" onclick="fillCreds('admin', 'admin123')">
            <strong>Administrator</strong>
            <span>admin / admin123</span>
          </div>
          <div class="demo-chip" onclick="fillCreds('staff', 'staff123')">
            <strong>Barangay Staff</strong>
            <span>staff / staff123</span>
          </div>
          <div class="demo-chip" onclick="fillCreds('bhw', 'bhw123')">
            <strong>Health Worker (BHW)</strong>
            <span>bhw / bhw123</span>
          </div>
        </div>
      </div>

    </div>
  </main>

  <!-- Login Footer -->
  <footer class="login-footer">
    <div>
      &copy; <?= date('Y') ?> Barangay Health Outreach Management System &bull; <a href="index.php">Return to Public Portal</a>
    </div>
  </footer>

  <script>
    // Password visibility toggle
    function togglePassword() {
      const pwd = document.getElementById('password');
      const eyeShow = document.getElementById('eyeShowIcon');
      const eyeHide = document.getElementById('eyeHideIcon');
      const btn = document.getElementById('passwordToggleBtn');

      if (pwd.type === 'password') {
        pwd.type = 'text';
        eyeShow.style.display = 'none';
        eyeHide.style.display = 'block';
        btn.setAttribute('aria-label', 'Hide password');
      } else {
        pwd.type = 'password';
        eyeShow.style.display = 'block';
        eyeHide.style.display = 'none';
        btn.setAttribute('aria-label', 'Show password');
      }
    }

    // Auto-fill Demo Credentials
    function fillCreds(u, p) {
      const usernameInput = document.getElementById('username');
      const passwordInput = document.getElementById('password');
      const clientError = document.getElementById('client-error');

      if (clientError) clientError.style.display = 'none';

      usernameInput.value = u;
      passwordInput.value = p;
      passwordInput.focus();
    }

    // Inline Forgot Password Notice Toggle
    function toggleForgotNotice() {
      const notice = document.getElementById('forgotNotice');
      if (notice) {
        notice.style.display = (notice.style.display === 'block') ? 'none' : 'block';
      }
    }

    // Form submission validation & loading state
    const form = document.getElementById('loginForm');
    const submitBtn = document.getElementById('submitBtn');
    const clientError = document.getElementById('client-error');

    if (form) {
      form.addEventListener('submit', function(e) {
        const u = document.getElementById('username').value.trim();
        const p = document.getElementById('password').value;

        if (!u || !p) {
          e.preventDefault();
          clientError.textContent = 'Please enter both your username and password.';
          clientError.style.display = 'block';
          if (!u) {
            document.getElementById('username').focus();
          } else {
            document.getElementById('password').focus();
          }
          return false;
        }

        clientError.style.display = 'none';
        submitBtn.disabled = true;
        submitBtn.classList.add('is-loading');
      });
    }
  </script>
</body>
</html>
