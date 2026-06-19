<?php
/**
 * SimpleCMS — Database Connection
 */

if (!defined('DB_HOST')) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
}

if (!defined('DB_USER')) {
    $dbUser = getenv('DB_USER');
    if ($dbUser === false) {
        die('Database configuration error: DB_USER environment variable is not defined. Please check your .env configuration.');
    }
    define('DB_USER', $dbUser);
}

if (!defined('DB_PASS')) {
    $dbPass = getenv('DB_PASS');
    if ($dbPass === false) {
        die('Database configuration error: DB_PASS environment variable is not defined. Please check your .env configuration.');
    }
    define('DB_PASS', $dbPass);
}

if (!defined('DB_NAME')) {
    $dbName = getenv('DB_NAME');
    if ($dbName === false) {
        die('Database configuration error: DB_NAME environment variable is not defined. Please check your .env configuration.');
    }
    define('DB_NAME', $dbName);
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');