<?php if (!defined('SITE_NAME')) { require_once __DIR__ . '/config.php'; require_once __DIR__ . '/functions.php'; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' . SITE_NAME : SITE_NAME . ' — Blog & Portfolio' ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? sanitize($pageDesc) : 'Velora — A modern blog and portfolio platform for creators.' ?>">
    <meta property="og:title" content="<?= isset($pageTitle) ? sanitize($pageTitle) : SITE_NAME ?>">
    <meta property="og:description" content="<?= isset($pageDesc) ? sanitize($pageDesc) : 'Velora — A modern blog and portfolio CMS.' ?>">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/style.css">
</head>
<body>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close" id="lightboxClose">&times;</button>
    <img id="lightbox-img" src="" alt="Lightbox image">
</div>

<nav class="navbar" id="mainNavbar">
    <div class="container">

        <!-- Brand / Logo -->
        <a href="<?= SITE_URL ?>/" class="navbar-brand">
            <div class="navbar-brand-logo">
                <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="nlg" x1="0" y1="0" x2="36" y2="36">
                            <stop offset="0%" stop-color="#cb6441"/>
                            <stop offset="100%" stop-color="#e07d5b"/>
                        </linearGradient>
                    </defs>
                    <rect width="36" height="36" rx="10" fill="url(#nlg)"/>
                    <path d="M9 10.5L13.8 23L18 16.8L22.2 23L27 10.5H23.4L18 20.2L12.6 10.5H9Z" fill="white"/>
                </svg>
            </div>
            <span class="navbar-brand-name">Velora</span>
        </a>

        <!-- Mobile toggle -->
        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Nav links -->
        <ul class="nav-links" id="navLinks">
            <li><a href="<?= SITE_URL ?>/" <?= basename($_SERVER['PHP_SELF']) === 'index.php' && !isset($_GET['slug']) ? 'class="active"' : '' ?>>Home</a></li>
            <li><a href="<?= SITE_URL ?>/blog.php" <?= basename($_SERVER['PHP_SELF']) === 'blog.php' ? 'class="active"' : '' ?>>Blog</a></li>
            <li><a href="<?= SITE_URL ?>/portfolio.php" <?= basename($_SERVER['PHP_SELF']) === 'portfolio.php' ? 'class="active"' : '' ?>>Portfolio</a></li>
            <li><a href="<?= SITE_URL ?>/search.php" <?= basename($_SERVER['PHP_SELF']) === 'search.php' ? 'class="active"' : '' ?>>Search</a></li>
        </ul>

    </div>
</nav>

<?php $flash = getFlash(); if ($flash): ?>
<div class="container" style="margin-top:20px">
    <div class="alert alert-<?= $flash['type'] ?>"><?= sanitize($flash['message']) ?></div>
</div>
<?php endif; ?>
