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
define('DB_HOST', 'localhost');
define('DB_USER', 'simplecms');
define('DB_PASS', 'simplecms_pass');
define('DB_NAME', 'simple_cms');

require_once __DIR__ . '/db.php';

// ── Session ─────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}
