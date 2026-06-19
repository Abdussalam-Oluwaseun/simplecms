<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ' . ADMIN_URL . '/index.php');
    exit;
}

session_destroy();
header('Location: ' . ADMIN_URL . '/login.php');
exit;
