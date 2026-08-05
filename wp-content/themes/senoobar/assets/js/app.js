/**
 * Senoobar App JS
 * Mobile menu, search, install banner, cart, lazy loading
 */
(function() {
  'use strict';

  // DOM Ready
  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); return; }
    document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function() {
    initMobileMenu();
    initSearchOverlay();
    initInstallBanner();
    initLazyImages();
    initBackToTop();
  });

  // Mobile Menu
  function initMobileMenu() {
    const toggle = document.getElementById('menuToggle');
    const menu = document.getElementById('mobileMenu');
    const close = document.getElementById('menuClose');
    let overlay = document.querySelector('.menu-overlay');

    if (!toggle || !menu) return;

    // Create overlay if not exists
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'menu-overlay';
      document.body.appendChild(overlay);
    }

    function openMenu() {
      menu.classList.add('active');
      overlay.classList.add('active');
      document.body.style.overflow = 'hidden';
      toggle.setAttribute('aria-expanded', 'true');
    }

    function closeMenu() {
      menu.classList.remove('active');
      overlay.classList.remove('active');
      document.body.style.overflow = '';
      toggle.setAttribute('aria-expanded', 'false');
    }

    toggle.addEventListener('click', function() {
      menu.classList.contains('active') ? closeMenu() : openMenu();
    });

    if (close) close.addEventListener('click', closeMenu);
    overlay.addEventListener('click', closeMenu);

    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape' && menu.classList.contains('active')) closeMenu();
    });

    // Swipe to close
    let touchStartX = 0;
    menu.addEventListener('touchstart', function(e) {
      touchStartX = e.touches[0].clientX;
    }, { passive: true });

    menu.addEventListener('touchmove', function(e) {
      const diff = e.touches[0].clientX - touchStartX;
      if (diff > 50) closeMenu();
    }, { passive: true });
  }

  // Search Overlay
  function initSearchOverlay() {
    const toggle = document.getElementById('searchToggle');
    const overlay = document.getElementById('searchOverlay');
    if (!toggle || !overlay) return;

    toggle.addEventListener('click', function() {
      overlay.classList.toggle('active');
      if (overlay.classList.contains('active')) {
        const input = overlay.querySelector('input[type="search"]');
        if (input) setTimeout(() => input.focus(), 100);
      }
    });

    // Close on Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') overlay.classList.remove('active');
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
      if (!overlay.contains(e.target) && e.target !== toggle) {
        overlay.classList.remove('active');
      }
    });
  }

  // Install Banner (PWA)
  function initInstallBanner() {
    const banner = document.getElementById('installBanner');
    const installBtn = document.getElementById('installApp');
    const dismissBtn = document.getElementById('installDismiss');

    if (!banner) return;

    let deferredPrompt;

    window.addEventListener('beforeinstallprompt', function(e) {
      e.preventDefault();
      deferredPrompt = e;

      // Don't show if recently dismissed
      const dismissed = localStorage.getItem('senoobar_install_dismissed');
      if (!dismissed || Date.now() - parseInt(dismissed) > 7 * 24 * 60 * 60 * 1000) {
        banner.style.display = 'block';
      }
    });

    if (installBtn) {
      installBtn.addEventListener('click', async function() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const result = await deferredPrompt.userChoice;
        console.log('[PWA] Install:', result.outcome);
        deferredPrompt = null;
        banner.style.display = 'none';
      });
    }

    if (dismissBtn) {
      dismissBtn.addEventListener('click', function() {
        banner.style.display = 'none';
        localStorage.setItem('senoobar_install_dismissed', Date.now().toString());
      });
    }

    // Hide if already installed
    if (window.matchMedia('(display-mode: standalone)').matches) {
      banner.style.display = 'none';
    }
  }

  // Native Lazy Loading + Intersection Observer fallback
  function initLazyImages() {
    if ('loading' in HTMLImageElement.prototype) return;

    const images = document.querySelectorAll('img[loading="lazy"]');
    if (!images.length) return;

    const observer = new IntersectionObserver(function(entries) {
      entries.forEach(function(entry) {
        if (entry.isIntersecting) {
          const img = entry.target;
          if (img.dataset.src) {
            img.src = img.dataset.src;
          }
          if (img.dataset.srcset) {
            img.srcset = img.dataset.srcset;
          }
          img.classList.add('loaded');
          observer.unobserve(img);
        }
      });
    }, { rootMargin: '200px 0px' });

    images.forEach(function(img) { observer.observe(img); });
  }

  // Back to Top
  function initBackToTop() {
    const btn = document.createElement('button');
    btn.className = 'back-to-top';
    btn.setAttribute('aria-label', 'بازگشت به بالا');
    btn.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>';
    btn.style.cssText = `
      position:fixed; bottom:80px; right:20px; z-index:997;
      width:44px; height:44px; border-radius:50%;
      background:var(--color-accent); color:white;
      display:flex; align-items:center; justify-content:center;
      box-shadow:0 4px 12px rgba(0,0,0,0.15);
      opacity:0; visibility:hidden; transition:all 0.3s;
      transform:translateY(10px); cursor:pointer;
    `;
    document.body.appendChild(btn);

    window.addEventListener('scroll', function() {
      if (window.scrollY > 400) {
        btn.style.opacity = '1';
        btn.style.visibility = 'visible';
        btn.style.transform = 'translateY(0)';
      } else {
        btn.style.opacity = '0';
        btn.style.visibility = 'hidden';
        btn.style.transform = 'translateY(10px)';
      }
    }, { passive: true });

    btn.addEventListener('click', function() {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  // AJAX Cart Quantity Update
  document.addEventListener('click', function(e) {
    const target = e.target.closest('.quantity-btn');
    if (!target) return;

    e.preventDefault();
    const input = target.parentElement.querySelector('.qty');
    if (!input) return;

    let val = parseInt(input.value) || 1;
    if (target.classList.contains('plus')) val++;
    else if (target.classList.contains('minus')) val = Math.max(1, val - 1);

    input.value = val;
    input.dispatchEvent(new Event('change', { bubbles: true }));
  });

  // Sticky header shadow on scroll
  window.addEventListener('scroll', function() {
    const header = document.querySelector('.site-header');
    if (!header) return;
    if (window.scrollY > 10) {
      header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.08)';
    } else {
      header.style.boxShadow = 'none';
    }
  }, { passive: true });

})();
