<?php
/**
 * Login Screen & Authentication Handler
 * Barangay Health Outreach Management System
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

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
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: var(--canvas);
      padding: 24px;
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      background: var(--surface);
      border: 1px solid var(--line);
      padding: 34px 30px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    }
    .login-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 24px;
    }
    .login-header h1 {
      font-size: 20px;
      letter-spacing: -0.5px;
      margin: 0 0 6px;
      color: var(--ink);
    }
    .login-header p {
      margin: 0 0 24px;
      color: var(--muted);
      font-size: 12px;
    }
    .alert {
      padding: 10px 14px;
      font-size: 11px;
      margin-bottom: 18px;
      border-left: 3px solid transparent;
    }
    .alert.error {
      background: #fdf2f2;
      border-color: #d9534f;
      color: #a94442;
    }
    .alert.info {
      background: #f0f7f3;
      border-color: var(--green);
      color: var(--green-dark);
    }
    .login-form label {
      display: block;
      font-size: 11px;
      font-weight: 600;
      color: #4a554e;
      margin-bottom: 15px;
    }
    .login-form input {
      display: block;
      width: 100%;
      margin-top: 6px;
      padding: 11px 12px;
      border: 1px solid var(--line);
      outline: none;
      font-size: 12px;
      background: #fff;
    }
    .login-form input:focus {
      border-color: var(--green);
    }
    .login-form button[type="submit"] {
      width: 100%;
      padding: 12px;
      font-size: 13px;
      font-weight: 600;
      margin-top: 10px;
    }
    .demo-credentials {
      margin-top: 24px;
      padding-top: 18px;
      border-top: 1px solid var(--line);
    }
    .demo-credentials p {
      font-size: 10px;
      font-weight: 700;
      color: var(--muted);
      letter-spacing: 0.6px;
      text-transform: uppercase;
      margin: 0 0 10px;
    }
    .demo-chips {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .demo-chip {
      background: #f5f7f5;
      border: 1px solid var(--line);
      padding: 7px 10px;
      border-radius: 4px;
      font-size: 11px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      cursor: pointer;
      transition: background 0.15s;
    }
    .demo-chip:hover {
      background: var(--green-soft);
      border-color: #cbdad0;
    }
    .demo-chip span {
      color: var(--muted);
      font-family: monospace;
      font-size: 10px;
    }
  </style>
</head>
<body>

<div class="login-container">
  <div class="login-card">
    <div class="login-brand">
      <div class="brand-mark">BH</div>
      <div>
        <strong>Barangay Health</strong>
        <span style="display:block; color:var(--muted); font-size:10px;">Outreach Management</span>
      </div>
    </div>

    <div class="login-header">
      <h1>Sign in to Workspace</h1>
      <p>Enter your authorized healthcare credentials to continue.</p>
    </div>

    <?php if ($loggedOut): ?>
      <div class="alert info">You have been signed out successfully.</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form class="login-form" method="POST" action="login.php">
      <label>
        Username
        <input type="text" name="username" id="username" required autofocus placeholder="e.g. admin or staff">
      </label>

      <label>
        Password
        <input type="password" name="password" id="password" required placeholder="Enter password">
      </label>

      <button type="submit" class="button primary">Sign in</button>
    </form>

    <div class="demo-credentials">
      <p>Quick Test Accounts (Click to auto-fill)</p>
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
</div>

<script>
function fillCreds(u, p) {
  document.getElementById('username').value = u;
  document.getElementById('password').value = p;
  document.getElementById('username').focus();
}
</script>

</body>
</html>
