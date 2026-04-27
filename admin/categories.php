<?php
$pageTitle = 'Categories';
require_once __DIR__ . '/includes/header.php';
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($action === 'create' && $name) {
        $slug = uniqueSlug($conn, 'categories', createSlug($name));
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, slug) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
        if (mysqli_stmt_execute($stmt)) { setFlash('success', 'Category created.'); }
        else { setFlash('error', 'Failed to create category.'); }
        mysqli_stmt_close($stmt);
        redirect(ADMIN_URL . '/categories.php');
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        if ($id && $name) {
            $slug = uniqueSlug($conn, 'categories', createSlug($name), $id);
            $stmt = mysqli_prepare($conn, "UPDATE categories SET name=?, slug=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $slug, $id);
            mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
            setFlash('success', 'Category updated.');
        }
        redirect(ADMIN_URL . '/categories.php');
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
            setFlash('success', 'Category deleted.');
        }
        redirect(ADMIN_URL . '/categories.php');
    }
}

$categories = mysqli_query($conn, "SELECT c.*, (SELECT COUNT(*) FROM blog_posts WHERE category_id = c.id) as post_count FROM categories c ORDER BY name");
$editId = intval($_GET['edit'] ?? 0);
$editCat = null;
if ($editId) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM categories WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $editId);
    mysqli_stmt_execute($stmt);
    $editCat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
}
?>

<div class="admin-topbar"><h1>Categories</h1></div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:32px">

<div class="admin-form-wrap">
    <h3 style="margin-bottom:20px"><?= $editCat ? 'Edit Category' : 'Add Category' ?></h3>
    <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="<?= $editCat ? 'update' : 'create' ?>">
        <?php if ($editCat): ?><input type="hidden" name="id" value="<?= $editCat['id'] ?>"><?php endif; ?>
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?= sanitize($editCat['name'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><?= $editCat ? 'Update' : 'Add Category' ?></button>
        <?php if ($editCat): ?> <a href="<?= ADMIN_URL ?>/categories.php" class="btn btn-outline btn-sm">Cancel</a><?php endif; ?>
    </form>
</div>

<div class="admin-table-wrap">
    <table class="admin-table">
        <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
        <tr>
            <td><strong><?= sanitize($cat['name']) ?></strong></td>
            <td><code><?= sanitize($cat['slug']) ?></code></td>
            <td><?= $cat['post_count'] ?></td>
            <td class="actions">
                <a href="<?= ADMIN_URL ?>/categories.php?edit=<?= $cat['id'] ?>" class="edit-btn">Edit</a>
                <form method="POST" class="confirm-delete" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $cat['id'] ?>">
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
