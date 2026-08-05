/**
 * Senoobar Dark Luxe — Frontend JS
 * Fullscreen hero slider, mobile nav, search overlay, cart count, scroll-to-top
 */
(function(){
    'use strict';
    const doc = document;

    /* ===== HERO SLIDER ===== */
    function initSlider(){
        const slides   = doc.querySelectorAll('.hero-slide');
        const dots     = doc.querySelectorAll('.slider-dot');
        const prevBtn  = doc.querySelector('.slider-arrow--prev');
        const nextBtn  = doc.querySelector('.slider-arrow--next');
        if(!slides.length) return;

        let current = 0;
        let interval;
        const total = slides.length;

        function goTo(idx){
            slides[current].classList.remove('active');
            dots[current] && dots[current].classList.remove('active');
            current = (idx + total) % total;
            slides[current].classList.add('active');
            dots[current] && dots[current].classList.add('active');
        }

        function next(){ goTo(current + 1); }
        function prev(){ goTo(current - 1); }

        function startAuto(){ interval = setInterval(next, 5000); }
        function stopAuto(){ clearInterval(interval); }

        if(prevBtn) prevBtn.addEventListener('click', function(e){ e.preventDefault(); prev(); stopAuto(); startAuto(); });
        if(nextBtn) nextBtn.addEventListener('click', function(e){ e.preventDefault(); next(); stopAuto(); startAuto(); });

        dots.forEach(function(dot,i){
            dot.addEventListener('click',function(){ goTo(i); stopAuto(); startAuto(); });
        });

        // Touch swipe
        let touchStartX = 0;
        const sliderEl = doc.querySelector('.hero-slider');
        if(sliderEl){
            sliderEl.addEventListener('touchstart',function(e){ touchStartX = e.touches[0].clientX; });
            sliderEl.addEventListener('touchend',function(e){
                const diff = touchStartX - e.changedTouches[0].clientX;
                if(Math.abs(diff) > 50){ diff > 0 ? next() : prev(); stopAuto(); startAuto(); }
            });
        }

        // Init
        slides[0].classList.add('active');
        if(dots[0]) dots[0].classList.add('active');
        startAuto();
    }

    /* ===== MOBILE DRAWER ===== */
    function initDrawer(){
        const hamburger = doc.getElementById('js-hamburger');
        const drawer    = doc.getElementById('js-mobile-drawer');
        const overlay   = doc.getElementById('js-drawer-overlay');
        const closeBtn  = doc.getElementById('js-drawer-close');

        if(!hamburger || !drawer) return;

        function open(){ drawer.classList.add('open'); overlay && overlay.classList.add('visible'); doc.body.style.overflow='hidden'; }
        function close(){ drawer.classList.remove('open'); overlay && overlay.classList.remove('visible'); doc.body.style.overflow=''; }

        hamburger.addEventListener('click', open);
        closeBtn && closeBtn.addEventListener('click', close);
        overlay && overlay.addEventListener('click', close);
    }

    /* ===== SEARCH OVERLAY ===== */
    function initSearch(){
        const toggle  = doc.getElementById('js-search-toggle');
        const overlay = doc.getElementById('js-search-overlay');
        if(!toggle || !overlay) return;
        toggle.addEventListener('click',function(){ overlay.classList.toggle('active'); });
        overlay.addEventListener('click',function(e){ if(e.target === overlay) overlay.classList.remove('active'); });
    }

    /* ===== SCROLL TO TOP ===== */
    function initScrollTop(){
        const btn = doc.getElementById('js-totop');
        if(!btn) return;
        window.addEventListener('scroll',function(){
            btn.classList.toggle('visible', window.scrollY > 400);
        });
        btn.addEventListener('click',function(){ window.scrollTo({top:0,behavior:'smooth'}); });
    }

    /* ===== HEADER SCROLL HIDE ===== */
    function initHeaderScroll(){
        const header = doc.querySelector('.site-header');
        if(!header) return;
        let lastScroll = 0;
        window.addEventListener('scroll',function(){
            const y = window.scrollY;
            if(y > 100 && y > lastScroll){ header.style.transform = 'translateY(-120px)'; }
            else{ header.style.transform = 'translateY(0)'; }
            lastScroll = y;
        });
    }

    // Init all
    doc.addEventListener('DOMContentLoaded',function(){
        initSlider();
        initDrawer();
        initSearch();
        initScrollTop();
        initHeaderScroll();
        // Update cart count if WooCommerce
        if(typeof wc_cart_fragments_params !== 'undefined'){
            doc.body.addEventListener('added_to_cart',function(){ window.location.reload(); });
        }
    });
})();
