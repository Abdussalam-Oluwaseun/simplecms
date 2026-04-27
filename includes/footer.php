<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <h4>Simple<span style="color:var(--lemon)">CMS</span></h4>
                <p style="margin-top:10px;font-size:.9rem;line-height:1.6">A modern content management system for blogs and portfolios. Built with PHP, MySQL, and a passion for clean code.</p>
            </div>
            <div class="footer-col">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/blog.php">Blog</a></li>
                    <li><a href="<?= SITE_URL ?>/portfolio.php">Portfolio</a></li>
                    <li><a href="<?= SITE_URL ?>/search.php">Search</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Admin</h4>
                <ul>
                    <li><a href="<?= SITE_URL ?>/admin/">Dashboard</a></li>
                    <li><a href="<?= SITE_URL ?>/admin/login.php">Login</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<script src="<?= SITE_URL ?>/public/js/main.js"></script>
</body>
</html>
