-- SimpleCMS Database Schema
-- Run this file: /Applications/XAMPP/bin/mysql -u root < /Applications/XAMPP/htdocs/simplecms/schema.sql

-- Create database
CREATE DATABASE IF NOT EXISTS `simple_cms` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create dedicated user
CREATE USER IF NOT EXISTS 'simplecms'@'localhost' IDENTIFIED BY 'simplecms_pass';
GRANT ALL PRIVILEGES ON `simple_cms`.* TO 'simplecms'@'localhost';
FLUSH PRIVILEGES;

USE `simple_cms`;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CATEGORIES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOG POSTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(280) NOT NULL UNIQUE,
    `content` LONGTEXT NOT NULL,
    `excerpt` TEXT NULL,
    `featured_image` VARCHAR(255) NULL,
    `category_id` INT UNSIGNED NULL,
    `author_id` INT UNSIGNED NOT NULL,
    `status` ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_category` (`category_id`),
    INDEX `idx_author` (`author_id`),
    INDEX `idx_created` (`created_at`),
    CONSTRAINT `fk_post_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_post_author` FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PORTFOLIO ITEMS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `portfolio_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(280) NOT NULL UNIQUE,
    `description` LONGTEXT NOT NULL,
    `images` JSON NULL,
    `project_url` VARCHAR(500) NULL,
    `technologies_used` VARCHAR(500) NULL,
    `category` VARCHAR(100) NULL,
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_featured` (`featured`),
    INDEX `idx_portfolio_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TAGS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- POST_TAGS JUNCTION TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `post_tags` (
    `post_id` INT UNSIGNED NOT NULL,
    `tag_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`post_id`, `tag_id`),
    CONSTRAINT `fk_pt_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LOGIN ATTEMPTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `ip_address` VARCHAR(45) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_user_time` (`ip_address`, `username`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admin user  (password: admin123)
INSERT INTO `users` (`username`, `password`, `email`) VALUES
('admin', '$2y$10$ig0C2i4D5FXslLK.SxciQ.FD3M4ULr.x5IKotWCla5BXNVjT0iemO', 'admin@simplecms.local');
-- Note: hash generated with password_hash('admin123', PASSWORD_BCRYPT)

-- Categories
INSERT INTO `categories` (`name`, `slug`) VALUES
('Technology', 'technology'),
('Design', 'design'),
('Business', 'business'),
('Lifestyle', 'lifestyle'),
('Development', 'development');

-- Tags
INSERT INTO `tags` (`name`, `slug`) VALUES
('PHP', 'php'),
('JavaScript', 'javascript'),
('CSS', 'css'),
('MySQL', 'mysql'),
('UI/UX', 'ui-ux'),
('Web Design', 'web-design'),
('Tutorial', 'tutorial'),
('Tips', 'tips');

-- Blog posts
INSERT INTO `blog_posts` (`title`, `slug`, `content`, `excerpt`, `category_id`, `author_id`, `status`, `created_at`) VALUES
(
    'Getting Started with Modern PHP Development',
    'getting-started-with-modern-php-development',
    '<p>PHP has evolved dramatically over the past decade. With the release of PHP 8.x, the language now supports features like named arguments, union types, attributes, fibers, and much more. In this post, we will explore the essential tools and practices for modern PHP development.</p>

<h2>Setting Up Your Environment</h2>
<p>The first step to modern PHP development is setting up a proper development environment. While XAMPP is great for beginners, you might also consider tools like Docker for containerized development, or Laravel Valet for macOS users.</p>

<h3>Essential Tools</h3>
<ul>
    <li><strong>Composer</strong> — The dependency manager for PHP. Almost every modern PHP project uses Composer for package management.</li>
    <li><strong>PHPStan or Psalm</strong> — Static analysis tools that catch bugs before runtime.</li>
    <li><strong>PHP CS Fixer</strong> — Automatically fixes your code to follow coding standards.</li>
</ul>

<h2>Best Practices</h2>
<p>Modern PHP development emphasizes clean code, type safety, and security. Always use prepared statements for database queries, validate all user input, and keep your dependencies updated.</p>

<p>Type declarations are now a first-class feature in PHP. Use them everywhere — function parameters, return types, and class properties. This makes your code self-documenting and catches bugs early.</p>

<h2>Conclusion</h2>
<p>PHP in 2024 is a powerful, modern language. By adopting the right tools and practices, you can build robust, maintainable applications that rival those built with any other language.</p>',
    'PHP has evolved dramatically over the past decade. Explore the essential tools and practices for modern PHP development including Composer, static analysis, and type safety.',
    1,
    1,
    'published',
    NOW() - INTERVAL 2 DAY
),
(
    'Designing Beautiful User Interfaces: A Practical Guide',
    'designing-beautiful-user-interfaces-practical-guide',
    '<p>Great user interface design is not just about making things look pretty — it is about creating intuitive, accessible, and delightful experiences for users. In this guide, we will walk through the fundamental principles of UI design.</p>

<h2>Color Theory Basics</h2>
<p>Color is one of the most powerful tools in a designer''s arsenal. A well-chosen color palette can evoke emotions, guide attention, and create visual hierarchy. Start with a primary color that represents your brand, then build complementary and accent colors around it.</p>

<h2>Typography Matters</h2>
<p>Typography is responsible for 95% of web design. Choose fonts that are readable, accessible, and appropriate for your content. Pair a distinctive heading font with a clean body font. Pay attention to line height, letter spacing, and font size scales.</p>

<h3>Key Typography Rules</h3>
<ul>
    <li>Limit yourself to 2-3 font families maximum</li>
    <li>Use a modular scale for font sizes (1.25x or 1.333x ratio)</li>
    <li>Set body text line height to 1.5-1.7 for readability</li>
    <li>Ensure sufficient contrast between text and background</li>
</ul>

<h2>Whitespace is Your Friend</h2>
<p>One of the most common mistakes in UI design is cramming too many elements together. Generous whitespace gives content room to breathe, improves readability, and creates a sense of elegance and sophistication.</p>

<h2>Responsive Design</h2>
<p>Every interface you design must work across devices. Start with mobile-first design, then progressively enhance for larger screens. Use CSS Grid and Flexbox for flexible layouts that adapt naturally.</p>',
    'Great user interface design is about creating intuitive, accessible, and delightful experiences. Learn the fundamental principles of color theory, typography, whitespace, and responsive design.',
    2,
    1,
    'published',
    NOW() - INTERVAL 1 DAY
),
(
    'Building Scalable Web Applications: Architecture Patterns',
    'building-scalable-web-applications-architecture-patterns',
    '<p>As your web application grows, the architecture decisions you made early on become increasingly important. Poorly structured code leads to bugs, slow performance, and developer frustration. Let us explore proven architecture patterns for scalable web apps.</p>

<h2>MVC Pattern</h2>
<p>The Model-View-Controller pattern is a classic for good reason. It separates data logic (Model), presentation (View), and request handling (Controller) into distinct layers. This separation makes your code easier to test, maintain, and extend.</p>

<h2>Repository Pattern</h2>
<p>The Repository pattern abstracts your data access layer, providing a clean API for your business logic to interact with. Instead of writing SQL queries directly in your controllers, you call methods like <code>$postRepository->findBySlug($slug)</code>.</p>

<h2>Service Layer</h2>
<p>Complex business logic should live in service classes, not in controllers or models. A service layer keeps your controllers thin and your business rules reusable across different entry points (web, API, CLI).</p>

<h2>Caching Strategies</h2>
<p>Caching is essential for performance at scale. Implement caching at multiple levels: database query caching, application-level caching with Redis or Memcached, and HTTP caching with proper headers.</p>

<h2>Conclusion</h2>
<p>Good architecture is an investment. It takes more effort upfront but pays dividends as your application scales. Start with simple patterns and evolve your architecture as your needs grow.</p>',
    'Poorly structured code leads to bugs and slow performance. Explore proven architecture patterns including MVC, Repository, Service Layer, and caching strategies for scalable web apps.',
    5,
    1,
    'published',
    NOW()
);

-- Post-tag associations
INSERT INTO `post_tags` (`post_id`, `tag_id`) VALUES
(1, 1), (1, 7),   -- PHP post: PHP, Tutorial
(2, 5), (2, 6), (2, 3),  -- Design post: UI/UX, Web Design, CSS
(3, 1), (3, 2), (3, 4);  -- Architecture post: PHP, JavaScript, MySQL

-- Portfolio items
INSERT INTO `portfolio_items` (`title`, `slug`, `description`, `images`, `project_url`, `technologies_used`, `category`, `featured`, `created_at`) VALUES
(
    'E-Commerce Platform Redesign',
    'e-commerce-platform-redesign',
    '<p>A complete redesign of an established e-commerce platform serving over 50,000 monthly active users. The project focused on improving the user experience, streamlining the checkout process, and implementing a modern, responsive design system.</p>

<h3>The Challenge</h3>
<p>The existing platform had a dated interface, poor mobile experience, and a checkout flow that resulted in a 73% cart abandonment rate. Our goal was to modernize the entire front-end while maintaining backward compatibility with the existing API.</p>

<h3>The Solution</h3>
<p>We implemented a component-based design system with reusable UI elements, redesigned the product browsing experience with advanced filtering, and simplified the checkout to a streamlined 3-step process. The result was a 40% reduction in cart abandonment and a 25% increase in mobile conversions.</p>',
    '["portfolio/ecommerce-1.jpg","portfolio/ecommerce-2.jpg"]',
    'https://example.com/ecommerce',
    'PHP, JavaScript, MySQL, CSS3, REST API',
    'Web Development',
    1,
    NOW() - INTERVAL 5 DAY
),
(
    'Task Management Dashboard',
    'task-management-dashboard',
    '<p>A real-time task management dashboard built for a mid-size development team of 30+ developers. The application features drag-and-drop task boards, real-time collaboration, time tracking, and comprehensive reporting.</p>

<h3>Key Features</h3>
<ul>
    <li>Kanban-style drag-and-drop boards</li>
    <li>Real-time updates across all connected clients</li>
    <li>Time tracking with detailed reports</li>
    <li>Team workload visualization</li>
    <li>Integration with GitHub and Slack</li>
</ul>

<h3>Results</h3>
<p>After deployment, the team reported a 35% improvement in task completion rates and significantly better visibility into project progress. The real-time features eliminated the need for frequent status update meetings.</p>',
    '["portfolio/taskdash-1.jpg","portfolio/taskdash-2.jpg"]',
    'https://example.com/taskdash',
    'JavaScript, Node.js, WebSocket, MongoDB, CSS3',
    'Web Application',
    1,
    NOW() - INTERVAL 3 DAY
),
(
    'Portfolio & Blog CMS',
    'portfolio-blog-cms',
    '<p>A custom-built content management system designed specifically for creative professionals to showcase their work and share their insights through a blog. The system emphasizes ease of use, beautiful presentation, and performance.</p>

<h3>Design Philosophy</h3>
<p>The CMS was designed with the principle that content should be king. The admin interface is clean and distraction-free, while the public-facing site puts the work front and center with large images, clean typography, and smooth animations.</p>

<h3>Technical Highlights</h3>
<ul>
    <li>Custom image optimization pipeline</li>
    <li>SEO-friendly URLs and meta tag management</li>
    <li>Responsive image galleries with lightbox</li>
    <li>Markdown and rich text editing support</li>
    <li>Built-in analytics dashboard</li>
</ul>',
    '["portfolio/cms-1.jpg","portfolio/cms-2.jpg"]',
    'https://example.com/cms',
    'PHP, MySQL, JavaScript, HTML5, CSS3',
    'Content Management',
    1,
    NOW() - INTERVAL 1 DAY
);
