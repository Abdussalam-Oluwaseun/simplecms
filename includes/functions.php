<?php
/**
 * SimpleCMS — Shared Utility Functions
 */

// ── Output Sanitization ────────────────────────────────────
function sanitize($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ── Slug Generation ─────────────────────────────────────────
function createSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

/**
 * Ensure slug is unique in a given table
 */
function uniqueSlug($conn, $table, $slug, $excludeId = null) {
    $original = $slug;
    $counter = 1;
    while (true) {
        $sql = "SELECT id FROM `$table` WHERE slug = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'si', $slug, $excludeId);
        } else {
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 's', $slug);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) === 0) {
            mysqli_stmt_close($stmt);
            return $slug;
        }
        mysqli_stmt_close($stmt);
        $slug = $original . '-' . $counter;
        $counter++;
    }
}

// ── Redirect Helper ─────────────────────────────────────────
function redirect($url) {
    header("Location: $url");
    exit;
}

// ── Authentication ──────────────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect(ADMIN_URL . '/login.php');
    }
}

function getCurrentUser($conn) {
    if (!isLoggedIn()) return null;
    $stmt = mysqli_prepare($conn, "SELECT id, username, email FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $user;
}

// ── CSRF Protection ─────────────────────────────────────────
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Image Upload ────────────────────────────────────────────
function uploadImage($file, $subdir = 'posts') {
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5 MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error code: ' . $file['error']];
    }

    // Use finfo to verify the actual file MIME type
    if (class_exists('finfo')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
    } else {
        $mimeType = $file['type'];
    }

    if (!in_array($mimeType, $allowed)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP.'];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File too large. Maximum 5 MB.'];
    }

    // Verify actual image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'error' => 'File is not a valid image.'];
    }

    $dir = UPLOAD_PATH . $subdir . '/';
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
            return ['success' => false, 'error' => 'Failed to create upload directory: ' . $dir];
        }
    }

    if (!is_writable($dir)) {
        return ['success' => false, 'error' => 'Upload directory is not writable: ' . $dir];
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file['name']);
    $filepath = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'error' => 'Failed to move uploaded file.'];
    }

    return [
        'success' => true,
        'filename' => $subdir . '/' . $filename,
        'path' => $filepath
    ];
}

function deleteImage($relativePath) {
    if (empty($relativePath)) return;
    $fullPath = UPLOAD_PATH . $relativePath;
    if (file_exists($fullPath)) {
        unlink($fullPath);
    }
}

// ── Pagination ──────────────────────────────────────────────
function getPagination($total, $perPage, $currentPage) {
    $totalPages = max(1, ceil($total / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset
    ];
}

// ── Text Helpers ────────────────────────────────────────────
function truncateText($text, $length = 150) {
    $text = strip_tags($text);
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

function getTimeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

// ── Flash Messages ──────────────────────────────────────────
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// ── Statistics (for admin dashboard) ────────────────────────
function getStats($conn) {
    $stats = [];

    $result = mysqli_query($conn, "SELECT COUNT(*) as c FROM blog_posts");
    $stats['total_posts'] = mysqli_fetch_assoc($result)['c'];

    $result = mysqli_query($conn, "SELECT COUNT(*) as c FROM blog_posts WHERE status='published'");
    $stats['published_posts'] = mysqli_fetch_assoc($result)['c'];

    $result = mysqli_query($conn, "SELECT COUNT(*) as c FROM blog_posts WHERE status='draft'");
    $stats['draft_posts'] = mysqli_fetch_assoc($result)['c'];

    $result = mysqli_query($conn, "SELECT COUNT(*) as c FROM portfolio_items");
    $stats['total_portfolio'] = mysqli_fetch_assoc($result)['c'];

    $result = mysqli_query($conn, "SELECT COUNT(*) as c FROM categories");
    $stats['total_categories'] = mysqli_fetch_assoc($result)['c'];

    $result = mysqli_query($conn, "SELECT COUNT(*) as c FROM tags");
    $stats['total_tags'] = mysqli_fetch_assoc($result)['c'];

    $result = mysqli_query($conn, "SELECT COUNT(*) as c FROM users");
    $stats['total_users'] = mysqli_fetch_assoc($result)['c'];

    return $stats;
}

// ── Tag Helper ─────────────────────────────────────────────
/**
 * Create or retrieve a tag by name. Returns tag ID.
 */
function getOrCreateTag($conn, $name) {
    $name = trim($name);
    if (empty($name)) return null;

    $slug = createSlug($name);
    
    // Check if tag exists by slug
    $stmt = mysqli_prepare($conn, "SELECT id FROM tags WHERE slug = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tag = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($tag) {
        return $tag['id'];
    }

    // Create new tag
    $stmt = mysqli_prepare($conn, "INSERT INTO tags (name, slug) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $name, $slug);
    
    if (mysqli_stmt_execute($stmt)) {
        $tagId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);
        return $tagId;
    }
    
    mysqli_stmt_close($stmt);
    return null;
}

// ── Additional Security & Helper Functions ──────────────────
function regenerateCSRFToken() {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function syncPostTags($conn, $postId, $tagIds) {
    // Delete existing associations using a prepared statement to prevent injection
    $stmt = mysqli_prepare($conn, "DELETE FROM post_tags WHERE post_id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $postId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Insert new associations
    if (!empty($tagIds)) {
        foreach ($tagIds as $tagId) {
            $tagId = intval($tagId);
            if ($tagId > 0) {
                $ts = mysqli_prepare($conn, "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
                mysqli_stmt_bind_param($ts, 'ii', $postId, $tagId);
                mysqli_stmt_execute($ts);
                mysqli_stmt_close($ts);
            }
        }
    }
}

function checkLoginLockout($conn, $username, $ip) {
    // Count failed attempts in the last 15 minutes
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) FROM login_attempts WHERE (ip_address = ? OR username = ?) AND attempted_at > NOW() - INTERVAL 15 MINUTE");
    mysqli_stmt_bind_param($stmt, 'ss', $ip, $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $count >= 5;
}

function logFailedLogin($conn, $username, $ip) {
    $stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, 'ss', $ip, $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

