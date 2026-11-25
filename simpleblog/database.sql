SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL DEFAULT 'Jam Alfy Ricamata',
  `role` varchar(50) NOT NULL DEFAULT 'Administrator',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `users` (`id`, `username`, `password`, `full_name`, `role`) VALUES
(1, 'jamalfy', 'jam12345', 'Jam Alfy Ricamata', 'Administrator');

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `category` varchar(80) NOT NULL,
  `summary` varchar(300) NOT NULL,
  `content` text NOT NULL,
  `status` enum('Published','Draft','Archived') NOT NULL DEFAULT 'Published',
  `reading_time` int(11) NOT NULL DEFAULT 3,
  `views` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `posts` (`id`, `title`, `author`, `category`, `summary`, `content`, `status`, `reading_time`, `views`, `created_at`) VALUES
(1, 'Getting Started with PHP and MySQL CRUD Applications', 'Jam Alfy Ricamata', 'Web Development', 'A practical guide to building clean, responsive Create, Read, Update, and Delete applications using PHP, MySQL, and Bootstrap 5.', 'Building a CRUD (Create, Read, Update, Delete) application is the foundation of modern back-end web development.\n\nIn this post, we explore how PHP interacts with a MySQL relational database using prepared statements to prevent SQL injection while keeping the codebase easy to maintain.', 'Published', 5, 142, '2026-11-10 09:30:00'),
(2, 'Designing Responsive Interfaces with Bootstrap 5', 'Jam Alfy Ricamata', 'UI/UX Design', 'Learn how Bootstrap 5 grid layouts, utility classes, and components help you ship consistent interfaces across desktop and mobile devices.', 'Bootstrap 5 makes front-end development faster and more consistent by offering a mobile-first 12-column flexbox grid, accessible form controls, and versatile components like navigation bars, badges, modals, and responsive tables.', 'Published', 4, 98, '2026-11-12 14:15:00'),
(3, 'Deploying Your PHP & MySQL Project to InfinityFree', 'Jam Alfy Ricamata', 'Cloud & Hosting', 'Step-by-step checklist for exporting your local XAMPP database, configuring db_connect.php, and going live on InfinityFree.', 'Moving a project from localhost (XAMPP) to a live production server on InfinityFree only takes a few minutes when you follow a structured deployment checklist.', 'Published', 6, 76, '2026-11-14 18:45:00'),
(4, 'Database Normalization and Choosing Proper Data Types', 'Jam Alfy Ricamata', 'Database Systems', 'Why selecting the right MySQL data types (VARCHAR, TEXT, INT, ENUM, TIMESTAMP) matters for performance and data integrity.', 'Every reliable web system starts with a thoughtful database schema. Defining an UNSIGNED INT AUTO_INCREMENT primary key guarantees unique record identification.', 'Draft', 5, 19, '2026-11-15 20:10:00');

COMMIT;
