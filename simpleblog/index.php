<?php
require_once 'db_connect.php';
require_login();

// ----------------------------------------------------------
// 1. FETCH ALL POSTS & FILTERED POSTS (MySQL SELECT)
// ----------------------------------------------------------
$search   = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$status   = trim($_GET['status'] ?? '');

$where_clauses = [];
$params        = [];
$types         = "";

if ($search !== '') {
    $where_clauses[] = "(title LIKE ? OR author LIKE ? OR summary LIKE ? OR content LIKE ?)";
    $like_term = "%" . $search . "%";
    $params[] = $like_term;
    $params[] = $like_term;
    $params[] = $like_term;
    $params[] = $like_term;
    $types   .= "ssss";
}

if ($category !== '') {
    $where_clauses[] = "category = ?";
    $params[] = $category;
    $types   .= "s";
}

if ($status !== '') {
    $where_clauses[] = "status = ?";
    $params[] = $status;
    $types   .= "s";
}

$sql = "SELECT id, title, author, category, summary, status, reading_time, views, created_at, updated_at FROM posts";
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}
$sql .= " ORDER BY created_at DESC, id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$posts  = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ----------------------------------------------------------
// 2. FETCH POPULAR POSTS & CATEGORY COUNTS (Sidebar & Discovery)
// ----------------------------------------------------------
$popular_posts = [];
$pop_res = $conn->query("SELECT id, title, category, views, created_at FROM posts ORDER BY views DESC, id DESC LIMIT 4");
if ($pop_res) {
    $popular_posts = $pop_res->fetch_all(MYSQLI_ASSOC);
}

$cat_counts = [];
$cat_res = $conn->query("SELECT category, COUNT(*) AS total FROM posts GROUP BY category");
if ($cat_res) {
    while ($row = $cat_res->fetch_assoc()) {
        $cat_counts[$row['category']] = (int) $row['total'];
    }
}

