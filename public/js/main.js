/* ═══════════════════════════════════════════════════════════════
   Velora — Main JavaScript
   Handles: navbar scroll, sidebar toggle, nav toggle,
            scroll reveal, stat count-up, lightbox,
            portfolio filter, alert auto-dismiss,
            image preview, slug generation, delete confirm
   ═══════════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. NAVBAR GLASSMORPHISM ON SCROLL ─────────────────────── */
  const navbar = document.getElementById('mainNavbar');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 48);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll(); // run once on load
  }

  /* ── 2. PUBLIC NAV TOGGLE (mobile) ────────────────────────── */
  const navToggle  = document.getElementById('navToggle');
  const navLinks   = document.getElementById('navLinks');
  if (navToggle && navLinks) {
    navToggle.addEventListener('click', () => {
      const isOpen = navLinks.classList.toggle('open');
      navToggle.setAttribute('aria-expanded', isOpen);
    });
    // Close nav when a link is clicked
    navLinks.querySelectorAll('a').forEach(link => {
      link.addEventListener('click', () => {
        navLinks.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
      });
    });
  }

  /* ── 3. ADMIN SIDEBAR TOGGLE (mobile) ─────────────────────── */
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar       = document.getElementById('adminSidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
      if (sidebar.classList.contains('open') &&
          !sidebar.contains(e.target) &&
          e.target !== sidebarToggle &&
          !sidebarToggle.contains(e.target)) {
        sidebar.classList.remove('open');
      }
    });
  }

  /* ── 4. SCROLL REVEAL ──────────────────────────────────────── */
  const revealEls = document.querySelectorAll('.reveal, .card, .stat-card, .sidebar-widget');
  if (revealEls.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry, i) => {
        if (entry.isIntersecting) {
          // Stagger cards in a grid
          const delay = entry.target.closest('.card-grid, .stats-grid')
            ? Array.from(entry.target.parentElement.children).indexOf(entry.target) * 60
            : 0;
          setTimeout(() => {
            entry.target.classList.add('visible');
            entry.target.style.opacity = '';
            entry.target.style.transform = '';
          }, delay);
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

    revealEls.forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(18px)';
      el.style.transition = 'opacity .45s ease, transform .45s cubic-bezier(.4,0,.2,1)';
      observer.observe(el);
    });
  }

  /* ── 5. STAT COUNT-UP ANIMATION ───────────────────────────── */
  const statNumbers = document.querySelectorAll('.stat-number');
  if (statNumbers.length && 'IntersectionObserver' in window) {
    const countObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el     = entry.target;
          const target = parseInt(el.textContent, 10);
          if (isNaN(target) || target === 0) return;
          const duration = 900;
          const step     = 16;
          const steps    = Math.floor(duration / step);
          let current    = 0;
          const increment = target / steps;
          const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
              el.textContent = target;
              clearInterval(timer);
            } else {
              el.textContent = Math.floor(current);
            }
          }, step);
          countObserver.unobserve(el);
        }
      });
    }, { threshold: 0.5 });

    statNumbers.forEach(el => countObserver.observe(el));
  }

  /* ── 6. AUTO-DISMISS ALERTS ────────────────────────────────── */
  document.querySelectorAll('.alert').forEach(alert => {
    // Add transition
    alert.style.transition = 'opacity .35s ease, transform .35s ease';
    setTimeout(() => {
      alert.style.opacity = '0';
      alert.style.transform = 'translateY(-8px)';
      setTimeout(() => alert.remove(), 380);
    }, 5000);
  });

  /* ── 7. DELETE CONFIRMATIONS ───────────────────────────────── */
  document.querySelectorAll('.confirm-delete').forEach(form => {
    form.addEventListener('submit', function (e) {
      if (!confirm('Are you sure you want to delete this item? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });

  /* ── 8. PORTFOLIO FILTER ───────────────────────────────────── */
  const filterBtns     = document.querySelectorAll('.filter-btn');
  const portfolioCards = document.querySelectorAll('.portfolio-item');
  filterBtns.forEach(btn => {
    btn.addEventListener('click', function () {
      filterBtns.forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      const cat = this.dataset.filter;
      portfolioCards.forEach(card => {
        const show = cat === 'all' || card.dataset.category === cat;
        card.style.display = show ? '' : 'none';
        if (show) card.style.animation = 'fadeIn .35s ease';
      });
    });
  });

  /* ── 9. LIGHTBOX ───────────────────────────────────────────── */
  const lightbox    = document.getElementById('lightbox');
  const lightboxImg = document.getElementById('lightbox-img');
  const lightboxClose = document.getElementById('lightboxClose');

  document.querySelectorAll('.gallery-grid img, .lightbox-trigger').forEach(img => {
    img.style.cursor = 'zoom-in';
    img.addEventListener('click', function () {
      if (lightbox && lightboxImg) {
        lightboxImg.src = this.src || this.dataset.src;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });
  });
  function closeLightbox() {
    if (lightbox) {
      lightbox.classList.remove('active');
      document.body.style.overflow = '';
    }
  }
  if (lightbox) {
    lightbox.addEventListener('click', e => {
      if (e.target === lightbox) closeLightbox();
    });
  }
  if (lightboxClose) {
    lightboxClose.addEventListener('click', closeLightbox);
  }
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeLightbox();
  });

  /* ── 10. IMAGE PREVIEW ON FILE INPUT ──────────────────────── */
  document.querySelectorAll('input[type="file"].img-input').forEach(input => {
    input.addEventListener('change', function () {
      const preview = document.getElementById(this.dataset.preview);
      if (preview && this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
          preview.src = e.target.result;
          preview.style.display = 'block';
        };
        reader.readAsDataURL(this.files[0]);
      }
    });
  });

  /* ── 11. SLUG AUTO-GENERATION ──────────────────────────────── */
  const titleInput = document.getElementById('title');
  const slugInput  = document.getElementById('slug');
  if (titleInput && slugInput && !slugInput.value) {
    titleInput.addEventListener('input', function () {
      slugInput.value = this.value.toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    });
  }

  /* ── 12. REMOVE IMAGE THUMBNAILS ───────────────────────────── */
  document.querySelectorAll('.remove-img').forEach(btn => {
    btn.addEventListener('click', function () {
      const thumb = this.closest('.thumb');
      if (thumb) {
        thumb.style.transition = 'opacity .2s, transform .2s';
        thumb.style.opacity = '0';
        thumb.style.transform = 'scale(.85)';
        setTimeout(() => thumb.remove(), 220);
      }
    });
  });

});

/* ── Global: fadeIn keyframe for portfolio filter ──────────── */
(function () {
  const s = document.createElement('style');
  s.textContent = '@keyframes fadeIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}';
  document.head.appendChild(s);
})();
