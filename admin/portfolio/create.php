<?php
$pageTitle = 'New Portfolio Item';
require_once __DIR__ . '/../includes/header.php';
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
    if (empty($description)) $errors[] = 'Description is required.';

    $slug = createSlug($title);
    $slug = uniqueSlug($conn, 'portfolio_items', $slug);

    // Handle multiple image uploads
    $images = [];
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
        $stmt = mysqli_prepare($conn, "INSERT INTO portfolio_items (title, slug, description, images, project_url, technologies_used, category, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'sssssssi', $title, $slug, $description, $imagesJson, $projectUrl, $technologies, $category, $featured);
        if (mysqli_stmt_execute($stmt)) {
            setFlash('success', 'Portfolio item created!');
            redirect(ADMIN_URL . '/portfolio/');
        } else { $errors[] = 'Failed to create item.'; }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="admin-topbar">
    <h1>New Portfolio Item</h1>
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
        <input type="text" id="title" name="title" value="<?= sanitize($_POST['title'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" class="rich-editor"><?= $_POST['description'] ?? '' ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="project_url">Project URL</label>
            <input type="url" id="project_url" name="project_url" value="<?= sanitize($_POST['project_url'] ?? '') ?>" placeholder="https://...">
        </div>
        <div class="form-group">
            <label for="category">Category</label>
            <input type="text" id="category" name="category" value="<?= sanitize($_POST['category'] ?? '') ?>" placeholder="e.g. Web Development">
        </div>
    </div>

    <div class="form-group">
        <label for="technologies_used">Technologies Used</label>
        <input type="text" id="technologies_used" name="technologies_used" value="<?= sanitize($_POST['technologies_used'] ?? '') ?>" placeholder="PHP, JavaScript, MySQL, etc.">
        <p class="help-text">Comma-separated list of technologies.</p>
    </div>

    <div class="form-group">
        <label class="form-check">
            <input type="checkbox" name="featured" value="1" <?= !empty($_POST['featured']) ? 'checked' : '' ?>>
            Featured project (shown on homepage)
        </label>
    </div>

    <div class="form-group">
        <label for="images">Project Images</label>
        <input type="file" id="images" name="images[]" multiple accept="image/*">
        <p class="help-text">You can select multiple images. Max 5MB each.</p>
    </div>

    <button type="submit" class="btn btn-primary">Create Item</button>
</form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
