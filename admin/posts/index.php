<?php
$pageTitle = 'Blog Posts';
require_once __DIR__ . '/../includes/header.php';

$statusFilter = $_GET['status'] ?? '';
$where = '';
$params = [];
$types = '';

if ($statusFilter === 'published' || $statusFilter === 'draft') {
    $where = 'WHERE bp.status = ?';
    $params[] = $statusFilter;
    $types = 's';
}

$countSql = "SELECT COUNT(*) as c FROM blog_posts bp $where";
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
$pagination = getPagination($total, 15, $page);

$sql = "SELECT bp.*, c.name as category_name, u.username as author_name
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
?>

<div class="admin-topbar">
    <h1>Blog Posts</h1>
    <a href="<?= ADMIN_URL ?>/posts/create.php" class="btn btn-primary">+ New Post</a>
</div>

<div style="margin-bottom:20px;display:flex;gap:10px">
    <a href="<?= ADMIN_URL ?>/posts/" class="btn btn-sm <?= !$statusFilter ? 'btn-primary' : 'btn-outline' ?>">All (<?= $total ?>)</a>
    <a href="<?= ADMIN_URL ?>/posts/?status=published" class="btn btn-sm <?= $statusFilter === 'published' ? 'btn-primary' : 'btn-outline' ?>">Published</a>
    <a href="<?= ADMIN_URL ?>/posts/?status=draft" class="btn btn-sm <?= $statusFilter === 'draft' ? 'btn-primary' : 'btn-outline' ?>">Drafts</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead>
            <tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($posts) === 0): ?>
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-light)">No posts found.</td></tr>
        <?php endif; ?>
        <?php while ($post = mysqli_fetch_assoc($posts)): ?>
        <tr>
            <td><strong><?= sanitize($post['title']) ?></strong></td>
            <td><span class="badge badge-lemon"><?= sanitize($post['category_name'] ?? 'None') ?></span></td>
            <td><?= sanitize($post['author_name']) ?></td>
            <td><span class="status-<?= $post['status'] ?>"><?= ucfirst($post['status']) ?></span></td>
            <td><?= formatDate($post['created_at']) ?></td>
            <td class="actions">
                <a href="<?= ADMIN_URL ?>/posts/edit.php?id=<?= $post['id'] ?>" class="edit-btn">Edit</a>
                <form method="POST" action="<?= ADMIN_URL ?>/posts/delete.php" class="confirm-delete" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                    <button type="submit" class="del-btn" style="border:none;cursor:pointer;font-family:var(--font)">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php if ($pagination['total_pages'] > 1): ?>
<div class="pagination" style="margin-top:24px">
    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
        <?php $qs = $statusFilter ? "&status=$statusFilter" : ''; ?>
        <a href="<?= ADMIN_URL ?>/posts/?page=<?= $i ?><?= $qs ?>" class="<?= $i === $pagination['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
