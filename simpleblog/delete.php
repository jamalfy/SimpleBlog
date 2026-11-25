<?php
require_once 'db_connect.php';
require_login();

// Support both POST (from confirmation modal) and GET (direct link fallback)
$post_id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = (int) ($_POST['id'] ?? 0);
} elseif (isset($_GET['id'])) {
    $post_id = (int) $_GET['id'];
}

if ($post_id <= 0) {
    $_SESSION['flash_message'] = "Invalid blog post ID specified for deletion.";
    $_SESSION['flash_type']    = "danger";
    header("Location: index.php");
    exit;
}

// ----------------------------------------------------------
// EXECUTE MYSQL DELETE OPERATION
// ----------------------------------------------------------
$stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $_SESSION['flash_message'] = "Blog post #{$post_id} has been permanently deleted from the database.";
    $_SESSION['flash_type']    = "success";
} else {
    $_SESSION['flash_message'] = "Could not delete post #{$post_id}. It may have already been removed.";
    $_SESSION['flash_type']    = "danger";
}

$stmt->close();
header("Location: index.php");
exit;
?>
