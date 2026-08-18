(function () {
  'use strict';

  // Loop add-to-cart (home / shop / archive / bestsellers).
  //
  // The product buy-box already does reliable AJAX via a custom endpoint
  // (senoobar_cart_set_quantity) using plain fetch() — independent of the
  // WooCommerce "enable ajax add to cart" setting and jQuery. This file brings
  // the SAME mechanism to the loop buttons (class .add_to_cart_button) so they
  // add to cart without a full page reload and without changing any UI.
  //
  // It does NOT touch the visual stepper that wishlist.js builds — we only
  // replace the navigation/submit behavior, keeping the button markup intact.

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(function () {
    var data = window.senoobarData;
    if (!data || !data.ajaxUrl || !data.nonce) return;

    function productIdFrom(el) {
      // prefer data-product_id, then data-product_id on the button, then href ?add-to-cart=
      var id = el.getAttribute && el.getAttribute('data-product_id');
      if (id) return parseInt(id, 10) || 0;
      var href = el.getAttribute && el.getAttribute('href');
      if (href) {
        var m = href.match(/[?&]add-to-cart=(\d+)/);
        if (m) return parseInt(m[1], 10) || 0;
      }
      return 0;
    }

    function postCartAdd(pid, quantity) {
      var body = new URLSearchParams();
      body.set('action', 'senoobar_cart_set_quantity');
      body.set('nonce', data.nonce);
      body.set('product_id', String(pid || 0));
      body.set('quantity', String(quantity || 1));
      return fetch(data.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      }).then(function (r) { return r.json(); });
    }

    function applyFragments(resp) {
      var frag = resp && (resp.fragments || (resp.data && resp.data.fragments));
      if (!frag) return;
      Object.keys(frag).forEach(function (sel) {
        var el = document.querySelector(sel);
        if (el) el.outerHTML = frag[sel];
      });
    }

    function forceBadge(count) {
      document.querySelectorAll('.cart-badge[data-cart-count], .mbn-badge[data-cart-count]').forEach(function (el) {
        el.textContent = String(count);
        if (count > 0) el.classList.remove('is-hidden');
        else el.classList.add('is-hidden');
      });
    }

    // Delegate clicks on any loop add-to-cart button.
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('.add_to_cart_button');
      if (!btn) return;

      // Skip the variable "select options" button (no data-product_id / href add-to-cart).
      var pid = productIdFrom(btn);
      if (!pid) return;

      // Skip if it looks like out-of-stock / view product link.
      if (btn.classList.contains('out-of-stock')) return;

      e.preventDefault();
      e.stopPropagation();

      var qtyEl = btn.getAttribute('data-quantity');
      var qty = qtyEl ? (parseInt(qtyEl, 10) || 1) : 1;

      // Mirror wishlist.js: start fading the icon, our AJAX will complete fast.
      btn.classList.add('added');

      postCartAdd(pid, qty).then(function (resp) {
        applyFragments(resp);
        var count = resp && (typeof resp.cart_count === 'number' ? resp.cart_count : (resp.data && resp.data.cart_count));
        if (typeof count === 'number') forceBadge(count);
        // Let wishlist.js build the stepper; trigger its expectations.
        if (window.jQuery) jQuery(document.body).trigger('added_to_cart');
      }).catch(function () {
        // Fallback: allow WooCommerce's default navigation.
        btn.classList.remove('added');
        if (btn.getAttribute('href')) window.location.href = btn.getAttribute('href');
      });
    }, true); // capture phase so we beat any bubbling handler that navigates
  });
})();
