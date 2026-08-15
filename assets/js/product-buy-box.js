(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') fn();
    else document.addEventListener('DOMContentLoaded', fn);
  }

  ready(initBuyBox);

  function initBuyBox() {
    var box = document.querySelector('.pd-buy-box');
    if (!box) return;

    var form = box.querySelector('form.cart, form.variations_form');
    var btn = box.querySelector('.single_add_to_cart_button');
    if (!form || !btn) return;

    var productId = 0;
    var pidInput = form.querySelector('input[name="add-to-cart"]');
    if (pidInput) productId = pidInput.value;
    if (!productId && form.dataset.product_id) productId = form.dataset.product_id;

    var qtyHidden = box.querySelector('#pdHiddenQty') || form.querySelector('input[name="quantity"]');
    var qty = 1;

    // ── Stepper UI ──────────────────────────────
    var stepper = document.createElement('div');
    stepper.className = 'pd-buy-stepper';
    stepper.style.display = 'none';

    var minusBtn = document.createElement('button');
    minusBtn.type = 'button'; minusBtn.className = 'pd-step-btn'; minusBtn.textContent = '−';
    var qtyShow = document.createElement('span');
    qtyShow.className = 'pd-step-qty'; qtyShow.textContent = '1';
    var plusBtn = document.createElement('button');
    plusBtn.type = 'button'; plusBtn.className = 'pd-step-btn'; plusBtn.textContent = '+';
    var removeBtn = document.createElement('button');
    removeBtn.type = 'button'; removeBtn.className = 'pd-step-remove'; removeBtn.title = 'حذف';
    removeBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg>';

    stepper.appendChild(minusBtn);
    stepper.appendChild(qtyShow);
    stepper.appendChild(plusBtn);
    stepper.appendChild(removeBtn);

    btn.parentNode.insertBefore(stepper, btn);

    function setQty(n) {
      qty = Math.max(1, n);
      qtyShow.textContent = String(qty);
      if (qtyHidden) qtyHidden.value = String(qty);
    }

    function showButton() { stepper.style.display = 'none'; btn.style.display = ''; }
    function showStepper() { btn.style.display = 'none'; stepper.style.display = 'inline-flex'; }

    // ── AJAX: use WooCommerce's own add_to_cart ajax endpoint ──
    function addToCart(quantity) {
      var body = new URLSearchParams();
      body.set('product_id', productId);
      body.set('quantity', String(quantity));

      // variable products: append selected attributes
      form.querySelectorAll('select[name^="attribute_"]').forEach(function (sel) {
        if (sel.value) body.set(sel.name, sel.value);
      });
      var variationId = form.querySelector('input.variation_id');
      if (variationId && variationId.value && variationId.value !== '0') {
        body.set('variation_id', variationId.value);
      }

      return fetch(senoobarData.ajaxUrl + '?wc-ajax=add_to_cart', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body: body.toString()
      }).then(function (r) { return r.json(); });
    }

    function refreshCartBadge() {
      if (window.jQuery && typeof jQuery(document.body).trigger === 'function') {
        jQuery(document.body).trigger('wc_fragments_refreshed');
      }
    }

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      btn.disabled = true;
      addToCart(1).then(function (data) {
        btn.disabled = false;
        if (data && data.error === undefined && data.fragments !== undefined) {
          setQty(1);
          showStepper();
          refreshCartBadge();
        } else if (data && data.error) {
          form.submit(); // fallback to normal submit
        } else {
          showStepper();
          refreshCartBadge();
        }
      }).catch(function () {
        btn.disabled = false;
        form.submit();
      });
    });

    minusBtn.addEventListener('click', function () {
      if (qty <= 1) return;
      setQty(qty - 1);
      addToCart(qty).then(refreshCartBadge);
    });

    plusBtn.addEventListener('click', function () {
      setQty(qty + 1);
      addToCart(qty).then(refreshCartBadge);
    });

    removeBtn.addEventListener('click', function () {
      // Navigate to cart where the item can be removed, or remove directly.
      // Simplest reliable behavior: go to the cart page.
      window.location.href = (senoobarData && senoobarData.cartUrl) ? senoobarData.cartUrl : '/cart/';
    });
  }
})();
