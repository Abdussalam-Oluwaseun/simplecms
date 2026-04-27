<?php require_once __DIR__ . '/auth_check.php'; $currentUser = getCurrentUser($conn); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?>Admin — <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/admin.css">
</head>
<body class="admin-body">

<button class="sidebar-toggle">☰</button>

<aside class="admin-sidebar">
    <div class="sidebar-brand">Simple<span>CMS</span></div>
    <nav class="sidebar-nav">
        <div class="sidebar-section">Main</div>
        <a href="<?= ADMIN_URL ?>/index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' && strpos($_SERVER['PHP_SELF'], '/admin/posts/') === false && strpos($_SERVER['PHP_SELF'], '/admin/portfolio/') === false ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <div class="sidebar-section">Content</div>
        <a href="<?= ADMIN_URL ?>/posts/" class="<?= strpos($_SERVER['PHP_SELF'], '/admin/posts/') !== false ? 'active' : '' ?>">
            <span class="nav-icon">📝</span> Blog Posts
        </a>
        <a href="<?= ADMIN_URL ?>/portfolio/" class="<?= strpos($_SERVER['PHP_SELF'], '/admin/portfolio/') !== false ? 'active' : '' ?>">
            <span class="nav-icon">💼</span> Portfolio
        </a>
        <div class="sidebar-section">Organize</div>
        <a href="<?= ADMIN_URL ?>/categories.php" class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">
            <span class="nav-icon">📁</span> Categories
        </a>
        <a href="<?= ADMIN_URL ?>/tags.php" class="<?= basename($_SERVER['PHP_SELF']) === 'tags.php' ? 'active' : '' ?>">
            <span class="nav-icon">🏷️</span> Tags
        </a>
    </nav>
    <div class="sidebar-footer">
        <p style="margin-bottom:8px">👤 <?= sanitize($currentUser['username'] ?? 'Admin') ?></p>
        <a href="<?= ADMIN_URL ?>/logout.php">← Logout</a>
        &nbsp;|&nbsp;
        <a href="<?= SITE_URL ?>/" style="color:var(--gold)">View Site</a>
    </div>
</aside>

<main class="admin-main">
<?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div>
<?php endif; ?>
