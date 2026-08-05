/**
 * Senoobar — Frontend Application
 */
document.addEventListener('DOMContentLoaded', function () {

    // ---- DOM refs ----
    const hamburger   = document.getElementById('js-hamburger');
    const drawer      = document.getElementById('js-mobile-drawer');
    const drawerClose = document.getElementById('js-drawer-close');
    const overlay     = document.getElementById('js-drawer-overlay');
    const searchTgl   = document.getElementById('js-search-toggle');
    const searchOver  = document.getElementById('js-search-overlay');

    // ---- Mobile Drawer ----
    const openDrawer  = () => { drawer.classList.add('open'); overlay.classList.add('visible'); };
    const closeDrawer = () => { drawer.classList.remove('open'); overlay.classList.remove('visible'); };

    hamburger?.addEventListener('click', openDrawer);
    drawerClose?.addEventListener('click', closeDrawer);
    overlay?.addEventListener('click', closeDrawer);

    // Close drawer on Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeDrawer();
    });

    // ---- Search Overlay ----
    searchTgl?.addEventListener('click', function () {
        searchOver.classList.toggle('active');
    });
    searchOver?.addEventListener('click', function (e) {
        if (e.target === searchOver) searchOver.classList.remove('active');
    });

    // ---- Sticky Header Hide/Show on scroll ----
    const header   = document.querySelector('.site-header');
    let lastScroll = 0;

    window.addEventListener('scroll', function () {
        const y = window.pageYOffset;
        if (y > 120) {
            header.style.transform = y > lastScroll ? 'translateY(-100%)' : 'translateY(0)';
            lastScroll = y;
        } else {
            header.style.transform = 'translateY(0)';
        }
    }, { passive: true });

    // ---- Push Subscribe Button ----
    const pushBtn = document.getElementById('js-push-subscribe');

    if (pushBtn && 'Notification' in window) {
        if (Notification.permission === 'default') {
            pushBtn.style.display = 'block';
        }

        pushBtn.addEventListener('click', function () {
            Notification.requestPermission().then(function (perm) {
                if (perm === 'granted') {
                    pushBtn.style.display = 'none';
                }
            });
        });
    }

    // ---- WooCommerce AJAX Cart Count Update ----
    const cartCount = document.querySelector('.cart-count');

    if (cartCount) {
        document.body.addEventListener('added_to_cart', function () {
            setTimeout(function () {
                const current = parseInt(cartCount.getAttribute('data-cart-count'), 10) || 0;
                cartCount.textContent = current + 1;
                cartCount.setAttribute('data-cart-count', current + 1);
            }, 300);
        });
    }

});
