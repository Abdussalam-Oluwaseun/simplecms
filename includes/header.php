<?php if (!defined('SITE_NAME')) { require_once __DIR__ . '/config.php'; require_once __DIR__ . '/functions.php'; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' — ' . SITE_NAME : SITE_NAME . ' — Blog & Portfolio' ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? sanitize($pageDesc) : 'SimpleCMS — A modern blog and portfolio content management system.' ?>">
    <meta property="og:title" content="<?= isset($pageTitle) ? sanitize($pageTitle) : SITE_NAME ?>">
    <meta property="og:description" content="<?= isset($pageDesc) ? sanitize($pageDesc) : 'SimpleCMS — A modern blog and portfolio CMS.' ?>">
    <meta property="og:type" content="website">
    <link rel="stylesheet" href="<?= SITE_URL ?>/public/css/style.css">
</head>
<body>

<!-- Lightbox -->
<div class="lightbox" id="lightbox">
    <button class="lightbox-close">&times;</button>
    <img id="lightbox-img" src="" alt="Lightbox">
</div>

<nav class="navbar">
    <div class="container">
        <a href="<?= SITE_URL ?>/" class="navbar-brand">Simple<span>CMS</span></a>
        <button class="nav-toggle" aria-label="Toggle navigation">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links">
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
