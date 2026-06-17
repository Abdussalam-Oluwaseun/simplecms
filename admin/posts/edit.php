<?php
$pageTitle = 'Edit Post';
require_once __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
if (!$id) { setFlash('error', 'Invalid post ID.'); redirect(ADMIN_URL . '/posts/'); }

// Fetch post
$stmt = mysqli_prepare($conn, "SELECT * FROM blog_posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$post) { setFlash('error', 'Post not found.'); redirect(ADMIN_URL . '/posts/'); }

// Fetch current tags for this post
$currentTags = [];
$tagStmt = mysqli_prepare($conn, "SELECT tag_id FROM post_tags WHERE post_id = ?");
mysqli_stmt_bind_param($tagStmt, 'i', $id);
mysqli_stmt_execute($tagStmt);
$tagResult = mysqli_stmt_get_result($tagStmt);
while ($r = mysqli_fetch_assoc($tagResult)) { $currentTags[] = $r['tag_id']; }
mysqli_stmt_close($tagStmt);

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

    $slug = trim($_POST['slug'] ?? '') ?: createSlug($title);
    $slug = uniqueSlug($conn, 'blog_posts', $slug, $id);

    // Featured image
    $featuredImage = $post['featured_image'];
    if (!empty($_FILES['featured_image']['name'])) {
        $upload = uploadImage($_FILES['featured_image'], 'posts');
        if ($upload['success']) {
            deleteImage($post['featured_image']);
            $featuredImage = $upload['filename'];
        } else { $errors[] = $upload['error']; }
    }
    if (!empty($_POST['remove_image'])) {
        deleteImage($post['featured_image']);
        $featuredImage = null;
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, "UPDATE blog_posts SET title=?, slug=?, content=?, excerpt=?, featured_image=?, category_id=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'sssssisi', $title, $slug, $content, $excerpt, $featuredImage, $catId, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
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
            
            // Sync tags
            syncPostTags($conn, $id, $tagIds);
            
            setFlash('success', 'Post updated successfully!');
            redirect(ADMIN_URL . '/posts/');
        } else {
            $errors[] = 'Failed to update post.';
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<div class="admin-topbar">
    <h1>Edit Post</h1>
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
        <input type="text" id="title" name="title" value="<?= sanitize($post['title']) ?>" required>
    </div>

    <div class="form-group">
        <label for="slug">Slug</label>
        <input type="text" id="slug" name="slug" value="<?= sanitize($post['slug']) ?>">
    </div>

    <div class="form-group">
        <label for="content">Content</label>
        <textarea id="content" name="content" class="rich-editor"><?= $post['content'] ?></textarea>
    </div>

    <div class="form-group">
        <label for="excerpt">Excerpt</label>
        <textarea id="excerpt" name="excerpt" rows="3"><?= sanitize($post['excerpt'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <option value="">— Select —</option>
                <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= $cat['id'] ?>" <?= $post['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= sanitize($cat['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" name="status">
                <option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="tags">Tags</label>
        <p class="help-text" style="margin-bottom:12px">Select existing tags or enter new ones below.</p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;margin-bottom:16px">
        <?php mysqli_data_seek($allTags, 0); while ($tag = mysqli_fetch_assoc($allTags)): ?>
            <label class="form-check">
                <input type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $currentTags) ? 'checked' : '' ?>>
                <?= sanitize($tag['name']) ?>
            </label>
        <?php endwhile; ?>
        </div>
        <label for="custom_tags">Add New Tags</label>
        <input type="text" id="custom_tags" name="custom_tags" placeholder="Enter comma-separated tags (e.g., PHP, MySQL, Web Design)">
        <p class="help-text">New tags will be created automatically if they don't exist.</p>
    </div>

    <div class="form-group">
        <label for="featured_image">Featured Image</label>
        <?php if ($post['featured_image']): ?>
            <div style="margin-bottom:10px">
                <img src="<?= UPLOAD_URL . $post['featured_image'] ?>" class="img-preview" alt="Current">
                <label class="form-check" style="margin-top:8px">
                    <input type="checkbox" name="remove_image" value="1"> Remove current image
                </label>
            </div>
        <?php endif; ?>
        <input type="file" id="featured_image" name="featured_image" class="img-input" data-preview="img-preview-new" accept="image/*">
        <img id="img-preview-new" class="img-preview" style="display:none" alt="Preview">
    </div>

    <button type="submit" class="btn btn-primary">Update Post</button>
</form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
