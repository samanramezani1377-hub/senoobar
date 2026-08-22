(function(){
  'use strict';
  function ready(fn){if(document.readyState!=='loading')fn();else document.addEventListener('DOMContentLoaded',fn);}
  
  ready(function(){
    initMobileMenu();
    initBackToTop();
    initStickyHeader();
  });

  function initMobileMenu(){
    var t=document.getElementById('menuToggle'),m=document.getElementById('mobileMenu'),c=document.getElementById('menuClose'),o=document.getElementById('menuOverlay');
    if(!t||!m||!o)return;
    function open(){m.classList.add('active');o.classList.add('active');document.body.style.overflow='hidden';}
    function close(){m.classList.remove('active');o.classList.remove('active');document.body.style.overflow='';}
    t.addEventListener('click',function(){m.classList.contains('active')?close():open();});
    if(c)c.addEventListener('click',close);
    o.addEventListener('click',close);
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
  }

  function initBackToTop(){
    var b=document.getElementById('backToTop');if(!b)return;
    window.addEventListener('scroll',function(){if(window.scrollY>500)b.classList.add('visible');else b.classList.remove('visible');},{passive:true});
    b.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
  }

  function initStickyHeader(){
    var h=document.querySelector('.site-header');if(!h)return;
    window.addEventListener('scroll',function(){if(window.scrollY>10)h.classList.add('scrolled');else h.classList.remove('scrolled');},{passive:true});
  }
})();

// Register service worker (so PWA + push work). Wrapped in a guard so it
// never throws on older browsers.
(function () {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      var d = window.senoobarData || {};
      var swUrl = d.swUrl || '/sw.js';
      // Pass the theme asset base to the worker so its precache + notification
      // icon paths are correct on any domain / theme directory name.
      if (d.themeBase) {
        swUrl += (swUrl.indexOf('?') === -1 ? '?' : '&') + 'theme=' + encodeURIComponent(d.themeBase);
      }
      navigator.serviceWorker.register(swUrl).catch(function (err) {
        // Non-fatal: the site works fine without cached assets.
        console.warn('[SW] registration failed:', err);
      });
    });
  }
})();


// Refresh cart counter badge.
// - On a normal load: refresh as soon as the DOM is ready (not on 'load'),
//   so a freshly-cached HTML page (e.g. served from LiteSpeed cache) never
//   shows a stale count, without waiting for every image/asset to finish.
// - On a back/forward-cache (bfcache) restore: 'pageshow' with
//   event.persisted fires instead (DOMContentLoaded does not fire again),
//   and for the cart page specifically we do a full reload since the
//   restored HTML can show items already removed on the server.
(function () {
  function applyCount(count) {
    var n = parseInt(count, 10);
    if (isNaN(n)) return;
    var badges = document.querySelectorAll('.cart-badge[data-cart-count], .mbn-badge[data-cart-count]');
    badges.forEach(function (el) {
      el.textContent = n;
      if (n > 0) el.classList.remove('is-hidden');
      else el.classList.add('is-hidden');
    });
  }

  function refreshCart() {
    var d = window.senoobarData || {};
    if (!d.ajaxUrl || !d.nonce) return;

    var fd = new FormData();
    fd.append('action', 'senoobar_cart_refresh');
    fd.append('nonce', d.nonce);

    fetch(d.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: fd })
      .then(function (r) { return r.json(); })
      .then(function (json) {
        if (json && json.success && typeof json.data !== 'undefined') {
          applyCount(json.data.count);
        }
      })
      .catch(function () { /* non-fatal */ });
  }

  // Refresh the badge as soon as the DOM is ready, instead of waiting for
  // 'load' (i.e. every image/asset finished). Firing on 'load' meant this
  // request always went out at the exact moment everything else finished,
  // which (a) tacked extra time onto "Fully Loaded" readings in performance
  // tools, and (b) looked like a bot pattern to some hosting-level bot
  // protections, since it was a POST with zero prior user interaction fired
  // at a very predictable, automatic instant.
  if (document.readyState !== 'loading') {
    refreshCart();
  } else {
    document.addEventListener('DOMContentLoaded', refreshCart);
  }

  // 'pageshow' with event.persisted fires specifically on a bfcache restore
  // (DOMContentLoaded does NOT fire again in that case, so this is the only
  // place a bfcache restore needs handling).
  window.addEventListener('pageshow', function (event) {
    if (!event.persisted) return;

    // A page restored from bfcache shows the HTML as it was when the user
    // left it. The cart page in particular can show a stale item list (an
    // item already removed on the server still appears). Replace the current
    // history entry with a fresh load so the cart always matches the server.
    // location.replace() avoids adding a new history entry (no back-loop) and
    // only fires on a genuine bfcache restore, not on a normal reload.
    if (isCartPage()) {
      window.location.replace(window.location.href);
      return;
    }

    // For non-cart pages, just refresh the header badge count.
    refreshCart();
  });

  // Detect whether the current page is the WooCommerce cart page.
  function isCartPage() {
    var b = document.body;
    if (!b) return false;
    // WooCommerce adds the class on the <body>; also match the theme's cart root.
    return /(^|\s)(woocommerce-cart|cart)\b/.test(b.className || '') ||
           !!document.querySelector('.senoobar-cart, [data-cart-key], .woocommerce-cart-form, form.woocommerce-cart-form');
  }
})();
