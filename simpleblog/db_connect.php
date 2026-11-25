<?php
/**
 * Database Connection Configuration (db_connect.php)
 * SimpleBlog & Post Management System
 */

// Enable helpful error display instead of blank HTTP 500 pages on InfinityFree
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// ----------------------------------------------------------
// 1. DATABASE CREDENTIALS (InfinityFree & XAMPP Auto-Support)
// ----------------------------------------------------------
$db_host = "sql303.infinityfree.com";
$db_user = "if0_43463122";
$db_pass = "jamalfy012826";
// IMPORTANT: Make sure $db_name below matches your exact database name
// shown in InfinityFree -> MySQL Databases (e.g., if0_43463122_simpleblog)
$db_name = "if0_43463122_simpleblog";

// Automatically use local XAMPP credentials when testing on localhost
$http_host = $_SERVER['HTTP_HOST'] ?? '';
$is_local  = ($http_host === 'localhost' || $http_host === '127.0.0.1' || strpos($http_host, 'localhost:') === 0);

if ($is_local) {
    $db_host = "localhost";
    $db_user = "root";
    $db_pass = "";
    $db_name = "simpleblog_db";
}

// ----------------------------------------------------------
// 2. ESTABLISH CONNECTION (Protected with try/catch against HTTP 500)
// ----------------------------------------------------------
mysqli_report(MYSQLI_REPORT_OFF);
$conn       = null;
$conn_error = "";

