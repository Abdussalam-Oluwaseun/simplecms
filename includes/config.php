<?php
/**
 * Velora — Configuration
 * Database connection, constants, session setup
 */

// Error reporting (disable display in production)
error_reporting(E_ALL);
$appEnv = getenv('APP_ENV') ?: 'development';
if ($appEnv === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
} else {
    ini_set('display_errors', '1');
}

// Timezone
date_default_timezone_set('Europe/London');

// ── Site Constants ──────────────────────────────────────────
define('SITE_NAME', 'Velora');

// Optional override for environments where URL auto-detection is not desired.
$siteUrlOverride = getenv('SITE_URL');

if ($siteUrlOverride) {
    define('SITE_URL', rtrim($siteUrlOverride, '/'));
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Infer app base path from DOCUMENT_ROOT so it works for both
    // localhost subfolders (/simplecms) and domain roots (/).
    $projectRoot = realpath(__DIR__ . '/..');
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $basePath = '';

    if ($projectRoot && $documentRoot && strpos($projectRoot, $documentRoot) === 0) {
        $relativePath = substr($projectRoot, strlen($documentRoot));
        $basePath = rtrim(str_replace('\\', '/', $relativePath), '/');
    }

    define('SITE_URL', $scheme . '://' . $host . $basePath);
}

define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', SITE_URL . '/public/uploads/');
define('POSTS_PER_PAGE', 9);

require_once __DIR__ . '/db.php';

// ── Session ─────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    session_start();
}
