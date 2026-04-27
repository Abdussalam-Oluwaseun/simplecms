<?php
/**
 * SimpleCMS — Blog Listing Page
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Blog';
$pageDesc = 'Browse all blog posts on SimpleCMS.';

// Filters
$catSlug = $_GET['category'] ?? '';
$tagSlug = $_GET['tag'] ?? '';

$where = "WHERE bp.status = 'published'";
$params = [];
$types = '';

if ($catSlug) {
    $where .= " AND c.slug = ?";
    $params[] = $catSlug;
    $types .= 's';
}
if ($tagSlug) {
    $where .= " AND bp.id IN (SELECT post_id FROM post_tags pt JOIN tags t ON pt.tag_id = t.id WHERE t.slug = ?)";
    $params[] = $tagSlug;
    $types .= 's';
}

// Count
$countSql = "SELECT COUNT(*) as c FROM blog_posts bp LEFT JOIN categories c ON bp.category_id = c.id $where";
if ($types) {
    $stmt = mysqli_prepare($conn, $countSql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $total = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];
    mysqli_stmt_close($stmt);
} else {
    $total = mysqli_fetch_assoc(mysqli_query($conn, $countSql))['c'];
}

$page = max(1, intval($_GET['page'] ?? 1));
$pagination = getPagination($total, POSTS_PER_PAGE, $page);

$sql = "SELECT bp.*, c.name as category_name, c.slug as category_slug, u.username as author_name
        FROM blog_posts bp
        LEFT JOIN categories c ON bp.category_id = c.id
        LEFT JOIN users u ON bp.author_id = u.id
        $where
        ORDER BY bp.created_at DESC
        LIMIT {$pagination['per_page']} OFFSET {$pagination['offset']}";

if ($types) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $posts = mysqli_stmt_get_result($stmt);
} else {
    $posts = mysqli_query($conn, $sql);
}

// Sidebar data
$categories = mysqli_query($conn, "SELECT c.*, (SELECT COUNT(*) FROM blog_posts WHERE category_id = c.id AND status='published') as post_count FROM categories c ORDER BY name");
$tags = mysqli_query($conn, "SELECT * FROM tags ORDER BY name");

require_once __DIR__ . '/includes/header.php';
?>

<section class="post-hero">
    <div class="container">
        <h1>Blog</h1>
        <p style="color:var(--text-light);margin-top:8px">
            <?php if ($catSlug): ?>Posts in category: <strong><?= sanitize($catSlug) ?></strong>
            <?php elseif ($tagSlug): ?>Posts tagged: <strong><?= sanitize($tagSlug) ?></strong>
            <?php else: ?>Explore our latest articles and insights.<?php endif; ?>
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="blog-layout">

            <!-- Posts -->
            <div>
                <?php if (mysqli_num_rows($posts) === 0): ?>
                    <p style="text-align:center;padding:60px 0;color:var(--text-light)">No posts found.</p>
                <?php endif; ?>

                <div class="card-grid" style="grid-template-columns:1fr">
                <?php while ($post = mysqli_fetch_assoc($posts)): ?>
                    <article class="card" style="display:grid;grid-template-columns:280px 1fr">
                        <?php if ($post['featured_image']): ?>
                            <img src="<?= UPLOAD_URL . $post['featured_image'] ?>" alt="<?= sanitize($post['title']) ?>" style="width:100%;height:100%;object-fit:cover">
                        <?php else: ?>
                            <div style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,var(--lemon-light),var(--gray-100));font-size:3rem;min-height:200px">📝</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="card-meta">
                                <span class="badge badge-lemon"><?= sanitize($post['category_name'] ?? 'Uncategorized') ?></span>
                                <span><?= formatDate($post['created_at']) ?></span>
                                <span>by <?= sanitize($post['author_name']) ?></span>
                            </div>
                            <h3><a href="<?= SITE_URL ?>/post.php?slug=<?= urlencode($post['slug']) ?>"><?= sanitize($post['title']) ?></a></h3>
                            <p><?= sanitize($post['excerpt'] ?: truncateText($post['content'], 200)) ?></p>
                            <a href="<?= SITE_URL ?>/post.php?slug=<?= urlencode($post['slug']) ?>" class="btn btn-outline btn-sm">Read More →</a>
                        </div>
                    </article>
                <?php endwhile; ?>
                </div>

                <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php
                    $qs = '';
                    if ($catSlug) $qs .= "&category=$catSlug";
                    if ($tagSlug) $qs .= "&tag=$tagSlug";
                    for ($i = 1; $i <= $pagination['total_pages']; $i++):
                    ?>
                    <a href="<?= SITE_URL ?>/blog.php?page=<?= $i ?><?= $qs ?>" class="<?= $i === $pagination['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside>
                <div class="sidebar-widget">
                    <h4>Search</h4>
                    <form action="<?= SITE_URL ?>/search.php" method="GET" class="search-form">
                        <input type="text" name="q" placeholder="Search posts..." required>
                        <button type="submit" class="btn btn-primary btn-sm">Go</button>
                    </form>
                </div>

                <div class="sidebar-widget">
                    <h4>Categories</h4>
                    <ul>
                    <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                        <li><a href="<?= SITE_URL ?>/blog.php?category=<?= urlencode($cat['slug']) ?>"><?= sanitize($cat['name']) ?> (<?= $cat['post_count'] ?>)</a></li>
                    <?php endwhile; ?>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h4>Tags</h4>
                    <div class="tag-cloud">
                    <?php while ($tag = mysqli_fetch_assoc($tags)): ?>
                        <a href="<?= SITE_URL ?>/blog.php?tag=<?= urlencode($tag['slug']) ?>" class="badge badge-tech"><?= sanitize($tag['name']) ?></a>
                    <?php endwhile; ?>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
