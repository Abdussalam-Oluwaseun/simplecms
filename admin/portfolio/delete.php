<?php
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect(ADMIN_URL . '/portfolio/'); }
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { setFlash('error', 'Invalid request.'); redirect(ADMIN_URL . '/portfolio/'); }

$id = intval($_POST['id'] ?? 0);
if (!$id) { setFlash('error', 'Invalid ID.'); redirect(ADMIN_URL . '/portfolio/'); }

$stmt = mysqli_prepare($conn, "SELECT images FROM portfolio_items WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$item = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($item) {
    $images = json_decode($item['images'] ?? '[]', true) ?: [];
    foreach ($images as $img) { deleteImage($img); }

    $stmt = mysqli_prepare($conn, "DELETE FROM portfolio_items WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    setFlash('success', 'Portfolio item deleted.');
} else {
    setFlash('error', 'Item not found.');
}

redirect(ADMIN_URL . '/portfolio/');
