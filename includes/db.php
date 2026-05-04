<?php
/**
 * SimpleCMS — Database Connection
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}

if (!defined('DB_USER')) {
    define('DB_USER', getenv('DB_USER') ?: 'simplecms');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', getenv('DB_PASS') ?: 'simplecms_pass');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', getenv('DB_NAME') ?: 'simple_cms');
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');