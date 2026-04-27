<?php
/**
 * SimpleCMS — Search
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Search';
$query = trim($_GET['q'] ?? '');
$results = [];
$totalResults = 0;

if ($query) {
    $pageTitle = "Search: $query";
    $searchTerm = "%$query%";

    // Search blog posts
    $stmt = mysqli_prepare($conn, "SELECT 'post' as type, id, title, slug, excerpt, content, created_at FROM blog_posts WHERE status='published' AND (title LIKE ? OR content LIKE ? OR excerpt LIKE ?) ORDER BY created_at DESC LIMIT 20");
    mysqli_stmt_bind_param($stmt, 'sss', $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $postResults = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($postResults)) { $results[] = $r; }
    mysqli_stmt_close($stmt);

    // Search portfolio items
    $stmt = mysqli_prepare($conn, "SELECT 'portfolio' as type, id, title, slug, description as excerpt, description as content, created_at FROM portfolio_items WHERE title LIKE ? OR description LIKE ? OR technologies_used LIKE ? ORDER BY created_at DESC LIMIT 20");
    mysqli_stmt_bind_param($stmt, 'sss', $searchTerm, $searchTerm, $searchTerm);
    mysqli_stmt_execute($stmt);
    $portResults = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($portResults)) { $results[] = $r; }
    mysqli_stmt_close($stmt);

    $totalResults = count($results);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="post-hero">
    <div class="container">
        <h1>Search</h1>
        <form action="<?= SITE_URL ?>/search.php" method="GET" class="search-form" style="max-width:600px;margin:24px auto 0">
            <input type="text" name="q" value="<?= sanitize($query) ?>" placeholder="Search posts and projects..." required>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</section>

<section class="section">
    <div class="container" style="max-width:800px">

    <?php if ($query): ?>
        <p style="margin-bottom:32px;color:var(--text-light)">Found <strong><?= $totalResults ?></strong> result<?= $totalResults !== 1 ? 's' : '' ?> for "<strong><?= sanitize($query) ?></strong>"</p>

        <?php if ($totalResults === 0): ?>
            <div style="text-align:center;padding:60px 0">
                <p style="font-size:3rem;margin-bottom:16px">🔍</p>
                <p style="color:var(--text-light)">No results found. Try different keywords.</p>
            </div>
        <?php endif; ?>

        <?php foreach ($results as $result): ?>
        <div class="card" style="margin-bottom:20px">
            <div class="card-body">
                <div class="card-meta">
                    <span class="badge <?= $result['type'] === 'post' ? 'badge-lemon' : 'badge-success' ?>">
                        <?= $result['type'] === 'post' ? '📝 Blog Post' : '💼 Portfolio' ?>
                    </span>
                    <span><?= formatDate($result['created_at']) ?></span>
                </div>
                <h3>
                    <a href="<?= SITE_URL ?>/<?= $result['type'] === 'post' ? 'post' : 'project' ?>.php?slug=<?= urlencode($result['slug']) ?>">
                        <?= sanitize($result['title']) ?>
                    </a>
                </h3>
                <p><?= sanitize(truncateText($result['excerpt'] ?: $result['content'], 200)) ?></p>
            </div>
        </div>
        <?php endforeach; ?>

    <?php else: ?>
        <div style="text-align:center;padding:60px 0">
            <p style="font-size:3rem;margin-bottom:16px">🔍</p>
            <p style="color:var(--text-light)">Enter a search term to find blog posts and portfolio projects.</p>
        </div>
    <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
