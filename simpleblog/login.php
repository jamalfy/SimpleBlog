<?php
require_once 'db_connect.php';

$error    = '';
$username = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = "Please enter both your username and password.";
    } else {
        $authenticated = false;
        $full_name     = 'Jam Alfy Ricamata';
        $role          = 'Administrator';

        // 1. Check against MySQL `users` table
        $stmt = $conn->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $res  = $stmt->get_result();
            $user = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
                $authenticated = true;
                $username      = $user['username'];
                $full_name     = $user['full_name'];
                $role          = $user['role'];
            }
        }

        // 2. Built-in fallback check for jamalfy / jam12345
        if (!$authenticated && $username === 'jamalfy' && $password === 'jam12345') {
            $authenticated = true;
            $username      = 'jamalfy';
        }

        if ($authenticated) {
            $_SESSION['auth_user'] = $username;
            $_SESSION['auth_name'] = $full_name;
            $_SESSION['auth_role'] = $role;
            $_SESSION['login_at']  = date('M d, Y h:i A');

            $_SESSION['flash_message'] = "Welcome back, {$username}! You are now signed in.";
            $_SESSION['flash_type']    = "success";
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In &mdash; SimpleBlog</title>
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
                            <a class="nav-link active" href="login.php">
                                <i class="bi bi-person-circle me-1"></i><?= is_logged_in() ? htmlspecialchars($_SESSION['auth_user']) : 'Log In' ?>
                            </a>
                        </li>
                    </ul>
                    <div class="d-flex align-items-center gap-2">
                        <?php if (is_logged_in()): ?>
                            <a href="logout.php" class="btn btn-soft btn-sm px-3">
                                <i class="bi bi-box-arrow-right me-1"></i>Log Out
                            </a>
                        <?php else: ?>
                            <a href="index.php" class="btn btn-soft btn-sm px-3">
                                <i class="bi bi-arrow-left me-1"></i>Back
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Login Section -->
        <main class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-7 col-lg-5">

                    <?php if (!empty($_SESSION['flash_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm d-flex align-items-center gap-2 mb-4" role="alert" style="background: var(--teal-light); color: var(--teal-dark);">
                            <i class="bi bi-info-circle-fill fs-5"></i>
                            <div class="small fw-medium"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
                    <?php endif; ?>

                    <div class="section-box p-4 p-md-5 bg-white shadow-sm">
                        <div class="text-center mb-4">
                            <div class="brand-logo-mark mx-auto mb-3" style="width: 52px; height: 52px; font-size: 1.6rem;">
                                B
                            </div>
                            <div class="eyebrow-teal mb-1">Welcome Back</div>
                            <h1 class="h3 fw-bold serif-title mb-0">Sign In to SimpleBlog</h1>
                        </div>

                        <?php if (is_logged_in()): ?>
                            <div class="p-3 rounded-3 mb-4 text-center" style="background: var(--teal-light); border: 1px solid var(--teal-border);">
                                <div class="small fw-semibold" style="color: var(--teal-dark);">Currently Signed In As</div>
                                <div class="fs-5 fw-bold text-dark mt-1">
                                    <i class="bi bi-person-check-fill me-1" style="color: var(--teal-primary);"></i>
                                    <?= htmlspecialchars($_SESSION['auth_user']) ?>
                                </div>
                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <a href="index.php" class="btn btn-teal btn-sm px-3">Go to Home</a>
                                    <a href="logout.php" class="btn btn-soft btn-sm px-3">Log Out</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error !== ''): ?>
                            <div class="alert alert-danger py-2 px-3 mb-3 small rounded-3">
                                <i class="bi bi-exclamation-circle-fill me-1"></i>
                                <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>

                        <form action="login.php" method="POST" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label small fw-bold text-secondary mb-1">Username</label>
                                <input
                                    type="text"
                                    id="username"
                                    name="username"
                                    class="form-control"
                                    placeholder="Enter username"
                                    required
                                    autofocus
                                    value="<?= htmlspecialchars($username) ?>"
                                >
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label small fw-bold text-secondary mb-1">Password</label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Enter password"
                                        required
                                    >
                                    <button type="button" class="btn btn-soft" id="togglePasswordBtn" title="Show/Hide Password">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-teal w-100 py-2">
                                Log In <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        if (toggleBtn && passwordInput && toggleIcon) {
            toggleBtn.addEventListener('click', () => {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
            });
        }
    </script>
</body>
</html>
