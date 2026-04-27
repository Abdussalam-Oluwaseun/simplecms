<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
$stats = getStats($conn);

// Recent posts
$recentPosts = mysqli_query($conn, "SELECT bp.*, c.name as category_name FROM blog_posts bp LEFT JOIN categories c ON bp.category_id = c.id ORDER BY bp.created_at DESC LIMIT 5");

// Recent portfolio
$recentPortfolio = mysqli_query($conn, "SELECT * FROM portfolio_items ORDER BY created_at DESC LIMIT 5");
?>

<div class="admin-topbar">
    <h1>Dashboard</h1>
    <div class="user-info">Welcome, <?= sanitize($currentUser['username']) ?></div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon lemon">📝</div>
        <div class="stat-number"><?= $stats['total_posts'] ?></div>
        <div class="stat-label">Total Posts</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div class="stat-number"><?= $stats['published_posts'] ?></div>
        <div class="stat-label">Published</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">💼</div>
        <div class="stat-number"><?= $stats['total_portfolio'] ?></div>
        <div class="stat-label">Portfolio Items</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange">📁</div>
        <div class="stat-number"><?= $stats['total_categories'] ?></div>
        <div class="stat-label">Categories</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

<div class="admin-table-wrap">
    <div class="admin-table-header">
        <h3>Recent Posts</h3>
        <a href="<?= ADMIN_URL ?>/posts/create.php" class="btn btn-primary btn-sm">+ New Post</a>
    </div>
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php while ($post = mysqli_fetch_assoc($recentPosts)): ?>
        <tr>
            <td><a href="<?= ADMIN_URL ?>/posts/edit.php?id=<?= $post['id'] ?>"><?= sanitize($post['title']) ?></a></td>
            <td><span class="status-<?= $post['status'] ?>"><?= ucfirst($post['status']) ?></span></td>
            <td><?= formatDate($post['created_at']) ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<div class="admin-table-wrap">
    <div class="admin-table-header">
        <h3>Recent Portfolio</h3>
        <a href="<?= ADMIN_URL ?>/portfolio/create.php" class="btn btn-primary btn-sm">+ New Item</a>
    </div>
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Category</th><th>Featured</th></tr></thead>
        <tbody>
        <?php while ($item = mysqli_fetch_assoc($recentPortfolio)): ?>
        <tr>
            <td><a href="<?= ADMIN_URL ?>/portfolio/edit.php?id=<?= $item['id'] ?>"><?= sanitize($item['title']) ?></a></td>
            <td><?= sanitize($item['category'] ?? '—') ?></td>
            <td><?= $item['featured'] ? '⭐' : '—' ?></td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
