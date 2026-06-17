<?php
/**
 * Admin Register Page
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
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
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
                $errors[] = 'Username or Email is already registered.';
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

                // Auto-login
                session_regenerate_id(true);
                regenerateCSRFToken();
                $_SESSION['user_id'] = $userId;
                $_SESSION['username'] = $username;
                setFlash('success', 'Account created successfully! Welcome to your dashboard.');
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
    <title>Register — <?= SITE_NAME ?> Admin</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/admin.css">
</head>
<body>
<div class="login-page">
    <div class="login-card" style="max-width: 450px;">
        <h1>Simple<span>CMS</span></h1>
        <p class="subtitle">Create a new admin account</p>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?= implode('<br>', array_map('sanitize', $errors)) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <?= csrfField() ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= sanitize($_POST['username'] ?? '') ?>" placeholder="Choose a username" required autofocus>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Create a password (min 8 chars)" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
            </div>
            <div class="form-group">
                <label for="showPassword" class="checkbox-label">
                    <input type="checkbox" id="showPassword" class="show-password-checkbox">
                    Show passwords
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Register Account</button>
        </form>

        <p style="text-align:center;margin-top:24px;font-size:.85rem;color:var(--text-light)">
            Already have an account? <a href="<?= ADMIN_URL ?>/login.php">Sign In</a>
        </p>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const showPasswordCheckbox = document.getElementById('showPassword');

    // Handle checkbox toggle
    showPasswordCheckbox.addEventListener('change', function() {
        const type = this.checked ? 'text' : 'password';
        passwordInput.type = type;
        confirmPasswordInput.type = type;
    });
});
</script>
</body>
</html>
