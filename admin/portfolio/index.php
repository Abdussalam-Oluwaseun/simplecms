<?php
$pageTitle = 'Portfolio';
require_once __DIR__ . '/../includes/header.php';

$items = mysqli_query($conn, "SELECT * FROM portfolio_items ORDER BY created_at DESC");
?>

<div class="admin-topbar">
    <h1>Portfolio Items</h1>
    <a href="<?= ADMIN_URL ?>/portfolio/create.php" class="btn btn-primary">+ New Item</a>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Title</th><th>Category</th><th>Technologies</th><th>Featured</th><th>Date</th><th>Actions</th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($items) === 0): ?>
            <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--text-light)">No portfolio items yet.</td></tr>
        <?php endif; ?>
        <?php while ($item = mysqli_fetch_assoc($items)): ?>
        <tr>
            <td><strong><?= sanitize($item['title']) ?></strong></td>
            <td><span class="badge badge-lemon"><?= sanitize($item['category'] ?? '—') ?></span></td>
            <td style="max-width:200px"><?= sanitize(truncateText($item['technologies_used'] ?? '', 50)) ?></td>
            <td><?= $item['featured'] ? '⭐ Yes' : '—' ?></td>
            <td><?= formatDate($item['created_at']) ?></td>
            <td class="actions">
                <a href="<?= ADMIN_URL ?>/portfolio/edit.php?id=<?= $item['id'] ?>" class="edit-btn">Edit</a>
                <form method="POST" action="<?= ADMIN_URL ?>/portfolio/delete.php" class="confirm-delete" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                    <button type="submit" class="del-btn" style="border:none;cursor:pointer;font-family:var(--font)">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