try {
    if ($is_local) {
        $temp_conn = @new mysqli($db_host, $db_user, $db_pass);
        if ($temp_conn && !$temp_conn->connect_error) {
            $safe_db = $temp_conn->real_escape_string($db_name);
            @$temp_conn->query("CREATE DATABASE IF NOT EXISTS `$safe_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            @$temp_conn->close();
        }
    }

    $conn = @new mysqli($db_host, $db_user, $db_pass, $db_name);

    // If the default database suffix "if0_43463122_simpleblog" wasn't found,
    // try common database names or auto-discover via SHOW DATABASES
    if ((!$conn || $conn->connect_error) && !$is_local) {
        $conn_error = $conn ? $conn->connect_error : "Connection failed";
        $probe_conn = @new mysqli($db_host, $db_user, $db_pass);
        if ($probe_conn && !$probe_conn->connect_error) {
            $db_list = @$probe_conn->query("SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME NOT IN ('information_schema', 'performance_schema', 'mysql', 'sys')");
            if (!$db_list) {
                $db_list = @$probe_conn->query("SHOW DATABASES");
            }
            if ($db_list) {
                while ($db_row = $db_list->fetch_row()) {
                    $candidate = $db_row[0];
                    if (stripos($candidate, 'information_schema') === false && @$probe_conn->select_db($candidate)) {
                        $db_name    = $candidate;
                        $conn       = $probe_conn;
                        $conn_error = "";
                        break;
                    }
                }
            }
        }
    } elseif ($conn && $conn->connect_error) {
        $conn_error = $conn->connect_error;
    }
} catch (Throwable $e) {
    $conn_error = $e->getMessage();
}

if (!$conn || $conn->connect_error || $conn_error !== "") {
    $err_msg = $conn_error !== "" ? $conn_error : ($conn ? $conn->connect_error : "Unknown MySQL connection error");
    die("
        <div style='font-family: -apple-system, BlinkMacSystemFont, sans-serif; max-width: 680px; margin: 50px auto; padding: 28px; border: 1px solid #111; background: #111114; color: #ffffff; border-radius: 16px; box-shadow: 0 12px 30px rgba(0,0,0,0.2);'>
            <h2 style='margin-top: 0; color: #ffffff;'>MySQL Database Connection Notice</h2>
            <p style='color: #d1d1d6;'>Could not connect to database <strong style='color:#fff;'>" . htmlspecialchars($db_name) . "</strong> on host <strong style='color:#fff;'>" . htmlspecialchars($db_host) . "</strong>.</p>
            <div style='font-size: 0.9rem; background: #1b1b20; border: 1px solid rgba(255,255,255,0.2); padding: 12px 16px; border-radius: 10px; margin: 16px 0;'>
                <strong>MySQL Error:</strong> " . htmlspecialchars($err_msg) . "
            </div>
            <p style='margin-bottom: 0; font-size: 0.92rem; line-height: 1.6; color: #d1d1d6;'>
                <strong style='color:#fff;'>How to fix on InfinityFree:</strong><br>
                1. Look at your InfinityFree <strong>MySQL Databases</strong> table or phpMyAdmin tab.<br>
                2. Check your exact <strong>MySQL Database Name</strong> (e.g., <code>if0_43463122_xxx</code>) and <strong>MySQL Password</strong>.<br>
                3. Put that exact database name in <code>\$db_name</code> on <strong>line 19</strong> of <code>db_connect.php</code>.
            </p>
        </div>
    ");
}

@$conn->set_charset("utf8mb4");

// ----------------------------------------------------------
// 3. ENSURE `posts` AND `users` TABLES EXIST
// ----------------------------------------------------------
try {
    $table_check = @$conn->query("SHOW TABLES LIKE 'posts'");
    if ($table_check && $table_check->num_rows === 0) {
        $create_sql = "CREATE TABLE IF NOT EXISTS `posts` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `author` VARCHAR(100) NOT NULL,
            `category` VARCHAR(80) NOT NULL,
            `summary` VARCHAR(300) NOT NULL,
            `content` TEXT NOT NULL,
            `status` ENUM('Published', 'Draft', 'Archived') NOT NULL DEFAULT 'Published',
            `reading_time` INT(11) NOT NULL DEFAULT 3,
            `views` INT(11) NOT NULL DEFAULT 0,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        if (@$conn->query($create_sql)) {
            $seed_sql = "INSERT INTO `posts` (`title`, `author`, `category`, `summary`, `content`, `status`, `reading_time`, `views`, `created_at`) VALUES
            ('Getting Started with PHP and MySQL CRUD Applications', 'Jam Alfy Ricamata', 'Web Development', 'A practical guide to building clean, responsive Create, Read, Update, and Delete applications using PHP, MySQL, and Bootstrap 5.', 'Building a CRUD (Create, Read, Update, Delete) application is the foundation of modern back-end web development.\n\nIn this post, we explore how PHP interacts with a MySQL relational database using prepared statements to prevent SQL injection while keeping the codebase easy to maintain.', 'Published', 5, 142, '2026-11-10 09:30:00'),
            ('Designing Responsive Interfaces with Bootstrap 5', 'Jam Alfy Ricamata', 'UI/UX Design', 'Learn how Bootstrap 5 grid layouts, utility classes, and components help you ship consistent interfaces across desktop and mobile devices.', 'Bootstrap 5 makes front-end development faster and more consistent by offering a mobile-first 12-column flexbox grid, accessible form controls, and versatile components like navigation bars, badges, modals, and responsive tables.', 'Published', 4, 98, '2026-11-12 14:15:00'),
            ('Deploying Your PHP & MySQL Project to InfinityFree', 'Jam Alfy Ricamata', 'Cloud & Hosting', 'Step-by-step checklist for exporting your local XAMPP database, configuring db_connect.php, and going live on InfinityFree.', 'Moving a project from localhost (XAMPP) to a live production server on InfinityFree only takes a few minutes when you follow a structured deployment checklist.', 'Published', 6, 76, '2026-11-14 18:45:00'),
            ('Database Normalization and Choosing Proper Data Types', 'Jam Alfy Ricamata', 'Database Systems', 'Why selecting the right MySQL data types (VARCHAR, TEXT, INT, ENUM, TIMESTAMP) matters for performance and data integrity.', 'Every reliable web system starts with a thoughtful database schema. Defining an UNSIGNED INT AUTO_INCREMENT primary key guarantees unique record identification.', 'Draft', 5, 19, '2026-11-15 20:10:00');";
            @$conn->query($seed_sql);
        }
    }

    $users_table_check = @$conn->query("SHOW TABLES LIKE 'users'");
    if ($users_table_check && $users_table_check->num_rows === 0) {
        $create_users_sql = "CREATE TABLE IF NOT EXISTS `users` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `username` VARCHAR(50) NOT NULL UNIQUE,
            `password` VARCHAR(255) NOT NULL,
            `full_name` VARCHAR(100) NOT NULL DEFAULT 'Jam Alfy Ricamata',
            `role` VARCHAR(50) NOT NULL DEFAULT 'Administrator',
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        if (@$conn->query($create_users_sql)) {
            @$conn->query("INSERT IGNORE INTO `users` (`username`, `password`, `full_name`, `role`) VALUES ('jamalfy', 'jam12345', 'Jam Alfy Ricamata', 'Administrator')");
        }
    }
} catch (Throwable $e) {
    // Continue gracefully if tables already exist
}

// Start PHP session for flash notifications and authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Helper: Check whether the user is currently logged in
 */
function is_logged_in(): bool {
    return !empty($_SESSION['auth_user']);
}

/**
 * Helper: Require authentication for protected pages
 */
function require_login(): void {
    if (!is_logged_in()) {
        $_SESSION['flash_message'] = "Please log in with your account to continue.";
        $_SESSION['flash_type']    = "warning";
        header("Location: login.php");
        exit;
    }
}

/**
 * Helper: Return list of standard blog categories
 */
function get_blog_categories(): array {
    return [
        'Web Development',
        'UI/UX Design',
        'Database Systems',
        'Cloud & Hosting',
        'Programming Tutorials',
        'Technology News',
        'Announcements',
        'Personal & Lifestyle'
    ];
}

/**
 * Helper: Return Bootstrap badge classes for post status
 */
function get_status_badge_class(string $status): string {
    switch ($status) {
        case 'Published':
            return 'badge-status-published';
        case 'Draft':
            return 'badge-status-draft';
        case 'Archived':
            return 'badge-status-archived';
        default:
            return 'badge-pink';
    }
}

/**
 * Helper: Bootstrap icon class for Featured Categories circles
 */
function get_category_icon(string $category): string {
    $icons = [
        'Web Development'       => 'bi-laptop',
        'UI/UX Design'          => 'bi-palette',
        'Database Systems'      => 'bi-hdd-network',
        'Cloud & Hosting'       => 'bi-cloud-check',
        'Programming Tutorials' => 'bi-code-square',
        'Technology News'       => 'bi-cpu',
        'Announcements'         => 'bi-megaphone',
        'Personal & Lifestyle'  => 'bi-cup-hot',
    ];
    return $icons[$category] ?? 'bi-journal-text';
}

/**
 * Helper: Output the Blogify Editorial + Apple Fluid CSS inline
 * so the theme always renders even if assets/style.css is cached or not uploaded.
 */
function render_apple_theme_css(): void {
    $css_path = __DIR__ . '/assets/style.css';
    if (is_file($css_path)) {
        echo "<style>\n" . file_get_contents($css_path) . "\n</style>\n";
        return;
    }
    echo <<<'CSS'
<style>
:root {
    --bg-page: #f3f5f7;
    --bg-frame: #ffffff;
    --bg-section: #f7f9fa;
    --bg-card: #ffffff;
    --teal-primary: #149b8e;
    --teal-dark: #0e7d72;
    --teal-light: #e6f5f3;
    --teal-border: #bce5e0;
    --text-dark: #14181c;
    --text-body: #4a5562;
    --text-muted: #7b8794;
    --border-light: #e6eaee;
    --shadow-soft: 0 4px 20px rgba(20, 24, 28, 0.05);
    --shadow-hover: 0 12px 28px rgba(20, 24, 28, 0.10);
    --spring-smooth: cubic-bezier(0.22, 1, 0.36, 1);
    --font-serif: "Playfair Display", Georgia, serif;
    --font-sans: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Inter", system-ui, sans-serif;
}
html, body {
    background-color: var(--bg-page) !important;
    color: var(--text-dark) !important;
    font-family: var(--font-sans);
}
.magazine-shell {
    background: var(--bg-frame);
    max-width: 1280px;
    margin: 1.25rem auto;
    border-radius: 1.25rem;
    box-shadow: 0 10px 40px rgba(15, 23, 42, 0.06);
    overflow: hidden;
    border: 1px solid var(--border-light);
}
.blogify-navbar {
    background: rgba(255, 255, 255, 0.95) !important;
    border-bottom: 1px solid var(--border-light);
    padding: 0.9rem 0;
}
.brand-logo-mark {
    width: 40px; height: 40px; border-radius: 10px;
    background: var(--teal-primary); color: #fff;
    font-family: var(--font-serif); font-weight: 800; font-size: 1.45rem;
    display: inline-flex; align-items: center; justify-content: center;
}
.brand-title { font-family: var(--font-serif); font-weight: 700; font-size: 1.35rem; color: var(--text-dark); line-height: 1.1; }
.brand-subtitle { font-size: 0.68rem; color: var(--text-muted); display: block; }
.btn-teal { background-color: var(--teal-primary) !important; border-color: var(--teal-primary) !important; color: #fff !important; }
.btn-soft { background-color: #fff !important; border: 1px solid var(--border-light) !important; color: var(--text-dark) !important; }
.featured-hero { position: relative; border-radius: 1.1rem; overflow: hidden; background: var(--bg-section); border: 1px solid var(--border-light); }
.featured-hero-overlay { width: 100%; padding: 2.25rem; }
.hero-headline, .serif-title, h1, h2, .h3, .h4 { font-family: var(--font-serif); color: var(--text-dark); }
.section-box { background: var(--bg-section); border: 1px solid var(--border-light); border-radius: 1rem; padding: 1.5rem; }
.post-card { background: #fff; border: 1px solid var(--border-light); border-radius: 0.85rem; overflow: hidden; height: 100%; display: flex; flex-direction: column; box-shadow: var(--shadow-soft); }
.post-card-img-wrap { position: relative; height: 155px; overflow: hidden; }
.post-card-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.category-badge-teal { background: var(--teal-primary); color: #fff; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; padding: 0.25rem 0.6rem; border-radius: 0.35rem; }
.post-card-title { font-family: var(--font-serif); font-weight: 700; font-size: 1.02rem; color: var(--text-dark); text-decoration: none; }
.popular-item { display: flex; align-items: center; gap: 0.85rem; padding: 0.65rem 0; border-bottom: 1px solid var(--border-light); text-decoration: none; }
.popular-thumb { width: 58px; height: 58px; border-radius: 0.55rem; object-fit: cover; flex-shrink: 0; }
.popular-title { font-family: var(--font-serif); font-size: 0.92rem; font-weight: 700; color: var(--text-dark); }
.category-pill-card { text-decoration: none; text-align: center; padding: 0.85rem 0.5rem; border-radius: 0.85rem; background: #fff; border: 1px solid var(--border-light); display: block; color: var(--text-dark); }
.category-circle-icon { width: 50px; height: 50px; border-radius: 50%; background: var(--teal-light); color: var(--teal-primary); display: inline-flex; align-items: center; justify-content: center; font-size: 1.25rem; margin-bottom: 0.5rem; }
.trending-rank-badge { width: 26px; height: 26px; border-radius: 50%; background: var(--teal-primary); color: #fff; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.blogify-footer { background: #111315; color: #a0aab4; padding: 2.5rem 2rem 1.5rem; margin-top: 2rem; }
</style>
CSS;
}
?>
