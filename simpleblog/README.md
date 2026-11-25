# SimpleBlog & Post Management System

**Course:** PHP and MySQL Development (Web Systems Technologies)  
**Tech Stack:** HTML5, CSS3, Bootstrap 5.3, PHP, MySQL  
**Deployment Target:** Local XAMPP & InfinityFree

---

## 1. System Requirements Coverage

### A. Front-End (Bootstrap 5 + HTML/CSS)
- Responsive **Bootstrap 5 Navigation Bar**, summary metric cards, search/filter controls, forms, responsive table, action buttons, and confirmation modal.
- Dual presentation: **Post Management Directory Table** (Admin CRUD view) and **Latest Blog Articles** (Reader card grid + full article view).

### B. Back-End (PHP + MySQL CRUD Operations)
- **INSERT (`create.php`)**: Add new blog post records using prepared statements and form validation.
- **SELECT (`index.php` & `view.php`)**: View all blog posts with keyword search, category filtering, status filtering, and full article reader view.
- **UPDATE (`edit.php`)**: Edit existing blog post records with pre-filled form fields.
- **DELETE (`delete.php`)**: Remove blog post records from the database with modal confirmation.

### C. Database Design (`database.sql`)
- Table name: `posts` (**11 fields** — exceeds the minimum 5-field requirement):
  1. `id` — `INT(11) UNSIGNED NOT NULL AUTO_INCREMENT` (**Primary Key**)
  2. `title` — `VARCHAR(255) NOT NULL`
  3. `author` — `VARCHAR(100) NOT NULL`
  4. `category` — `VARCHAR(80) NOT NULL`
  5. `summary` — `VARCHAR(300) NOT NULL`
  6. `content` — `TEXT NOT NULL`
  7. `status` — `ENUM('Published', 'Draft', 'Archived') NOT NULL DEFAULT 'Published'`
  8. `reading_time` — `INT(11) NOT NULL DEFAULT 3`
  9. `views` — `INT(11) NOT NULL DEFAULT 0`
  10. `created_at` — `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP`
  11. `updated_at` — `TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`

---

## 2. Running Locally on XAMPP

1. Copy this project folder into `C:\xampp\htdocs\simpleblog\`.
2. Open the **XAMPP Control Panel** and start **Apache** and **MySQL**.
3. Open your browser and visit `http://localhost/simpleblog/`.
   - *Note:* `db_connect.php` automatically creates the `simpleblog_db` database, the `posts` table, and 4 sample posts on first run! You can also manually import `database.sql` in `http://localhost/phpmyadmin`.

---

## 3. InfinityFree Deployment Steps

1. Create a free account at `https://www.infinityfree.com` and create a new **Hosting Account**.
2. Go to **Control Panel → MySQL Databases**:
   - Create a new database (e.g., `if0_xxxxxxx_simpleblog`).
   - Copy your **MySQL Hostname**, **MySQL Username**, **MySQL Password**, and **Database Name**.
3. Open `db_connect.php` and update lines 13–16 with your InfinityFree credentials:
   ```php
   $db_host = "sqlXXX.infinityfree.com";
   $db_user = "if0_12345678";
   $db_pass = "your_hosting_password";
   $db_name = "if0_12345678_simpleblog";
   ```
4. Upload all project files (`index.php`, `create.php`, `edit.php`, `view.php`, `delete.php`, `db_connect.php`, and the `assets/` folder) into `htdocs/` using InfinityFree's **File Manager**.
5. Open **phpMyAdmin** from the InfinityFree Control Panel, select your database, and import `database.sql` (or simply visit your live site URL and `db_connect.php` will automatically create and seed the `posts` table for you).
