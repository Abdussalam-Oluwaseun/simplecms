<?php
/**
 * SimpleCMS — Single Blog Post
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (empty($slug)) { redirect(SITE_URL . '/blog.php'); }

$stmt = mysqli_prepare($conn, "SELECT bp.*, c.name as category_name, c.slug as category_slug, u.username as author_name FROM blog_posts bp LEFT JOIN categories c ON bp.category_id = c.id LEFT JOIN users u ON bp.author_id = u.id WHERE bp.slug = ? AND bp.status = 'published'");
mysqli_stmt_bind_param($stmt, 's', $slug);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$post) { http_response_code(404); $pageTitle = 'Not Found'; require_once __DIR__ . '/includes/header.php'; echo '<div class="container section" style="text-align:center"><h1>Post Not Found</h1><p>The post you are looking for does not exist.</p><a href="' . SITE_URL . '/blog.php" class="btn btn-primary" style="margin-top:20px">Back to Blog</a></div>'; require_once __DIR__ . '/includes/footer.php'; exit; }

$pageTitle = $post['title'];
$pageDesc = $post['excerpt'] ?: truncateText($post['content'], 160);

// Get tags for this post
$postTags = mysqli_query($conn, "SELECT t.* FROM tags t JOIN post_tags pt ON t.id = pt.tag_id WHERE pt.post_id = " . intval($post['id']));

// Related posts (same category, exclude current)
$relatedPosts = null;
if ($post['category_id']) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM blog_posts WHERE category_id = ? AND id != ? AND status = 'published' ORDER BY created_at DESC LIMIT 3");
    mysqli_stmt_bind_param($stmt, 'ii', $post['category_id'], $post['id']);
    mysqli_stmt_execute($stmt);
    $relatedPosts = mysqli_stmt_get_result($stmt);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="post-hero">
    <div class="container" style="max-width:800px">
        <div class="card-meta" style="margin-bottom:16px">
            <span class="badge badge-lemon"><?= sanitize($post['category_name'] ?? 'Uncategorized') ?></span>
            <span><?= formatDate($post['created_at']) ?></span>
            <span>by <?= sanitize($post['author_name']) ?></span>
        </div>
        <h1><?= sanitize($post['title']) ?></h1>
    </div>
</section>

<div class="post-content">
    <?php if ($post['featured_image']): ?>
        <img src="<?= UPLOAD_URL . $post['featured_image'] ?>" alt="<?= sanitize($post['title']) ?>" class="post-featured-img">
    <?php endif; ?>

    <?= $post['content'] ?>

    <!-- Tags -->
    <?php if (mysqli_num_rows($postTags) > 0): ?>
    <div style="margin-top:40px;padding-top:24px;border-top:1px solid var(--gray-200)">
        <strong>Tags:</strong>
        <div class="tech-list" style="margin-top:8px">
        <?php while ($tag = mysqli_fetch_assoc($postTags)): ?>
            <a href="<?= SITE_URL ?>/blog.php?tag=<?= urlencode($tag['slug']) ?>" class="badge badge-tech"><?= sanitize($tag['name']) ?></a>
        <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Related Posts -->
<?php if ($relatedPosts && mysqli_num_rows($relatedPosts) > 0): ?>
<section class="section" style="background:var(--white)">
    <div class="container">
        <div class="section-header">
            <h2>Related Posts</h2>
            <div class="section-divider"></div>
        </div>
        <div class="card-grid">
        <?php while ($rp = mysqli_fetch_assoc($relatedPosts)): ?>
            <article class="card">
                <?php if ($rp['featured_image']): ?>
                    <img src="<?= UPLOAD_URL . $rp['featured_image'] ?>" alt="<?= sanitize($rp['title']) ?>" class="card-img">
                <?php else: ?>
                    <div class="card-img" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--lemon-light),var(--gray-100));font-size:3rem">📝</div>
                <?php endif; ?>
                <div class="card-body">
                    <h3><a href="<?= SITE_URL ?>/post.php?slug=<?= urlencode($rp['slug']) ?>"><?= sanitize($rp['title']) ?></a></h3>
                    <p><?= sanitize(truncateText($rp['content'])) ?></p>
                </div>
            </article>
        <?php endwhile; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
