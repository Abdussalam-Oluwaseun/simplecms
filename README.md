# SimpleCMS — Blog & Portfolio System

A modern content management system built with PHP 8.x and MySQL/MariaDB.

## Features

- **Admin Panel** — Dashboard, blog post CRUD, portfolio CRUD, category/tag management
- **Blog System** — Rich text editing (TinyMCE), featured images, categories, tags, pagination
- **Portfolio Gallery** — Multiple image uploads, category filtering, featured projects
- **Search** — Full-text search across posts and portfolio items
- **Security** — CSRF protection, prepared statements, password hashing, XSS prevention
- **Responsive Design** — White & lemon theme, works on all devices

## Requirements

- XAMPP (Apache + MySQL/MariaDB)
- PHP 8.0+
- MySQL 5.7+ or MariaDB 10.4+

## Installation

1. **Copy files** to your XAMPP htdocs directory:
   ```
   /Applications/XAMPP/htdocs/simplecms/
   ```

2. **Start XAMPP** (Apache and MySQL)

3. **Import the database schema:**
   ```bash
   /Applications/XAMPP/bin/mysql -u root < /Applications/XAMPP/htdocs/simplecms/schema.sql
   ```
   This creates:
   - Database: `simple_cms`
   - User: `simplecms` / `simplecms_pass`
   - All tables with sample data

4. **Set upload permissions:**
   ```bash
   chmod -R 755 public/uploads/
   ```

5. **Access the site:**
   - Public site: http://localhost/simplecms/
   - Admin panel: http://localhost/simplecms/admin/

## Default Login

- **Username:** `admin`
- **Password:** `admin123`

## Project Structure

```
simplecms/
├── admin/
│   ├── index.php         # Dashboard
│   ├── login.php         # Login page
│   ├── logout.php        # Logout handler
│   ├── categories.php    # Category management
│   ├── tags.php          # Tag management
│   ├── posts/            # Blog post CRUD
│   ├── portfolio/        # Portfolio CRUD
│   └── includes/         # Admin header, footer, auth
├── includes/
│   ├── config.php        # DB connection & constants
│   ├── functions.php     # Utility functions
│   ├── header.php        # Public site header
│   └── footer.php        # Public site footer
├── public/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript
│   └── uploads/          # Uploaded images
├── index.php             # Homepage
├── blog.php              # Blog listing
├── post.php              # Single post
├── portfolio.php         # Portfolio gallery
├── project.php           # Single project
├── search.php            # Search page
├── schema.sql            # Database schema
└── .htaccess             # Security rules
```

## Configuration

Edit `includes/config.php` to change:
- Database credentials
- Site URL
- Upload paths
- Timezone

## Security

- All database queries use prepared statements (mysqli)
- Passwords hashed with `password_hash()` / `password_verify()`
- CSRF tokens on all forms
- XSS prevention with `htmlspecialchars()` on all output
- Upload validation (type whitelist, size limit)
- PHP execution blocked in uploads directory
- Directory listing disabled
