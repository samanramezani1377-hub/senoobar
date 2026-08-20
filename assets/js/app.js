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


// Refresh cart counter on back/forward navigation.
// When the browser restores a page from the back/forward cache (bfcache), PHP
// still holds the stale cart count. We re-fetch the real count from the server
// on 'pageshow' (covers both bfcache restores and regular loads) so the header
// and bottom-nav badges always reflect the live cart.
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

  // 'pageshow' fires on bfcache restore (event.persisted === true) as well as
  // normal first paint, so it covers the back-button case reliably.
  window.addEventListener('pageshow', function (event) {
    if (event.persisted) {
      refreshCart();

      // The cart PAGE itself can be restored from bfcache with a stale item
      // list (the server already removed the item, but the restored HTML still
      // shows it). Reload just the cart page so it always reflects the server.
      if (isCartPage()) {
        // Guard against an infinite reload loop: only reload once.
        if (!window.__senoobarCartReloaded) {
          window.__senoobarCartReloaded = true;
          window.location.reload();
        }
      }
    }
  });

  // Detect whether the current page is the WooCommerce cart page.
  function isCartPage() {
    var b = document.body;
    if (!b) return false;
    // WooCommerce adds the class on the <body>; also match the theme's cart root.
    return /(^|\s)(woocommerce-cart|cart)\b/.test(b.className || '') ||
           !!document.querySelector('.senoobar-cart, [data-cart-key], .woocommerce-cart-form, form.woocommerce-cart-form');
  }

  // Also refresh once on load so a freshly-cached HTML can never show a stale
  // badge even if the page was served from LiteSpeed cache.
  window.addEventListener('load', function () {
    refreshCart();
  });
})();
