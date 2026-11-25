<?php
require_once 'db_connect.php';
require_login();

$categories = get_blog_categories();
$errors     = [];

$title        = '';
$author       = $_SESSION['auth_name'] ?? 'Jam Alfy Ricamata';
$category     = $categories[0];
$summary      = '';
$content      = '';
$status       = 'Published';
$reading_time = 3;

// ----------------------------------------------------------
// HANDLE FORM SUBMISSION (MySQL INSERT Operation)
// ----------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $author       = trim($_POST['author'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $summary      = trim($_POST['summary'] ?? '');
    $content      = trim($_POST['content'] ?? '');
    $status       = trim($_POST['status'] ?? 'Published');
    $reading_time = (int) ($_POST['reading_time'] ?? 3);

    if ($title === '') {
        $errors[] = "Post title is required.";
    } elseif (strlen($title) > 255) {
        $errors[] = "Post title must not exceed 255 characters.";
    }

    if ($author === '') {
        $errors[] = "Author name is required.";
    } elseif (strlen($author) > 100) {
        $errors[] = "Author name must not exceed 100 characters.";
    }

    if ($category === '') {
        $errors[] = "Please select a category.";
    }

    if ($summary === '') {
        $errors[] = "Short summary is required.";
    } elseif (strlen($summary) > 300) {
        $errors[] = "Short summary must not exceed 300 characters.";
    }

    if ($content === '') {
        $errors[] = "Article content cannot be empty.";
    }

    if (!in_array($status, ['Published', 'Draft', 'Archived'], true)) {
        $status = 'Published';
    }

    if ($reading_time < 1 || $reading_time > 120) {
        $reading_time = max(1, (int) ceil(str_word_count(strip_tags($content)) / 200));
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "INSERT INTO posts (title, author, category, summary, content, status, reading_time) 
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssssi", $title, $author, $category, $summary, $content, $status, $reading_time);

        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;
            $stmt->close();

            $_SESSION['flash_message'] = "New blog post (#{$new_id}: \"{$title}\") has been published!";
            $_SESSION['flash_type']    = "success";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Database error while saving post: " . $conn->error;
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Write New Post &mdash; SimpleBlog</title>
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
                            <a class="nav-link" href="index.php#latest-posts">Blog</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="index.php#manage-posts">Manage Posts</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="create.php">Write Post</a>
                        </li>
                    </ul>
                    <div class="d-flex gap-2">
                        <a href="index.php" class="btn btn-soft btn-sm px-3">
                            <i class="bi bi-arrow-left me-1"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <main class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div>
                            <div class="eyebrow-teal">Create New Record</div>
                            <h1 class="h3 fw-bold serif-title mb-0">Write New Blog Post</h1>
                        </div>
                        <a href="index.php" class="btn btn-soft btn-sm px-3">Cancel</a>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger rounded-3 mb-4">
                            <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i>Please check the following:</h6>
                            <ul class="mb-0 small">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="section-box bg-white p-4 p-md-5 shadow-sm">
                        <form action="create.php" method="POST" novalidate>
                            <div class="row g-3">

                                <div class="col-12">
                                    <label for="title" class="form-label small fw-bold text-secondary">Post Title <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        id="title"
                                        name="title"
                                        class="form-control"
                                        placeholder="e.g., The Power of Consistent Habits"
                                        maxlength="255"
                                        required
                                        value="<?= htmlspecialchars($title) ?>"
                                    >
                                </div>

                                <div class="col-md-5">
                                    <label for="author" class="form-label small fw-bold text-secondary">Author Name <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        id="author"
                                        name="author"
                                        class="form-control"
                                        placeholder="Author name"
                                        maxlength="100"
                                        required
                                        value="<?= htmlspecialchars($author) ?>"
                                    >
                                </div>

                                <div class="col-md-4">
                                    <label for="category" class="form-label small fw-bold text-secondary">Category <span class="text-danger">*</span></label>
                                    <select id="category" name="category" class="form-select" required>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($cat) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="status" class="form-label small fw-bold text-secondary">Status <span class="text-danger">*</span></label>
                                    <select id="status" name="status" class="form-select" required>
                                        <option value="Published" <?= $status === 'Published' ? 'selected' : '' ?>>Published</option>
                                        <option value="Draft" <?= $status === 'Draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="Archived" <?= $status === 'Archived' ? 'selected' : '' ?>>Archived</option>
                                    </select>
                                </div>

                                <div class="col-md-9">
                                    <label for="summary" class="form-label small fw-bold text-secondary">
                                        Short Summary <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="summary"
                                        name="summary"
                                        class="form-control"
                                        placeholder="1-2 sentence overview of your article..."
                                        maxlength="300"
                                        required
                                        value="<?= htmlspecialchars($summary) ?>"
                                    >
                                </div>

                                <div class="col-md-3">
                                    <label for="reading_time" class="form-label small fw-bold text-secondary">Read Time (min)</label>
                                    <input
                                        type="number"
                                        id="reading_time"
                                        name="reading_time"
                                        class="form-control"
                                        min="1"
                                        max="120"
                                        value="<?= (int) $reading_time ?>"
                                    >
                                </div>

                                <div class="col-12">
                                    <label for="content" class="form-label small fw-bold text-secondary">Article Content <span class="text-danger">*</span></label>
                                    <textarea
                                        id="content"
                                        name="content"
                                        class="form-control"
                                        rows="9"
                                        placeholder="Write your full blog post here..."
                                        required
                                    ><?= htmlspecialchars($content) ?></textarea>
                                    <div class="d-flex justify-content-end mt-1">
                                        <small id="wordCountHelper" class="text-secondary">0 words</small>
                                    </div>
                                </div>

                                <div class="col-12 pt-3 border-top d-flex justify-content-end gap-2">
                                    <a href="index.php" class="btn btn-soft px-4">Cancel</a>
                                    <button type="submit" class="btn btn-teal px-4">
                                        <i class="bi bi-check2-circle me-1"></i>Publish Post
                                    </button>
                                </div>

                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const contentArea = document.getElementById('content');
        const wordHelper = document.getElementById('wordCountHelper');
        const readingTimeInput = document.getElementById('reading_time');

        if (contentArea && wordHelper) {
            const updateCount = () => {
                const text = contentArea.value.trim();
                const words = text ? text.split(/\s+/).length : 0;
                wordHelper.textContent = words + ' word' + (words === 1 ? '' : 's');
                if (words > 0 && readingTimeInput) {
                    readingTimeInput.value = Math.max(1, Math.ceil(words / 180));
                }
            };
            contentArea.addEventListener('input', updateCount);
            updateCount();
        }
    </script>
</body>
</html>
