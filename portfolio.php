<?php
/**
 * SimpleCMS — Portfolio Gallery
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Portfolio';
$pageDesc = 'Browse our portfolio of creative projects and web applications.';

$items = mysqli_query($conn, "SELECT * FROM portfolio_items ORDER BY featured DESC, created_at DESC");

// Get unique categories
$catResult = mysqli_query($conn, "SELECT DISTINCT category FROM portfolio_items WHERE category IS NOT NULL AND category != '' ORDER BY category");
$portfolioCategories = [];
while ($r = mysqli_fetch_assoc($catResult)) { $portfolioCategories[] = $r['category']; }

require_once __DIR__ . '/includes/header.php';
?>

<section class="post-hero">
    <div class="container">
        <h1>Portfolio</h1>
        <p style="color:var(--text-light);margin-top:8px">A showcase of our creative work and projects.</p>
    </div>
</section>

<section class="section">
    <div class="container">

        <?php if ($portfolioCategories): ?>
        <div class="portfolio-filters">
            <button class="filter-btn active" data-filter="all">All</button>
            <?php foreach ($portfolioCategories as $cat): ?>
                <button class="filter-btn" data-filter="<?= sanitize($cat) ?>"><?= sanitize($cat) ?></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="card-grid">
        <?php while ($item = mysqli_fetch_assoc($items)):
            $images = json_decode($item['images'] ?? '[]', true) ?: [];
            $thumb = !empty($images) ? UPLOAD_URL . $images[0] : '';
        ?>
            <article class="card portfolio-card portfolio-item" data-category="<?= sanitize($item['category'] ?? '') ?>">
                <?php if ($thumb): ?>
                    <img src="<?= $thumb ?>" alt="<?= sanitize($item['title']) ?>" class="card-img">
                <?php else: ?>
                    <div class="card-img" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--lemon-light),var(--gray-100));font-size:3rem">💼</div>
                <?php endif; ?>
                <div class="portfolio-overlay">
                    <a href="<?= SITE_URL ?>/project.php?slug=<?= urlencode($item['slug']) ?>" class="btn btn-primary">View Details</a>
                </div>
                <div class="card-body">
                    <h3><a href="<?= SITE_URL ?>/project.php?slug=<?= urlencode($item['slug']) ?>"><?= sanitize($item['title']) ?></a></h3>
                    <p><?= sanitize(truncateText($item['description'])) ?></p>
                    <div class="tech-list">
                        <?php foreach (array_slice(explode(',', $item['technologies_used'] ?? ''), 0, 4) as $tech): ?>
                            <?php if (trim($tech)): ?>
                                <span class="badge badge-tech"><?= sanitize(trim($tech)) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
