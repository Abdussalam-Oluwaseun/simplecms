<?php require_once __DIR__ . '/auth_check.php'; $currentUser = getCurrentUser($conn); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' : '' ?>Admin — <?= SITE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/admin.css">
</head>
<body class="admin-body">

<!-- Mobile sidebar toggle -->
<button class="sidebar-toggle" id="sidebarToggle" aria-label="Open navigation">
    <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>

<aside class="admin-sidebar" id="adminSidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="<?= ADMIN_URL ?>/index.php" class="brand-link">
            <div class="brand-logo">
                <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="vlg" x1="0" y1="0" x2="36" y2="36">
                            <stop offset="0%" stop-color="#cb6441"/>
                            <stop offset="100%" stop-color="#e07d5b"/>
                        </linearGradient>
                    </defs>
                    <rect width="36" height="36" rx="10" fill="url(#vlg)"/>
                    <path d="M9 10.5L13.8 23L18 16.8L22.2 23L27 10.5H23.4L18 20.2L12.6 10.5H9Z" fill="white"/>
                </svg>
            </div>
            <span class="brand-name">Velora</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <div class="sidebar-section">Main</div>
        <a href="<?= ADMIN_URL ?>/index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' && strpos($_SERVER['PHP_SELF'], '/admin/posts/') === false && strpos($_SERVER['PHP_SELF'], '/admin/portfolio/') === false ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
            </span>
            Dashboard
        </a>

        <div class="sidebar-section">Content</div>
        <a href="<?= ADMIN_URL ?>/posts/" class="<?= strpos($_SERVER['PHP_SELF'], '/admin/posts/') !== false ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8L14 2z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
            </span>
            Blog Posts
        </a>
        <a href="<?= ADMIN_URL ?>/portfolio/" class="<?= strpos($_SERVER['PHP_SELF'], '/admin/portfolio/') !== false ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
            </span>
            Portfolio
        </a>

        <div class="sidebar-section">Organize</div>
        <a href="<?= ADMIN_URL ?>/categories.php" class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </span>
            Categories
        </a>
        <a href="<?= ADMIN_URL ?>/tags.php" class="<?= basename($_SERVER['PHP_SELF']) === 'tags.php' ? 'active' : '' ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </span>
            Tags
        </a>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar"><?= strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)) ?></div>
            <span class="sidebar-username"><?= sanitize($currentUser['username'] ?? 'Admin') ?></span>
        </div>
        <div class="sidebar-footer-links">
            <form method="POST" action="<?= ADMIN_URL ?>/logout.php" style="flex: 1; display: flex; margin: 0; padding: 0;">
                <?= csrfField() ?>
                <button type="submit" class="logout-link" title="Logout" style="flex: 1; border: none; background: transparent; font-family: inherit; font-size: inherit; color: inherit; display: flex; align-items: center; justify-content: center; gap: 5px; cursor: pointer; padding: 8px; border-radius: var(--v-radius-sm); border: 1px solid transparent; transition: all var(--v-transition);">
                    <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 00-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Logout
                </button>
            </form>
            <a href="<?= SITE_URL ?>/" target="_blank" title="View Site">
                <svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                View Site
            </a>
        </div>
    </div>

</aside>

<!-- Global Topbar -->
<header class="velora-topbar">
    <div class="topbar-left">
        <form class="topbar-search" action="<?= SITE_URL ?>/search.php" method="GET" role="search">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input type="text" name="q" placeholder="Search posts &amp; content…" autocomplete="off">
        </form>
    </div>
    <div class="topbar-right">
        <a href="<?= SITE_URL ?>/" class="topbar-site-link" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
            <span>View Site</span>
        </a>
        <div class="topbar-divider"></div>
        <div class="topbar-user">
            <div class="topbar-avatar"><?= strtoupper(substr($currentUser['username'] ?? 'A', 0, 1)) ?></div>
            <span class="topbar-username"><?= sanitize($currentUser['username'] ?? 'Admin') ?></span>
            <svg class="topbar-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
        </div>
    </div>
</header>

<main class="admin-main">
<?php $flash = getFlash(); if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div>
<?php endif; ?>
