<?php
/**
 * SimpleCMS — Homepage
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home';
$pageDesc = 'Welcome to SimpleCMS — a modern blog and portfolio platform.';

// Recent published posts (3)
$recentPosts = mysqli_query($conn, "SELECT bp.*, c.name as category_name FROM blog_posts bp LEFT JOIN categories c ON bp.category_id = c.id WHERE bp.status='published' ORDER BY bp.created_at DESC LIMIT 3");

// Featured portfolio items (3)
$featuredPortfolio = mysqli_query($conn, "SELECT * FROM portfolio_items WHERE featured = 1 ORDER BY created_at DESC LIMIT 3");
// Fallback: if no featured items, get latest 3
if (mysqli_num_rows($featuredPortfolio) === 0) {
    $featuredPortfolio = mysqli_query($conn, "SELECT * FROM portfolio_items ORDER BY created_at DESC LIMIT 3");
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Create. Share. <span>Inspire.</span></h1>
        <p>A modern content management system for showcasing your blog posts and portfolio projects with elegance and simplicity.</p>
        <div style="display:flex;gap:16px;justify-content:center">
            <a href="<?= SITE_URL ?>/blog.php" class="btn btn-primary">Read the Blog</a>
            <a href="<?= SITE_URL ?>/portfolio.php" class="btn btn-outline">View Portfolio</a>
        </div>
    </div>
</section>

<!-- Recent Blog Posts -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <h2>Latest Blog Posts</h2>
            <p>Insights, tutorials, and thoughts on technology and design.</p>
            <div class="section-divider"></div>
        </div>

        <div class="card-grid">
        <?php while ($post = mysqli_fetch_assoc($recentPosts)): ?>
            <article class="card">
                <?php if ($post['featured_image']): ?>
                    <img src="<?= UPLOAD_URL . $post['featured_image'] ?>" alt="<?= sanitize($post['title']) ?>" class="card-img">
                <?php else: ?>
                    <div class="card-img" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--lemon-light),var(--gray-100));font-size:3rem">📝</div>
                <?php endif; ?>
                <div class="card-body">
                    <div class="card-meta">
                        <span class="badge badge-lemon"><?= sanitize($post['category_name'] ?? 'Uncategorized') ?></span>
                        <span><?= formatDate($post['created_at']) ?></span>
                    </div>
                    <h3><a href="<?= SITE_URL ?>/post.php?slug=<?= urlencode($post['slug']) ?>"><?= sanitize($post['title']) ?></a></h3>
                    <p><?= sanitize($post['excerpt'] ?: truncateText($post['content'])) ?></p>
                    <a href="<?= SITE_URL ?>/post.php?slug=<?= urlencode($post['slug']) ?>" class="btn btn-outline btn-sm">Read More →</a>
                </div>
            </article>
        <?php endwhile; ?>
        </div>

        <div style="text-align:center;margin-top:40px">
            <a href="<?= SITE_URL ?>/blog.php" class="btn btn-primary">View All Posts</a>
        </div>
    </div>
</section>

<!-- Featured Portfolio -->
<section class="section" style="background:var(--white)">
    <div class="container">
        <div class="section-header">
            <h2>Featured Projects</h2>
            <p>A selection of recent work and creative projects.</p>
            <div class="section-divider"></div>
        </div>

        <div class="card-grid">
        <?php while ($item = mysqli_fetch_assoc($featuredPortfolio)):
            $images = json_decode($item['images'] ?? '[]', true) ?: [];
            $thumb = !empty($images) ? UPLOAD_URL . $images[0] : '';
        ?>
            <article class="card portfolio-card">
                <?php if ($thumb): ?>
                    <img src="<?= $thumb ?>" alt="<?= sanitize($item['title']) ?>" class="card-img">
                <?php else: ?>
                    <div class="card-img" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--lemon-light),var(--gray-100));font-size:3rem">💼</div>
                <?php endif; ?>
                <div class="portfolio-overlay">
                    <a href="<?= SITE_URL ?>/project.php?slug=<?= urlencode($item['slug']) ?>" class="btn btn-primary">View Project</a>
                </div>
                <div class="card-body">
                    <h3><a href="<?= SITE_URL ?>/project.php?slug=<?= urlencode($item['slug']) ?>"><?= sanitize($item['title']) ?></a></h3>
                    <div class="tech-list">
                        <?php foreach (explode(',', $item['technologies_used'] ?? '') as $tech): ?>
                            <?php if (trim($tech)): ?>
                                <span class="badge badge-tech"><?= sanitize(trim($tech)) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
        </div>

        <div style="text-align:center;margin-top:40px">
            <a href="<?= SITE_URL ?>/portfolio.php" class="btn btn-primary">View All Projects</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
