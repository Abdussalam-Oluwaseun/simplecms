<?php
$pageTitle = 'Tags';
require_once __DIR__ . '/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($action === 'create' && $name) {
        $slug = uniqueSlug($conn, 'tags', createSlug($name));
        $stmt = mysqli_prepare($conn, "INSERT INTO tags (name, slug) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
        mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        setFlash('success', 'Tag created.');
        redirect(ADMIN_URL . '/tags.php');
    }
    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if ($id && $name) {
            $slug = uniqueSlug($conn, 'tags', createSlug($name), $id);
            $stmt = mysqli_prepare($conn, "UPDATE tags SET name=?, slug=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $id);
            mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
            setFlash('success', 'Tag updated.');
        }
        redirect(ADMIN_URL . '/tags.php');
    }
    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $stmt = mysqli_prepare($conn, "DELETE FROM tags WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
            setFlash('success', 'Tag deleted.');
        }
        redirect(ADMIN_URL . '/tags.php');
    }
}

$tags = mysqli_query($conn, "SELECT t.*, (SELECT COUNT(*) FROM post_tags WHERE tag_id = t.id) as usage_count FROM tags t ORDER BY name");
$editId = intval($_GET['edit'] ?? 0);
$editTag = null;
if ($editId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM tags WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $editTag = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}
?>

<div class="admin-topbar"><h1>Tags</h1></div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:32px">

<div class="admin-form-wrap">
    <h3 style="margin-bottom:20px"><?= $editTag ? 'Edit Tag' : 'Add Tag' ?></h3>
    <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $editTag ? 'update' : 'create' ?>">
        <?php if ($editTag): ?><input type="hidden" name="id" value="<?= $editTag['id'] ?>"><?php endif; ?>
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= sanitize($editTag['name'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><?= $editTag ? 'Update' : 'Add Tag' ?></button>
        <?php if ($editTag): ?> <a href="<?= ADMIN_URL ?>/tags.php" class="btn btn-outline btn-sm">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Slug</th><th>Used In</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($tag = mysqli_fetch_assoc($tags)): ?>
        <tr>
            <td><strong><?= sanitize($tag['name']) ?></strong></td>
            <td><code><?= sanitize($tag['slug']) ?></code></td>
            <td><?= $tag['usage_count'] ?> posts</td>
            <td class="actions">
                <a href="<?= ADMIN_URL ?>/tags.php?edit=<?= $tag['id'] ?>" class="edit-btn">Edit</a>
                <form method="POST" class="confirm-delete" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $tag['id'] ?>">
                    <button type="submit" class="del-btn" style="border:none;cursor:pointer;font-family:var(--font)">Delete</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
