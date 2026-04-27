<?php
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(ADMIN_URL . '/posts/'); }
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { setFlash('error', 'Invalid request.'); redirect(ADMIN_URL . '/posts/'); }

$id = intval($_POST['id'] ?? 0);
if (!$id) { setFlash('error', 'Invalid post ID.'); redirect(ADMIN_URL . '/posts/'); }

// Get post to delete its image
$stmt = mysqli_prepare($conn, "SELECT featured_image FROM blog_posts WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$post = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($post) {
    deleteImage($post['featured_image']);
    // Delete tag associations (handled by CASCADE, but explicit for safety)
    mysqli_query($conn, "DELETE FROM post_tags WHERE post_id = $id");
    $stmt = mysqli_prepare($conn, "DELETE FROM blog_posts WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    setFlash('success', 'Post deleted successfully.');
} else {
    setFlash('error', 'Post not found.');
}

redirect(ADMIN_URL . '/posts/');
