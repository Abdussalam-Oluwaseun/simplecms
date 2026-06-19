<?php
/**
 * Velora Admin Register Page
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Already logged in?
if (isLoggedIn()) {
    redirect(ADMIN_URL . '/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $errors[] = 'All fields are required.';
        }
        if (strlen($username) < 3 || strlen($username) > 20) {
            $errors[] = 'Username must be between 3 and 20 characters.';
        }
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        // Check if username or email already exists
        if (empty($errors)) {
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
            mysqli_stmt_bind_param($stmt, 'ss', $username, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors[] = 'Username or email is already registered.';
            }
            mysqli_stmt_close($stmt);
        }

        // Register user
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $hashedPassword);
            if (mysqli_stmt_execute($stmt)) {
                $userId = mysqli_insert_id($conn);
                mysqli_stmt_close($stmt);
                session_regenerate_id(true);
                regenerateCSRFToken();
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                setFlash('success', 'Account created! Welcome to Velora, ' . $username . '.');
                redirect(ADMIN_URL . '/index.php');
            } else {
                $errors[] = 'Registration failed. Please try again.';
                mysqli_stmt_close($stmt);
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
    <title>Create Account — <?= SITE_NAME ?> Admin</title>
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
            <h2>Join Velora</h2>
            <p>Create your admin account and start managing your content beautifully. It only takes a moment.</p>
            <ul class="login-brand-features">
                <li><span class="feat-dot"></span> Full dashboard access</li>
                <li><span class="feat-dot"></span> Create &amp; publish blog posts</li>
                <li><span class="feat-dot"></span> Manage portfolio projects</li>
                <li><span class="feat-dot"></span> Organise with tags &amp; categories</li>
            </ul>
        </div>
    </div>

    <!-- Right form panel -->
    <div class="login-form-panel">
        <div class="login-card">

            <div class="login-card-logo">
                <div class="brand-logo" style="width:34px;height:34px">
                    <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="rlg" x1="0" y1="0" x2="36" y2="36">
                                <stop offset="0%" stop-color="#7C3AED"/>
                                <stop offset="100%" stop-color="#A855F7"/>
                            </linearGradient>
                        </defs>
                        <rect width="36" height="36" rx="10" fill="url(#rlg)"/>
                        <path d="M9 10.5L13.8 23L18 16.8L22.2 23L27 10.5H23.4L18 20.2L12.6 10.5H9Z" fill="white"/>
                    </svg>
                </div>
                <span class="brand-name">Velora</span>
            </div>

            <h1>Create account</h1>
            <p class="subtitle">Set up your admin account to get started.</p>

            <?php if ($errors): ?>
                <div class="alert alert-error">
                    <?= implode('<br>', array_map('sanitize', $errors)) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="registerForm">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="<?= sanitize($_POST['username'] ?? '') ?>"
                           placeholder="Choose a username (3–20 chars)"
                           required autofocus autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= sanitize($_POST['email'] ?? '') ?>"
                           placeholder="Enter your email address"
                           required autocomplete="email">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Min. 8 characters"
                               required autocomplete="new-password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               placeholder="Repeat password"
                               required autocomplete="new-password">
                    </div>
                </div>
                <div class="form-group">
                    <label for="showPassword" class="checkbox-label">
                        <input type="checkbox" id="showPassword" class="show-password-checkbox">
                        Show passwords
                    </label>
                </div>
                <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                    Create Account
                    <svg viewBox="0 0 24 24"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                </button>
            </form>

            <div class="login-footer-links">
                <p>Already have an account? <a href="<?= ADMIN_URL ?>/login.php">Sign in here</a></p>
                <p><a href="<?= SITE_URL ?>/" style="color:var(--v-text-muted)">← Back to site</a></p>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const passwordInput  = document.getElementById('password');
    const confirmInput   = document.getElementById('confirm_password');
    const showCheckbox   = document.getElementById('showPassword');

    showCheckbox.addEventListener('change', function () {
        const type = this.checked ? 'text' : 'password';
        passwordInput.type = type;
        confirmInput.type  = type;
    });

    document.getElementById('registerForm').addEventListener('submit', function () {
        const btn = document.getElementById('submitBtn');
        btn.textContent = 'Creating account…';
        btn.disabled = true;
    });
});
</script>
</body>
</html>
