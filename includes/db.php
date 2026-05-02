<?php
/**
 * SimpleCMS — Database Connection
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'simplecms');
define('DB_PASS', 'simplecms_pass');
define('DB_NAME', 'simple_cms');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');