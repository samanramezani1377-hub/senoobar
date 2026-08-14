/**
 * Senoobar — Cart AJAX + Mobile Drawer
 * Pure JavaScript, no PHP inside JS
 */
(function () {
  'use strict';

  var nonce = (typeof senoobarData !== 'undefined' && senoobarData.nonce) ? senoobarData.nonce : '';
  var ajaxUrl = (typeof senoobarData !== 'undefined' && senoobarData.ajaxUrl) ? senoobarData.ajaxUrl : '';

  function updateCart(key, quantity) {
    var item = document.querySelector('.cart-item[data-key="' + key + '"]');
    if (!item) return;

    item.classList.add('cart-loading');
    var spinner = document.createElement('div');
    spinner.className = 'spinner';
    spinner.style.position = 'absolute';
    spinner.style.top = '50%';
    spinner.style.left = '50%';
    spinner.style.marginTop = '-10px';
    spinner.style.marginLeft = '-10px';
    item.style.position = 'relative';
    item.appendChild(spinner);

    var formData = new FormData();
    formData.append('action', 'senoobar_cart_update_quantity');
    formData.append('nonce', nonce);
    formData.append('cart_item_key', key);
    formData.append('quantity', quantity);

    fetch(ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      item.classList.remove('cart-loading');
      if (spinner.parentNode) spinner.parentNode.removeChild(spinner);

      if (data.success) {
        document.getElementById('cartItemsContainer').innerHTML = data.data.cart_items;
        updateTotals(data.data);
        updateDrawer(data.data);
        bindCartEvents();
      }
    })
    .catch(function () {
      item.classList.remove('cart-loading');
      if (spinner.parentNode) spinner.parentNode.removeChild(spinner);
    });
  }

  function removeCartItem(key) {
    var item = document.querySelector('.cart-item[data-key="' + key + '"]');
    if (!item) return;

    item.style.opacity = '0.5';

    var formData = new FormData();
    formData.append('action', 'senoobar_cart_remove_item');
    formData.append('nonce', nonce);
    formData.append('cart_item_key', key);

    fetch(ajaxUrl, {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
      item.style.opacity = '1';

      if (data.success) {
        if (data.data.empty) {
          location.reload();
          return;
        }
        document.getElementById('cartItemsContainer').innerHTML = data.data.cart_items;
        updateTotals(data.data);
        updateDrawer(data.data);
        bindCartEvents();
      }
    })
    .catch(function () {
      item.style.opacity = '1';
    });
  }

  function updateTotals(data) {
    var subtotal = document.getElementById('cartSubtotal');
    var total = document.getElementById('cartTotal');
    if (subtotal) subtotal.innerHTML = formatPrice(data.subtotal);
    if (total) total.innerHTML = formatPrice(data.total);
  }

  function updateDrawer(data) {
    var drawerCount = document.querySelector('.floating-cart-count');
    if (drawerCount) drawerCount.textContent = data.count;
  }

  function formatPrice(price) {
    if (typeof price === 'string' && price.indexOf('تومان') !== -1) return price;
    return Number(price).toLocaleString('fa-IR') + ' <span style="font-size:0.85rem;color:#6b7280;margin-right:4px;font-weight:400;">تومان</span>';
  }

  function bindCartEvents() {
    document.querySelectorAll('.qty-minus').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = this.parentElement.querySelector('.qty-input');
        var key = this.dataset.key;
        var val = parseInt(input.value, 10);
        if (val > 1) {
          input.value = val - 1;
          updateCart(key, val - 1);
        }
      });
    });

    document.querySelectorAll('.qty-plus').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = this.parentElement.querySelector('.qty-input');
        var key = this.dataset.key;
        var val = parseInt(input.value, 10);
        input.value = val + 1;
        updateCart(key, val + 1);
      });
    });

    document.querySelectorAll('.qty-input').forEach(function (input) {
      input.addEventListener('change', function () {
        var key = this.dataset.key;
        var val = parseInt(this.value, 10);
        if (isNaN(val) || val < 1) {
          this.value = 1;
          updateCart(key, 1);
        } else {
          updateCart(key, val);
        }
      });
    });

    document.querySelectorAll('.cart-item__remove').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var key = this.dataset.key;
        if (confirm('آیا از حذف این محصول اطمینان دارید؟')) {
          removeCartItem(key);
        }
      });
    });

    document.querySelectorAll('.cart-drawer__item-remove').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var key = this.dataset.key;
        if (confirm('آیا از حذف این محصول اطمینان دارید؟')) {
          removeCartItem(key);
        }
      });
    });

    document.querySelectorAll('#cartDrawerBody .qty-minus').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = this.parentElement.querySelector('.qty-input');
        var key = this.dataset.key;
        var val = parseInt(input.value, 10);
        if (val > 1) {
          input.value = val - 1;
          updateCart(key, val - 1);
        }
      });
    });

    document.querySelectorAll('#cartDrawerBody .qty-plus').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var input = this.parentElement.querySelector('.qty-input');
        var key = this.dataset.key;
        var val = parseInt(input.value, 10);
        input.value = val + 1;
        updateCart(key, val + 1);
      });
    });

    document.querySelectorAll('#cartDrawerBody .qty-input').forEach(function (input) {
      input.addEventListener('change', function () {
        var key = this.dataset.key;
        var val = parseInt(this.value, 10);
        if (isNaN(val) || val < 1) {
          this.value = 1;
          updateCart(key, 1);
        } else {
          updateCart(key, val);
        }
      });
    });
  }

  // Floating cart button
  var floatingBtn = document.getElementById('floatingCartBtn');
  var drawer = document.getElementById('cartDrawer');
  var drawerClose = document.getElementById('cartDrawerClose');
  var drawerBackdrop = document.getElementById('cartDrawerBackdrop');

  if (floatingBtn && drawer) {
    floatingBtn.addEventListener('click', function () {
      drawer.classList.add('active');
      document.body.style.overflow = 'hidden';
    });
  }

  function closeDrawer() {
    if (drawer) {
      drawer.classList.remove('active');
      document.body.style.overflow = '';
    }
  }

  if (drawerClose) {
    drawerClose.addEventListener('click', closeDrawer);
  }

  if (drawerBackdrop) {
    drawerBackdrop.addEventListener('click', closeDrawer);
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeDrawer();
  });

  // Coupon toggle
  var cpToggle = document.getElementById('cpToggle');
  var cpBody = document.getElementById('cpBody');
  var cpArrow = document.getElementById('cpArrow');

  if (cpToggle && cpBody) {
    if (cpBody.querySelector('.cart-coupon-success')) {
      cpBody.style.display = 'block';
      if (cpArrow) cpArrow.style.transform = 'rotate(180deg)';
    }
    cpToggle.addEventListener('click', function () {
      var open = cpBody.style.display !== 'none';
      cpBody.style.display = open ? 'none' : 'block';
      if (cpArrow) cpArrow.style.transform = open ? '' : 'rotate(180deg)';
    });
  }

  bindCartEvents();
})();
