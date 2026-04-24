document.addEventListener('DOMContentLoaded', function () {
    var csrf = { token_name: '_csrf_token', token: '' };

    fetch('csrf_token.php', { credentials: 'same-origin' })
        .then(function (response) { return response.json(); })
        .then(function (payload) {
            if (payload && payload.token_name && payload.token) {
                csrf = payload;
            }
        })
        .catch(function () {
            // Keep default values; backend will still reject invalid submissions.
        });

    /* ── Smooth scrolling ──────────────────────────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var href = this.getAttribute('href');
            if (href === '#') return;
            var target = document.querySelector(href);
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
            // Close mobile nav if open
            navLinks.classList.remove('open');
            hamburger.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        });
    });

    /* ── Sticky header style on scroll ────────────────────── */
    var header = document.getElementById('site-header');
    window.addEventListener('scroll', function () {
        header.classList.toggle('scrolled', window.scrollY > 40);
    });

    /* ── Mobile hamburger ──────────────────────────────────── */
    var hamburger = document.getElementById('hamburger');
    var navLinks  = document.getElementById('nav-links');

    hamburger.addEventListener('click', function () {
        var isOpen = navLinks.classList.toggle('open');
        hamburger.classList.toggle('open', isOpen);
        hamburger.setAttribute('aria-expanded', String(isOpen));
    });

    /* ── Contact pop-up ────────────────────────────────────── */
    var contactPopup        = document.getElementById('contact-popup');
    var contactPopupTrigger = document.getElementById('contact-popup-trigger');
    var closePopup          = document.getElementById('close-popup');

    contactPopupTrigger.addEventListener('click', function (e) {
        e.preventDefault();
        contactPopup.classList.add('active');
        closePopup.focus();
    });

    closePopup.addEventListener('click', function () {
        contactPopup.classList.remove('active');
    });

    window.addEventListener('click', function (e) {
        if (e.target === contactPopup) {
            contactPopup.classList.remove('active');
        }
    });

    window.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') contactPopup.classList.remove('active');
    });

    /* ── Contact form submit ───────────────────────────────── */
    document.getElementById('contact-form').addEventListener('submit', function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.set(csrf.token_name, csrf.token);

        fetch('contact_process.php', { method: 'POST', body: formData })
            .then(function (response) {
                if (response.ok) {
                    showNotification('Message sent successfully! We\'ll be in touch soon.');
                    contactPopup.classList.remove('active');
                } else {
                    throw new Error('Network response was not ok.');
                }
            })
            .catch(function () {
                showNotification('Sorry, something went wrong. Please try again.');
            });
    });

    /* ── Animated stat counters ────────────────────────────── */
    var statNumbers = document.querySelectorAll('.stat-number[data-target]');

    function animateCounter(el) {
        var target   = parseInt(el.getAttribute('data-target'), 10);
        var duration = 1800;
        var start    = null;

        function step(timestamp) {
            if (!start) start = timestamp;
            var progress = Math.min((timestamp - start) / duration, 1);
            var ease     = 1 - Math.pow(1 - progress, 3); // ease-out cubic
            el.textContent = Math.floor(ease * target).toLocaleString();
            if (progress < 1) requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.4 });

        statNumbers.forEach(function (el) { observer.observe(el); });
    } else {
        statNumbers.forEach(function (el) {
            el.textContent = parseInt(el.getAttribute('data-target'), 10).toLocaleString();
        });
    }

    /* ── Helpers ───────────────────────────────────────────── */
    function showNotification(message) {
        var notification = document.createElement('div');
        notification.className = 'notification';
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(function () {
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.5s ease';
            setTimeout(function () {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 500);
        }, 3000);
    }
});

