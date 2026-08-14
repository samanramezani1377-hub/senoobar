/**
 * Senoobar — Cart AJAX + Mobile Drawer
 * Pure JavaScript, no PHP inside JS
 */
(function () {
  'use strict';

  var nonce = (typeof senoobarData !== 'undefined' && senoobarData.nonce) ? senoobarData.nonce : '';
  var ajaxUrl = (typeof senoobarData !== 'undefined' && senoobarData.ajaxUrl) ? senoobarData.ajaxUrl : '';
(function () {
    'use strict';

    const config = window.SenoobarCart || {};

    const itemsContainer = document.getElementById(
        'senoobar-cart-items'
    );

    if (!itemsContainer) {
        return;
    }

    const messageBox = document.getElementById(
        'senoobar-cart-message'
    );

    const subtotalElement = document.getElementById(
        'senoobar-cart-subtotal'
    );

    const totalElement = document.getElementById(
        'senoobar-cart-total'
    );

    let requestInProgress = false;


    /* =====================================================
       HELPERS
    ===================================================== */

    function showMessage(message, type) {

        if (!messageBox) {
            return;
        }

        messageBox.textContent = message;

        messageBox.className =
            'senoobar-cart-message ' + type;

        clearTimeout(showMessage.timer);

        showMessage.timer = setTimeout(function () {

            messageBox.className =
                'senoobar-cart-message';

        }, 4000);
    }


    function setLoading(isLoading) {

        requestInProgress = isLoading;

        const cartItems =
            document.querySelectorAll(
                '.senoobar-cart-item'
            );

        cartItems.forEach(function (item) {

            if (isLoading) {
                item.classList.add('is-loading');
            } else {
                item.classList.remove('is-loading');
            }

        });
    }


    function formatNumber(number) {

        return new Intl.NumberFormat(
            'fa-IR'
        ).format(number);
    }


    function updateCartCounter(count) {

        document
            .querySelectorAll(
                '.senoobar-cart-count, .cart-count, .header-cart-count'
            )
            .forEach(function (element) {

                element.textContent = count;

            });
    }


    function updateTotals(data) {

        if (
            data.subtotal_html &&
            subtotalElement
        ) {

            subtotalElement.innerHTML =
                data.subtotal_html;

        }

        if (
            data.total_html &&
            totalElement
        ) {

            totalElement.innerHTML =
                data.total_html;

        }

        if (
            typeof data.count !== 'undefined'
        ) {

            updateCartCounter(data.count);

        }
    }


    /* =====================================================
       AJAX REQUEST
    ===================================================== */

    async function request(action, payload) {

        if (requestInProgress) {
            return;
        }

        setLoading(true);

        const formData = new FormData();

        formData.append(
            'action',
            action
        );

        formData.append(
            'nonce',
            config.nonce || ''
        );


        Object.keys(payload || {}).forEach(
            function (key) {

                formData.append(
                    key,
                    payload[key]
                );

            }
        );


        try {

            const response = await fetch(
                config.ajaxUrl,
                {
                    method: 'POST',

                    credentials: 'same-origin',

                    body: formData
                }
            );


            const data =
                await response.json();


            if (!data.success) {

                throw new Error(
                    data.data &&
                    data.data.message
                        ? data.data.message
                        : 'خطایی رخ داد.'
                );

            }


            return data.data;


        } catch (error) {

            showMessage(
                error.message ||
                'خطایی در بروزرسانی سبد خرید رخ داد.',
                'error'
            );

            throw error;

        } finally {

            setLoading(false);

        }
    }


    /* =====================================================
       RENDER CART
    ===================================================== */

    function renderCart(data) {

        if (!data) {
            return;
        }


        if (data.empty) {

            window.location.reload();

            return;
        }


        if (data.cart_html) {

            itemsContainer.outerHTML =
                data.cart_html;

        }


        updateTotals(data);


        /*
         * WooCommerce fragments.
         *
         * This keeps theme mini-cart/header
         * fragments compatible where available.
         */

        if (
            window.jQuery &&
            data.fragments
        ) {

            Object.keys(
                data.fragments
            ).forEach(function (selector) {

                const element =
                    document.querySelector(selector);

                if (element) {

                    element.outerHTML =
                        data.fragments[selector];

                }

            });


            jQuery(document.body).trigger(
                'wc_fragments_refreshed'
            );

        }
    }


    /* =====================================================
       UPDATE QUANTITY
    ===================================================== */

    async function updateQuantity(
        cartKey,
        quantity
    ) {

        quantity = parseInt(
            quantity,
            10
        );


        if (
            !cartKey ||
            isNaN(quantity) ||
            quantity < 1
        ) {

            return;

        }


        try {

            const data =
                await request(
                    'senoobar_cart_update_quantity',
                    {
                        cart_item_key: cartKey,
                        quantity: quantity
                    }
                );


            renderCart(data);


            showMessage(
                'سبد خرید بروزرسانی شد.',
                'success'
            );


        } catch (error) {

            // Error already shown.

        }

    }


    /* =====================================================
       REMOVE ITEM
    ===================================================== */

    async function removeItem(cartKey) {

        if (!cartKey) {
            return;
        }


        const item =
            document.querySelector(
                '.senoobar-cart-item[data-cart-key="' +
                CSS.escape(cartKey) +
                '"]'
            );


        if (item) {

            item.classList.add(
                'is-loading'
            );

        }


        try {

            const data =
                await request(
                    'senoobar_cart_remove_item',
                    {
                        cart_item_key: cartKey
                    }
                );


            if (data.empty) {

                window.location.reload();

                return;

            }


            renderCart(data);


            showMessage(
                'محصول از سبد خرید حذف شد.',
                'success'
            );


        } catch (error) {

            // Error already shown.

        }

    }


    /* =====================================================
       CLICK EVENTS
    ===================================================== */

    document.addEventListener(
        'click',
        function (event) {

            const plusButton =
                event.target.closest(
                    '.senoobar-qty-plus'
                );


            const minusButton =
                event.target.closest(
                    '.senoobar-qty-minus'
                );


            const removeButton =
                event.target.closest(
                    '.senoobar-remove-button'
                );


            if (
                plusButton ||
                minusButton
            ) {

                const button =
                    plusButton ||
                    minusButton;


                const key =
                    button.dataset.key;


                const input =
                    document.querySelector(
                        '.senoobar-qty-input[data-key="' +
                        CSS.escape(key) +
                        '"]'
                    );


                if (!input) {
                    return;
                }


                let quantity =
                    parseInt(
                        input.value,
                        10
                    ) || 1;


                if (plusButton) {

                    quantity++;

                } else {

                    quantity--;

                }


                quantity =
                    Math.max(
                        1,
                        quantity
                    );


                input.value =
                    quantity;


                updateQuantity(
                    key,
                    quantity
                );


                return;
            }


            if (removeButton) {

                event.preventDefault();


                const key =
                    removeButton.dataset.key;


                removeItem(key);

            }

        }
    );


    /* =====================================================
       INPUT EVENTS
    ===================================================== */

    document.addEventListener(
        'change',
        function (event) {

            if (
                !event.target.matches(
                    '.senoobar-qty-input'
                )
            ) {

                return;

            }


            const input =
                event.target;


            const key =
                input.dataset.key;


            let quantity =
                parseInt(
                    input.value,
                    10
                ) || 1;


            quantity =
                Math.max(
                    1,
                    quantity
                );


            input.value =
                quantity;


            updateQuantity(
                key,
                quantity
            );

        }
    );


    /* =====================================================
       COUPON TOGGLE
    ===================================================== */

    const coupon =
        document.querySelector(
            '.senoobar-coupon'
        );


    const couponToggle =
        document.querySelector(
            '.senoobar-coupon-toggle'
        );


    if (
        coupon &&
        couponToggle
    ) {

        couponToggle.addEventListener(
            'click',
            function () {

                const open =
                    coupon.classList.toggle(
                        'is-open'
                    );


                couponToggle.setAttribute(
                    'aria-expanded',
                    open
                        ? 'true'
                        : 'false'
                );

            }
        );

    }


    /* =====================================================
       APPLY COUPON
    ===================================================== */

    const couponButton =
        document.getElementById(
            'senoobar-apply-coupon'
        );


    const couponInput =
        document.getElementById(
            'senoobar-coupon-code'
        );


    if (
        couponButton &&
        couponInput
    ) {

        couponButton.addEventListener(
            'click',
            async function () {

                const code =
                    couponInput.value.trim();


                if (!code) {

                    showMessage(
                        'لطفاً کد تخفیف را وارد کنید.',
                        'error'
                    );

                    return;

                }


                try {

                    const data =
                        await request(
                            'senoobar_apply_coupon',
                            {
                                coupon_code: code
                            }
                        );


                    if (data.cart_html) {

                        itemsContainer.outerHTML =
                            data.cart_html;

                    }


                    updateTotals(data);


                    showMessage(
                        data.message ||
                        'کد تخفیف با موفقیت اعمال شد.',
                        'success'
                    );


                } catch (error) {

                    // Error already shown.

                }

            }
        );


        couponInput.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Enter'
                ) {

                    event.preventDefault();

                    couponButton.click();

                }

            }
        );

    }


})();
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
