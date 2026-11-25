<?php
require_once 'db_connect.php';
require_login();

$post_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($post_id <= 0) {
    $_SESSION['flash_message'] = "Invalid blog post ID.";
    $_SESSION['flash_type']    = "danger";
    header("Location: index.php");
    exit;
}

// Increment view counter
$inc_stmt = $conn->prepare("UPDATE posts SET views = views + 1 WHERE id = ?");
$inc_stmt->bind_param("i", $post_id);
$inc_stmt->execute();
$inc_stmt->close();

// Fetch post details (SELECT operation)
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post   = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    $_SESSION['flash_message'] = "Blog post #{$post_id} does not exist.";
    $_SESSION['flash_type']    = "danger";
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> &mdash; SimpleBlog</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/style.css?v=blogify1" rel="stylesheet">
    <?php render_apple_theme_css(); ?>
</head>
<body>

    <div class="magazine-shell">

        <!-- Top Navigation Bar -->
        <nav class="navbar navbar-expand-lg blogify-navbar sticky-top px-3 px-md-4">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="index.php">
                    <span class="brand-logo-mark">B</span>
                    <div>
                        <span class="brand-title">SimpleBlog</span>
                        <span class="brand-subtitle">Share Ideas, Inspire Minds</span>
                    </div>
                </a>
                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php#latest-posts">Blog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#manage-posts">Manage Posts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="create.php">Write Post</a>
                        </li>
                    </ul>
                    <div class="d-flex gap-2">
                        <a href="edit.php?id=<?= (int) $post['id'] ?>" class="btn btn-teal btn-sm px-3">
                            <i class="bi bi-pencil-square me-1"></i>Edit Post
                        </a>
                        <a href="index.php" class="btn btn-soft btn-sm px-3">
                            <i class="bi bi-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <article class="section-box bg-white p-0 overflow-hidden shadow-sm">
                        <div class="p-4 p-md-5">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <span class="category-badge-teal">
                                    <?= htmlspecialchars($post['category']) ?>
                                </span>
                                <span class="badge <?= get_status_badge_class($post['status']) ?> rounded-pill px-3 py-1">
                                    <?= htmlspecialchars($post['status']) ?>
                                </span>
                                <span class="small text-secondary ms-auto">
                                    <i class="bi bi-clock me-1"></i><?= (int) $post['reading_time'] ?> min read
                                    &bull;
                                    <i class="bi bi-eye ms-1 me-1"></i><?= number_format((int) $post['views']) ?> views
                                </span>
                            </div>

                            <h1 class="hero-headline mb-3" style="font-size: clamp(1.65rem, 3vw, 2.35rem);">
                                <?= htmlspecialchars($post['title']) ?>
                            </h1>

                            <p class="p-3 rounded-3 mb-4" style="background: var(--teal-light); color: var(--teal-dark); border-left: 4px solid var(--teal-primary); font-size: 1.02rem;">
                                <?= htmlspecialchars($post['summary']) ?>
                            </p>

                            <div class="d-flex flex-wrap justify-content-between align-items-center py-3 border-top border-bottom mb-4 small text-secondary">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="brand-logo-mark rounded-circle" style="width: 36px; height: 36px; font-size: 1rem;">
                                        <?= strtoupper(substr($post['author'], 0, 1)) ?>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($post['author']) ?></div>
                                        <div>Published <?= date('F j, Y', strtotime($post['created_at'])) ?></div>
                                    </div>
                                </div>
                                <div>Record #<?= (int) $post['id'] ?></div>
                            </div>

                            <!-- Full Post Content -->
                            <div class="mb-4" style="font-size: 1.05rem; line-height: 1.8; color: var(--text-dark); white-space: pre-line;"><?= htmlspecialchars($post['content']) ?></div>

                            <!-- Post Footer Actions -->
                            <div class="pt-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <a href="index.php" class="btn btn-soft px-3">
                                    <i class="bi bi-arrow-left me-1"></i>Back to Home
                                </a>
                                <div class="d-flex gap-2">
                                    <a href="edit.php?id=<?= (int) $post['id'] ?>" class="btn btn-teal px-3">
                                        <i class="bi bi-pencil-square me-1"></i>Edit Post
                                    </a>
                                    <form action="delete.php" method="POST" onsubmit="return confirm('Delete this blog post permanently?');">
                                        <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                                        <button type="submit" class="btn btn-soft text-danger px-3">
                                            <i class="bi bi-trash3 me-1"></i>Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>

                </div>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
