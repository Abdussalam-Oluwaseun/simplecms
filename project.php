<?php
/**
 * SimpleCMS — Single Portfolio Project
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) { redirect(SITE_URL . '/portfolio.php'); }

$stmt = mysqli_prepare($conn, "SELECT * FROM portfolio_items WHERE slug = ?");
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$item) { http_response_code(404); $pageTitle = 'Not Found'; require_once __DIR__ . '/includes/header.php'; echo '<div class="container section" style="text-align:center"><h1>Project Not Found</h1><p>The project you are looking for does not exist.</p><a href="' . SITE_URL . '/portfolio.php" class="btn btn-primary" style="margin-top:20px">Back to Portfolio</a></div>'; require_once __DIR__ . '/includes/footer.php'; exit; }

$pageTitle = $item['title'];
$pageDesc = truncateText($item['description'], 160);
$images = json_decode($item['images'] ?? '[]', true) ?: [];
$technologies = array_filter(array_map('trim', explode(',', $item['technologies_used'] ?? '')));

// Related projects (same category, exclude current)
$related = null;
if ($item['category']) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM portfolio_items WHERE category = ? AND id != ? ORDER BY created_at DESC LIMIT 3");
    mysqli_stmt_bind_param($stmt, 'si', $item['category'], $item['id']);
    mysqli_stmt_execute($stmt);
    $related = mysqli_stmt_get_result($stmt);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="post-hero">
    <div class="container">
        <div class="card-meta" style="margin-bottom:16px">
            <?php if ($item['category']): ?><span class="badge badge-lemon"><?= sanitize($item['category']) ?></span><?php endif; ?>
            <span><?= formatDate($item['created_at']) ?></span>
            <?php if ($item['featured']): ?><span class="badge badge-success">⭐ Featured</span><?php endif; ?>
        </div>
        <h1><?= sanitize($item['title']) ?></h1>
    </div>
</section>

<div class="post-content">

    <!-- Image Gallery -->
    <?php if ($images): ?>
    <div class="gallery-grid">
        <?php foreach ($images as $img): ?>
            <img src="<?= UPLOAD_URL . $img ?>" alt="<?= sanitize($item['title']) ?>">
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Description -->
    <?= $item['description'] ?>

    <!-- Project Details -->
    <div style="margin-top:40px;padding:24px;background:var(--gray-50);border-radius:var(--radius);border:1px solid var(--gray-200)">
        <h3 style="margin-bottom:16px">Project Details</h3>

        <?php if ($technologies): ?>
        <p><strong>Technologies:</strong></p>
        <div class="tech-list" style="margin-bottom:16px">
            <?php foreach ($technologies as $tech): ?>
                <span class="badge badge-tech"><?= sanitize($tech) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($item['project_url']): ?>
        <p style="margin-top:16px">
            <a href="<?= sanitize($item['project_url']) ?>" target="_blank" rel="noopener" class="btn btn-primary">
                Visit Project →
            </a>
        </p>
        <?php endif; ?>
    </div>
</div>

<!-- Related Projects -->
<?php if ($related && mysqli_num_rows($related) > 0): ?>
<section class="section" style="background:var(--white)">
    <div class="container">
        <div class="section-header">
            <h2>Related Projects</h2>
            <div class="section-divider"></div>
        </div>
        <div class="card-grid">
        <?php while ($rp = mysqli_fetch_assoc($related)):
            $rpImages = json_decode($rp['images'] ?? '[]', true) ?: [];
            $rpThumb = !empty($rpImages) ? UPLOAD_URL . $rpImages[0] : '';
        ?>
            <article class="card">
                <?php if ($rpThumb): ?>
                    <img src="<?= $rpThumb ?>" alt="<?= sanitize($rp['title']) ?>" class="card-img">
                <?php else: ?>
                    <div class="card-img" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--lemon-light),var(--gray-100));font-size:3rem">💼</div>
                <?php endif; ?>
                <div class="card-body">
                    <h3><a href="<?= SITE_URL ?>/project.php?slug=<?= urlencode($rp['slug']) ?>"><?= sanitize($rp['title']) ?></a></h3>
                    <p><?= sanitize(truncateText($rp['description'])) ?></p>
                </div>
            </article>
        <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
