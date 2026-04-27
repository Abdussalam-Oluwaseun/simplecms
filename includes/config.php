<?php
/**
 * SimpleCMS — Configuration
 * Database connection, constants, session setup
 */

// Error reporting (disable display in production)
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Timezone
date_default_timezone_set('Europe/London');

// ── Site Constants ──────────────────────────────────────────
define('SITE_NAME', 'SimpleCMS');
define('SITE_URL', 'http://localhost/simplecms');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', SITE_URL . '/public/uploads/');
define('POSTS_PER_PAGE', 9);

// ── Database Connection (mysqli) ────────────────────────────
define('DB_HOST', 'localhost');
define('DB_USER', 'simplecms');
define('DB_PASS', 'simplecms_pass');
define('DB_NAME', 'simple_cms');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, 'utf8mb4');

// ── Session ─────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}
