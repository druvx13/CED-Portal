// assets/app.js
document.addEventListener('DOMContentLoaded', () => {
    // Mobile menu toggle
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            mobileMenuToggle.setAttribute('aria-expanded', 
                navMenu.classList.contains('active') ? 'true' : 'false'
            );
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', (event) => {
            if (!mobileMenuToggle.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.remove('active');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
            }
        });
        
        // Close mobile menu when clicking a nav link
        const navLinks = navMenu.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                mobileMenuToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && document.querySelector(href)) {
                e.preventDefault();
                document.querySelector(href).scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Subject section accordion (lab programs list)
    document.querySelectorAll('.subject-section__header').forEach(header => {
        const toggle = () => {
            const section = header.closest('.subject-section');
            const expanded = header.getAttribute('aria-expanded') !== 'false';
            section.classList.toggle('collapsed', expanded);
            header.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        };
        header.addEventListener('click', toggle);
        header.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
        });
    });

    // Copy code button
    document.querySelectorAll('.copy-code-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const pre = btn.closest('.lab-program-code').querySelector('pre code');
            if (!pre) return;
            navigator.clipboard.writeText(pre.textContent).then(() => {
                btn.textContent = 'Copied!';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.textContent = 'Copy';
                    btn.classList.remove('copied');
                }, 2000);
            }).catch(() => {
                btn.textContent = 'Failed';
                btn.setAttribute('aria-label', 'Copy failed');
                setTimeout(() => {
                    btn.textContent = 'Copy';
                    btn.setAttribute('aria-label', 'Copy code to clipboard');
                }, 2000);
            });
        });
    });
});