$featured_post = !empty($posts) ? $posts[0] : (!empty($popular_posts) ? $popular_posts[0] : null);
$categories    = get_blog_categories();
$featured_cats = array_slice($categories, 0, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SimpleBlog &mdash; Share Ideas, Inspire Minds</title>
    <!-- Google Fonts: Playfair Display (Editorial Serif) & Inter (Clean Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,800;1,600&display=swap" rel="stylesheet">
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Blogify Editorial Stylesheet -->
    <link href="assets/style.css?v=blogify1" rel="stylesheet">
    <?php render_apple_theme_css(); ?>
</head>
<body>

    <div class="magazine-shell">

        <!-- ==========================================================
             TOP NAVIGATION BAR (Blogify Layout)
             ========================================================== -->
        <nav class="navbar navbar-expand-lg blogify-navbar sticky-top px-3 px-md-4">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center gap-2 text-decoration-none" href="index.php">
                    <span class="brand-logo-mark">B</span>
                    <div>
                        <span class="brand-title">SimpleBlog</span>
                        <span class="brand-subtitle">Share Ideas, Inspire Minds</span>
                    </div>
                </a>

                <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                        <li class="nav-item">
                            <a class="nav-link <?= ($category === '' && $search === '') ? 'active' : '' ?>" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#latest-posts">Blog</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle <?= $category !== '' ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Categories
                            </a>
                            <ul class="dropdown-menu border-0 shadow-sm rounded-3">
                                <li><a class="dropdown-item small fw-medium" href="index.php">All Categories</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <?php foreach ($categories as $cat): ?>
                                    <li>
                                        <a class="dropdown-item small d-flex justify-content-between align-items-center <?= $category === $cat ? 'fw-bold text-success' : '' ?>" href="index.php?category=<?= urlencode($cat) ?>">
                                            <span><?= htmlspecialchars($cat) ?></span>
                                            <span class="badge rounded-pill bg-light text-secondary ms-2"><?= $cat_counts[$cat] ?? 0 ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#manage-posts">Manage Posts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="bi bi-person-circle me-1"></i><?= is_logged_in() ? htmlspecialchars($_SESSION['auth_user']) : 'Log In' ?>
                            </a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-2">
                        <a href="#sidebar-search" class="btn btn-soft btn-sm px-2" title="Search Articles">
                            <i class="bi bi-search"></i>
                        </a>
                        <a href="create.php" class="btn btn-teal px-3">
                            <i class="bi bi-plus-lg me-1"></i>New Post
                        </a>
                        <?php if (is_logged_in()): ?>
                            <a href="logout.php" class="btn btn-soft px-3" title="Log Out">
                                <i class="bi bi-box-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ==========================================================
             MAIN MAGAZINE CONTENT
             ========================================================== -->
        <main class="p-3 p-md-4">

            <!-- Flash Notification Alert -->
            <?php if (!empty($_SESSION['flash_message'])): ?>
                <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'success') ?> alert-dismissible fade show rounded-3 border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert" style="background: var(--teal-light); color: var(--teal-dark);">
                    <i class="bi bi-check-circle-fill fs-5"></i>
                    <div class="fw-medium"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
            <?php endif; ?>

            <!-- ==========================================================
                 1. FEATURED POST HERO BANNER
                 ========================================================== -->
            <?php if ($featured_post): ?>
                <section class="featured-hero mb-4">
                    <div class="featured-hero-overlay">
                        <div class="col-lg-8">
                            <div class="eyebrow-teal mb-2">FEATURED POST &bull; <?= htmlspecialchars($featured_post['category']) ?></div>
                            <h1 class="hero-headline mb-3">
                                <?= htmlspecialchars($featured_post['title']) ?>
                            </h1>
                            <p class="mb-4" style="color: var(--text-body); font-size: 0.98rem; max-width: 640px;">
                                <?= htmlspecialchars($featured_post['summary'] ?? 'Discover insights, tutorials, and stories from our latest featured blog post.') ?>
                            </p>
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <a href="view.php?id=<?= (int) $featured_post['id'] ?>" class="btn btn-teal px-4 py-2">
                                    Read More <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                                <a href="edit.php?id=<?= (int) $featured_post['id'] ?>" class="btn btn-soft px-3 py-2">
                                    <i class="bi bi-pencil-square me-1"></i>Edit
                                </a>
                                <span class="small" style="color: var(--text-muted);">
                                    <?= date('M d, Y', strtotime($featured_post['created_at'])) ?>
                                    <span class="mx-2">&bull;</span>
                                    <i class="bi bi-eye me-1"></i><?= number_format((int) $featured_post['views']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- ==========================================================
                 2. TWO-COLUMN EDITORIAL GRID: LATEST POSTS + SIDEBAR
                 ========================================================== -->
            <div class="row g-4 mb-4" id="latest-posts">

                <!-- Left Column: Latest Blog Posts Cards -->
                <div class="col-lg-8">
                    <div class="section-box h-100">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <h2 class="h4 fw-bold mb-0 serif-title">
                                <?= $category !== '' ? htmlspecialchars($category) . ' Posts' : ($search !== '' ? 'Search Results' : 'Latest Blog Posts') ?>
                            </h2>
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($category !== '' || $search !== '' || $status !== ''): ?>
                                    <a href="index.php" class="section-header-link">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                                    </a>
                                <?php endif; ?>
                                <a href="#manage-posts" class="section-header-link">
                                    View All (<?= count($posts) ?>) <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>

                        <?php if (empty($posts)): ?>
                            <div class="text-center py-5 bg-white rounded-3 border">
                                <i class="bi bi-journal-x fs-1" style="color: var(--teal-primary);"></i>
                                <h3 class="h6 fw-bold mt-2">No blog posts found</h3>
                                <p class="small text-secondary mb-3">Try clearing your search filter or publish a new blog post.</p>
                                <a href="create.php" class="btn btn-teal btn-sm px-3">
                                    <i class="bi bi-plus-lg me-1"></i>Create First Post
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach (array_slice($posts, 0, 8) as $post): ?>
                                    <div class="col-sm-6 col-xl-3">
                                        <article class="post-card">
                                            <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="category-badge-teal">
                                                            <?= htmlspecialchars($post['category']) ?>
                                                        </span>
                                                        <?php if ($post['status'] !== 'Published'): ?>
                                                            <span class="badge <?= get_status_badge_class($post['status']) ?> rounded-pill" style="font-size: 0.65rem;">
                                                                <?= htmlspecialchars($post['status']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <h3 class="mb-2">
                                                        <a href="view.php?id=<?= (int) $post['id'] ?>" class="post-card-title">
                                                            <?= htmlspecialchars($post['title']) ?>
                                                        </a>
                                                    </h3>
                                                    <p class="small text-secondary mb-2" style="font-size: 0.8rem;">
                                                        <?= htmlspecialchars($post['summary']) ?>
                                                    </p>
                                                </div>
                                                <div class="pt-2 border-top d-flex justify-content-between align-items-center" style="font-size: 0.76rem; color: var(--text-muted);">
                                                    <span><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span><i class="bi bi-eye me-1"></i><?= number_format((int) $post['views']) ?></span>
                                                        <a href="edit.php?id=<?= (int) $post['id'] ?>" class="text-secondary" title="Edit Post">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Right Column: Search & Popular Posts Sidebar -->
                <div class="col-lg-4" id="sidebar-search">
                    <div class="section-box h-100 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Live Search Bar -->
                            <form action="index.php" method="GET" class="search-pill-box mb-4">
                                <input
                                    type="text"
                                    name="search"
                                    placeholder="Search articles..."
                                    value="<?= htmlspecialchars($search) ?>"
                                    aria-label="Search articles"
                                >
                                <button type="submit" class="btn btn-sm border-0 text-secondary" title="Search">
                                    <i class="bi bi-search"></i>
                                </button>
                            </form>

                            <!-- Popular Posts Header -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h3 class="h5 fw-bold mb-0 serif-title">Popular Posts</h3>
                                <a href="#manage-posts" class="section-header-link">View All <i class="bi bi-arrow-right"></i></a>
                            </div>

                            <!-- Popular Posts Items -->
                            <div>
                                <?php foreach ($popular_posts as $pop): ?>
                                    <a href="view.php?id=<?= (int) $pop['id'] ?>" class="popular-item">
                                        <div>
                                            <div class="popular-title"><?= htmlspecialchars($pop['title']) ?></div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">
                                                <?= date('M d, Y', strtotime($pop['created_at'])) ?>
                                                <span class="mx-1">&bull;</span>
                                                <i class="bi bi-eye me-1"></i><?= number_format((int) $pop['views']) ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-top text-center">
                            <a href="create.php" class="btn btn-teal w-100">
                                <i class="bi bi-pencil-square me-1"></i>Write a New Blog Post
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==========================================================
                 3. DISCOVERY ROW: FEATURED CATEGORIES + TRENDING + AUTHOR
                 ========================================================== -->
            <div class="row g-4 mb-4">

                <!-- Featured Categories -->
                <div class="col-lg-5">
                    <div class="section-box h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h5 fw-bold mb-0 serif-title">Featured Categories</h3>
                            <a href="index.php" class="section-header-link">View All <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="row g-2">
                            <?php foreach ($featured_cats as $fcat): ?>
                                <div class="col-6 col-sm-3">
                                    <a href="index.php?category=<?= urlencode($fcat) ?>" class="category-pill-card <?= $category === $fcat ? 'active' : '' ?>">
                                        <span class="category-circle-icon">
                                            <i class="bi <?= get_category_icon($fcat) ?>"></i>
                                        </span>
                                        <div class="fw-bold small text-dark text-truncate"><?= htmlspecialchars($fcat) ?></div>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">
                                            <?= $cat_counts[$fcat] ?? 0 ?> Posts
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Trending Articles -->
                <div class="col-lg-4">
                    <div class="section-box h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="h5 fw-bold mb-0 serif-title">Trending Articles</h3>
                            <a href="#manage-posts" class="section-header-link">View All <i class="bi bi-arrow-right"></i></a>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach (array_slice($popular_posts, 0, 4) as $idx => $trend): ?>
                                <a href="view.php?id=<?= (int) $trend['id'] ?>" class="d-flex align-items-center gap-2 text-decoration-none py-1">
                                    <span class="trending-rank-badge"><?= $idx + 1 ?></span>
                                    <span class="small fw-semibold text-dark text-truncate"><?= htmlspecialchars($trend['title']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Author Profile Card -->
                <div class="col-lg-3">
                    <div class="section-box h-100 d-flex flex-column justify-content-center">
                        <div class="d-flex align-items-center gap-3 mb-2">
                            <div class="brand-logo-mark rounded-circle flex-shrink-0" style="width: 56px; height: 56px; font-size: 1.4rem;">
                                J
                            </div>
                            <div>
                                <div class="author-script-greeting">Hi, I'm Jam Alfy</div>
                                <div class="small text-secondary">Administrator &amp; Author</div>
                            </div>
                        </div>
                        <p class="small mb-3" style="color: var(--text-body); font-size: 0.82rem;">
                            Sharing practical web development tutorials, UI design patterns, and stories that inspire minds.
                        </p>
                        <div class="d-flex gap-2">
                            <a href="create.php" class="btn btn-soft btn-sm w-100">
                                <i class="bi bi-plus-circle me-1"></i>New Article
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ==========================================================
                 4. POST MANAGEMENT DIRECTORY (Full MySQL CRUD Table)
                 ========================================================== -->
            <section class="section-box" id="manage-posts">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <span class="eyebrow-teal">Post Management System</span>
                        <h2 class="h4 fw-bold mb-0 serif-title">Manage All Blog Posts</h2>
                    </div>

                    <!-- Quick Filter Form -->
                    <form action="index.php#manage-posts" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                        <select name="category" class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select name="status" class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Statuses</option>
                            <option value="Published" <?= $status === 'Published' ? 'selected' : '' ?>>Published</option>
                            <option value="Draft" <?= $status === 'Draft' ? 'selected' : '' ?>>Draft</option>
                            <option value="Archived" <?= $status === 'Archived' ? 'selected' : '' ?>>Archived</option>
                        </select>
                        <button type="submit" class="btn btn-soft btn-sm px-3">Filter</button>
                        <a href="create.php" class="btn btn-teal btn-sm px-3">
                            <i class="bi bi-plus-lg me-1"></i>Add New Post
                        </a>
                    </form>
                </div>

                <div class="bg-white rounded-3 border overflow-hidden shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 60px;">ID</th>
                                    <th>Title &amp; Summary</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Views</th>
                                    <th>Date</th>
                                    <th class="text-end" style="min-width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($posts)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-secondary small">
                                            No records found. Click <strong>Add New Post</strong> to insert a record.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($posts as $post): ?>
                                        <tr>
                                            <td class="fw-bold" style="color: var(--teal-primary);">#<?= (int) $post['id'] ?></td>
                                            <td style="max-width: 320px;">
                                                <a href="view.php?id=<?= (int) $post['id'] ?>" class="fw-bold text-dark text-decoration-none d-block text-truncate">
                                                    <?= htmlspecialchars($post['title']) ?>
                                                </a>
                                                <small class="text-secondary d-block text-truncate">
                                                    <?= htmlspecialchars($post['summary']) ?>
                                                </small>
                                            </td>
                                            <td class="small fw-medium"><?= htmlspecialchars($post['author']) ?></td>
                                            <td>
                                                <span class="category-badge-teal">
                                                    <?= htmlspecialchars($post['category']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?= get_status_badge_class($post['status']) ?> rounded-pill px-2 py-1">
                                                    <?= htmlspecialchars($post['status']) ?>
                                                </span>
                                            </td>
                                            <td class="small text-secondary">
                                                <i class="bi bi-eye me-1"></i><?= number_format((int) $post['views']) ?>
                                            </td>
                                            <td class="small text-secondary">
                                                <?= date('M d, Y', strtotime($post['created_at'])) ?>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    <a href="view.php?id=<?= (int) $post['id'] ?>" class="btn btn-sm btn-soft" title="Read Post">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?= (int) $post['id'] ?>" class="btn btn-sm btn-soft" title="Edit Post">
                                                        <i class="bi bi-pencil-square" style="color: var(--teal-primary);"></i>
                                                    </a>
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-soft text-danger"
                                                        title="Delete Post"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal"
                                                        data-post-id="<?= (int) $post['id'] ?>"
                                                        data-post-title="<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>"
                                                    >
                                                        <i class="bi bi-trash3"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>

        <!-- ==========================================================
             FOOTER (Log Out Button Only)
             ========================================================== -->
        <footer class="blogify-footer py-3 px-4 d-flex justify-content-end align-items-center">
            <a href="logout.php" class="btn btn-teal btn-sm px-4">
                <i class="bi bi-box-arrow-right me-1"></i>Log Out
            </a>
        </footer>

    </div>

    <!-- ==========================================================
         DELETE CONFIRMATION MODAL
         ========================================================== -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold serif-title" id="deleteModalLabel">
                        <i class="bi bi-trash3-fill text-danger me-2"></i>Delete Blog Post?
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="mb-2 text-secondary small">Are you sure you want to permanently remove this post?</p>
                    <div class="p-3 rounded-3 small fw-bold text-dark" id="deletePostTitlePreview" style="background: var(--teal-light); border: 1px solid var(--teal-border);"></div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-soft px-3" data-bs-dismiss="modal">Cancel</button>
                    <form action="delete.php" method="POST" class="d-inline">
                        <input type="hidden" name="id" id="deletePostIdInput" value="">
                        <button type="submit" class="btn btn-danger px-4">
                            <i class="bi bi-trash3 me-1"></i>Delete Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const deleteModal = document.getElementById('deleteModal');
        if (deleteModal) {
            deleteModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;
                const postId = button.getAttribute('data-post-id');
                const postTitle = button.getAttribute('data-post-title');
                document.getElementById('deletePostIdInput').value = postId;
                document.getElementById('deletePostTitlePreview').textContent = '#' + postId + ' — ' + postTitle;
            });
        }
    </script>
</body>
</html>
