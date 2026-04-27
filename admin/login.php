<?php
/**
 * Admin Login Page
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

        if (empty($username) || empty($password)) {
            $error = 'Please enter both username and password.';
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                setFlash('success', 'Welcome back, ' . $user['username'] . '!');
                redirect(ADMIN_URL . '/index.php');
            } else {
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
    <title>Login — <?= SITE_NAME ?> Admin</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/admin.css">
</head>
<body>
<div class="login-page">
    <div class="login-card">
        <h1>Simple<span>CMS</span></h1>
        <p class="subtitle">Sign in to your admin panel</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= sanitize($_POST['username'] ?? '') ?>" placeholder="Enter your username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <button type="button" class="show-password-toggle" id="togglePassword" aria-label="Toggle password visibility">
                    </button>
                </div>
                <label for="showPassword" class="checkbox-label">
                    <input type="checkbox" id="showPassword" class="show-password-checkbox">
                    Show password
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:.85rem;color:var(--text-light)">
            <a href="<?= SITE_URL ?>/">← Back to site</a>
        </p>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const showPasswordCheckbox = document.getElementById('showPassword');
    const toggleButton = document.getElementById('togglePassword');

    // Handle checkbox toggle
    showPasswordCheckbox.addEventListener('change', function() {
        passwordInput.type = this.checked ? 'text' : 'password';
    });

    // Handle button toggle
    toggleButton.addEventListener('click', function(e) {
        e.preventDefault();
        showPasswordCheckbox.checked = !showPasswordCheckbox.checked;
        showPasswordCheckbox.dispatchEvent(new Event('change'));
    });
});
</script>
</body>
</html>
