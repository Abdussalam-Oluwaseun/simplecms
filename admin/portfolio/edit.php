<?php
$pageTitle = 'Edit Portfolio Item';
require_once __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { setFlash('error', 'Invalid ID.'); redirect(ADMIN_URL . '/portfolio/'); }

$stmt = mysqli_prepare($conn, "SELECT * FROM portfolio_items WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$item) { setFlash('error', 'Item not found.'); redirect(ADMIN_URL . '/portfolio/'); }

$currentImages = json_decode($item['images'] ?? '[]', true) ?: [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid form submission.'; }

    $title = trim($_POST['title'] ?? '');
    $description = $_POST['description'] ?? '';
    $projectUrl = trim($_POST['project_url'] ?? '');
    $technologies = trim($_POST['technologies_used'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (empty($title)) $errors[] = 'Title is required.';

    $slug = trim($_POST['slug'] ?? '') ?: createSlug($title);
    $slug = uniqueSlug($conn, 'portfolio_items', $slug, $id);

    // Remove selected images
    $removeImages = $_POST['remove_images'] ?? [];
    $images = [];
    foreach ($currentImages as $img) {
        if (in_array($img, $removeImages)) { deleteImage($img); }
        else { $images[] = $img; }
    }

    // Add new uploads
    if (!empty($_FILES['images']['name'][0])) {
        foreach ($_FILES['images']['name'] as $key => $name) {
            if (empty($name)) continue;
            $file = [
                'name' => $_FILES['images']['name'][$key],
                'type' => $_FILES['images']['type'][$key],
                'tmp_name' => $_FILES['images']['tmp_name'][$key],
                'error' => $_FILES['images']['error'][$key],
                'size' => $_FILES['images']['size'][$key],
            ];
            $upload = uploadImage($file, 'portfolio');
            if ($upload['success']) { $images[] = $upload['filename']; }
            else { $errors[] = "Image '$name': " . $upload['error']; }
        }
    }

    if (empty($errors)) {
        $imagesJson = json_encode($images);
        $stmt = mysqli_prepare($conn, "UPDATE portfolio_items SET title=?, slug=?, description=?, images=?, project_url=?, technologies_used=?, category=?, featured=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssssii', $title, $slug, $description, $imagesJson, $projectUrl, $technologies, $category, $featured, $id);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Portfolio item updated!');
            redirect(ADMIN_URL . '/portfolio/');
        } else { $errors[] = 'Failed to update.'; }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="admin-topbar">
    <h1>Edit Portfolio Item</h1>
    <a href="<?= ADMIN_URL ?>/portfolio/" class="btn btn-outline btn-sm">← Back</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('sanitize', $errors)) ?></div>
<?php endif; ?>

<div class="admin-form-wrap">
<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>

    <div class="form-group">
        <label for="title">Project Title</label>
        <input type="text" id="title" name="title" value="<?= sanitize($item['title']) ?>" required>
    </div>

    <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="<?= sanitize($item['slug']) ?>">
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" class="rich-editor"><?= $item['description'] ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="project_url">Project URL</label>
            <input type="url" id="project_url" name="project_url" value="<?= sanitize($item['project_url'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?= sanitize($item['category'] ?? '') ?>">
        </div>
    </div>

    <div class="form-group">
        <label for="technologies_used">Technologies Used</label>
        <input type="text" id="technologies_used" name="technologies_used" value="<?= sanitize($item['technologies_used'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label class="form-check">
            <input type="checkbox" name="featured" value="1" <?= $item['featured'] ? 'checked' : '' ?>>
            Featured project
        </label>
    </div>

    <?php if ($currentImages): ?>
    <div class="form-group">
        <label>Current Images</label>
        <div class="img-thumbs">
        <?php foreach ($currentImages as $img): ?>
            <div class="thumb">
                <img src="<?= UPLOAD_URL . $img ?>" alt="Image">
                <label style="font-size:.75rem;margin-top:4px;display:flex;align-items:center;gap:4px">
                    <input type="checkbox" name="remove_images[]" value="<?= sanitize($img) ?>"> Remove
                </label>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="form-group">
        <label for="images">Add More Images</label>
        <input type="file" id="images" name="images[]" multiple accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary">Update Item</button>
</form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
