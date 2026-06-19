<?php
/**
 * Velora Admin Login Page
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in?
if (isLoggedIn()) {
    redirect(ADMIN_URL . '/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid form submission. Please try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } elseif (checkLoginLockout($conn, $username, $ipAddress)) {
            $error = 'Too many failed login attempts. Please try again in 15 minutes.';
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                regenerateCSRFToken();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                setFlash('success', 'Welcome back, ' . $user['username'] . '!');
                redirect(ADMIN_URL . '/index.php');
            } else {
                logFailedLogin($conn, $username, $ipAddress);
                $error = 'Invalid username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — <?= SITE_NAME ?> Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/admin.css">
</head>
<body>

<div class="login-page">

    <!-- Left brand panel -->
    <div class="login-brand-panel">
        <div class="orb-3"></div>
        <div class="login-brand-content">
            <div class="login-brand-logo-wrap">
                <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M4 10L10.5 28L18 17L25.5 28L32 10H26.5L18 24L9.5 10H4Z" fill="white" opacity="0.95"/>
                </svg>
            </div>
            <h2>Velora</h2>
            <p>A modern CMS for creators. Manage your blog posts, portfolio projects, and content with elegance.</p>
            <ul class="login-brand-features">
                <li><span class="feat-dot"></span> Rich text blog post editor</li>
                <li><span class="feat-dot"></span> Portfolio project management</li>
                <li><span class="feat-dot"></span> Categories &amp; tag organisation</li>
                <li><span class="feat-dot"></span> Media uploads &amp; image galleries</li>
            </ul>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="login-form-panel">
        <div class="login-card">

            <!-- Logo mark on form side -->
            <div class="login-card-logo">
                <div class="brand-logo" style="width:34px;height:34px">
                    <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="llg" x1="0" y1="0" x2="36" y2="36">
                                <stop offset="0%" stop-color="#7C3AED"/>
                                <stop offset="100%" stop-color="#A855F7"/>
                            </linearGradient>
                        </defs>
                        <rect width="36" height="36" rx="10" fill="url(#llg)"/>
                        <path d="M9 10.5L13.8 23L18 16.8L22.2 23L27 10.5H23.4L18 20.2L12.6 10.5H9Z" fill="white"/>
                    </svg>
                </div>
                <span class="brand-name">Velora</span>
            </div>

            <h1>Welcome back</h1>
            <p class="subtitle">Sign in to your admin panel to manage your content.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="loginForm">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="<?= sanitize($_POST['username'] ?? '') ?>"
                           placeholder="Enter your username"
                           required autofocus autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                               placeholder="Enter your password"
                               required autocomplete="current-password">
                        <button type="button" class="show-password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                            <svg id="eyeIcon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <label for="showPassword" class="checkbox-label">
                        <input type="checkbox" id="showPassword" class="show-password-checkbox">
                        Show password
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                    Sign In
                    <svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <div class="login-footer-links">
                <p>Don't have an account? <a href="<?= ADMIN_URL ?>/register.php">Create one here</a></p>
                <p><a href="<?= SITE_URL ?>/" style="color:var(--v-text-muted)">← Back to site</a></p>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput  = document.getElementById('password');
    const showCheckbox   = document.getElementById('showPassword');
    const toggleBtn      = document.getElementById('togglePassword');

    function syncType() {
        passwordInput.type = showCheckbox.checked ? 'text' : 'password';
    }
    showCheckbox.addEventListener('change', syncType);
    toggleBtn.addEventListener('click', function (e) {
        e.preventDefault();
        showCheckbox.checked = !showCheckbox.checked;
        syncType();
    });

    // Button loading state
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.textContent = 'Signing in…';
        btn.disabled = true;
    });
});
</script>
</body>
</html>
