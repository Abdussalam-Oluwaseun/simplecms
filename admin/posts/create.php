<?php
$pageTitle = 'New Post';
require_once __DIR__ . '/../includes/header.php';

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$allTags = mysqli_query($conn, "SELECT * FROM tags ORDER BY name");
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $errors[] = 'Invalid form submission.'; }

    $title   = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';
    $excerpt = trim($_POST['excerpt'] ?? '');
    $catId   = intval($_POST['category_id'] ?? 0) ?: null;
    $status  = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $tagIds  = $_POST['tags'] ?? [];
    $customTags = trim($_POST['custom_tags'] ?? '');

    if (empty($title)) $errors[] = 'Title is required.';
    if (empty($content)) $errors[] = 'Content is required.';

    $slug = createSlug($title);
    $slug = uniqueSlug($conn, 'blog_posts', $slug);

    // Featured image
    $featuredImage = null;
    if (!empty($_FILES['featured_image']['name'])) {
        $upload = uploadImage($_FILES['featured_image'], 'posts');
        if ($upload['success']) { $featuredImage = $upload['filename']; }
        else { $errors[] = $upload['error']; }
    }

    if (empty($errors)) {
        $authorId = $_SESSION['user_id'];
        $stmt = mysqli_prepare($conn, "INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, category_id, author_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssssssis', $title, $slug, $content, $excerpt, $featuredImage, $catId, $authorId, $status);

        if (mysqli_stmt_execute($stmt)) {
            $postId = mysqli_insert_id($conn);
            
            // Process custom tags (comma-separated)
            if (!empty($customTags)) {
                $customTagArray = array_map('trim', explode(',', $customTags));
                foreach ($customTagArray as $tagName) {
                    if (!empty($tagName)) {
                        $newTagId = getOrCreateTag($conn, $tagName);
                        if ($newTagId) {
                            $tagIds[] = $newTagId;
                        }
                    }
                }
            }
            
            // Insert all tags
            foreach ($tagIds as $tagId) {
                $tagId = intval($tagId);
                $ts = mysqli_prepare($conn, "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                mysqli_stmt_bind_param($ts, 'ii', $postId, $tagId);
                mysqli_stmt_execute($ts);
                mysqli_stmt_close($ts);
            }
            setFlash('success', 'Post created successfully!');
            redirect(ADMIN_URL . '/posts/');
        } else {
            $errors[] = 'Failed to create post: ' . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="admin-topbar">
    <h1>New Blog Post</h1>
    <a href="<?= ADMIN_URL ?>/posts/" class="btn btn-outline btn-sm">← Back to Posts</a>
</div>

<?php if ($errors): ?>
    <div class="alert alert-error"><?= implode('<br>', array_map('sanitize', $errors)) ?></div>
<?php endif; ?>

<div class="admin-form-wrap">
<form method="POST" enctype="multipart/form-data">
    <?= csrfField() ?>

    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= sanitize($_POST['title'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="<?= sanitize($_POST['slug'] ?? '') ?>" placeholder="Auto-generated from title">
        <p class="help-text">Leave blank to auto-generate from title.</p>
    </div>

    <div class="form-group">
        <label for="content">Content</label>
        <textarea id="content" name="content" class="rich-editor"><?= $_POST['content'] ?? '' ?></textarea>
    </div>

    <div class="form-group">
        <label for="excerpt">Excerpt</label>
        <textarea id="excerpt" name="excerpt" rows="3" placeholder="Brief summary for listings..."><?= sanitize($_POST['excerpt'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="">— Select Category —</option>
                <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft" <?= (($_POST['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= (($_POST['status'] ?? '') === 'published') ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="tags">Tags</label>
        <p class="help-text" style="margin-bottom:12px">Select existing tags or enter new ones below.</p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:16px">
        <?php mysqli_data_seek($allTags, 0); while ($tag = mysqli_fetch_assoc($allTags)): ?>
            <label class="form-check">
                <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $_POST['tags'] ?? []) ? 'checked' : '' ?>>
                <?= sanitize($tag['name']) ?>
            </label>
        <?php endwhile; ?>
        </div>
        <label for="custom_tags">Add New Tags</label>
        <input type="text" id="custom_tags" name="custom_tags" value="<?= sanitize($_POST['custom_tags'] ?? '') ?>" placeholder="Enter comma-separated tags (e.g., PHP, MySQL, Web Design)">
        <p class="help-text">New tags will be created automatically if they don't exist.</p>
    </div>

    <div class="form-group">
        <label for="featured_image">Featured Image</label>
        <input type="file" id="featured_image" name="featured_image" class="img-input" data-preview="img-preview" accept="image/*">
        <img id="img-preview" class="img-preview" style="display:none" alt="Preview">
    </div>

    <button type="submit" class="btn btn-primary">Create Post</button>
</form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
