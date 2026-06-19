<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="footer-brand">
                    <div class="footer-brand-logo">
                        <svg viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="36" height="36" rx="10" fill="rgba(255,255,255,0.15)"/>
                            <path d="M9 10.5L13.8 23L18 16.8L22.2 23L27 10.5H23.4L18 20.2L12.6 10.5H9Z" fill="white"/>
                        </svg>
                    </div>
                    <span class="footer-brand-name">Velora</span>
                </div>
                <p>A modern content management platform for blogs and portfolios. Built for creators who care about design.</p>
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
                    <li><a href="<?= SITE_URL ?>/admin/login.php">Sign In</a></li>
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
