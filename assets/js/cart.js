/**
 * Senoobar — Cart AJAX + Mobile Drawer
 * Uses window.SenoobarCart config
 * Pure JavaScript, no PHP inside JS
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initCart();
    });

    function initCart() {
        const config = window.SenoobarCart || {};

        if (!config.ajaxUrl || !config.nonce) {
            console.warn('[SenoobarCart] Config missing:', config);
            return;
        }

        const itemsContainer = document.getElementById('senoobar-cart-items');
        if (!itemsContainer) {
            return;
        }

        const messageBox = document.getElementById('senoobar-cart-message');
        const subtotalElement = document.getElementById('senoobar-cart-subtotal');
        const totalElement = document.getElementById('senoobar-cart-total');
        const discountElement = document.getElementById('senoobar-cart-discount');

        let requestInProgress = false;
        let currentAbortController = null;

        /* =====================================================
           HELPERS
        ===================================================== */

        function showMessage(message, type) {
            if (!messageBox) return;

            messageBox.textContent = message;
            messageBox.className = 'senoobar-cart-message ' + type;

            clearTimeout(showMessage.timer);
            showMessage.timer = setTimeout(function () {
                messageBox.className = 'senoobar-cart-message';
            }, 4000);
        }

        function setLoading(isLoading) {
            requestInProgress = isLoading;

            const cartItems = document.querySelectorAll('.senoobar-cart-item');
            cartItems.forEach(function (item) {
                if (isLoading) {
                    item.classList.add('is-loading');
                } else {
                    item.classList.remove('is-loading');
                }
            });
        }

        function updateCartCounter(count) {
            document
                .querySelectorAll('.senoobar-cart-count, .cart-count, .header-cart-count')
                .forEach(function (element) {
                    element.textContent = count;
                });
        }

        function updateTotals(data) {
            if (data.subtotal_html && subtotalElement) {
                subtotalElement.innerHTML = data.subtotal_html;
            }
            if (data.total_html && totalElement) {
                totalElement.innerHTML = data.total_html;
            }
            if (data.discount_html && discountElement) {
                discountElement.innerHTML = data.discount_html;
            }
            if (typeof data.count !== 'undefined') {
                updateCartCounter(data.count);
            }
        }

        function renderCart(data) {
            if (!data) return;

            if (data.empty) {
                window.location.reload();
                return;
            }

            if (data.cart_html) {
                itemsContainer.outerHTML = data.cart_html;
            }

            updateTotals(data);

            // WooCommerce fragments compatibility
            if (window.jQuery && data.fragments) {
                Object.keys(data.fragments).forEach(function (selector) {
                    const element = document.querySelector(selector);
                    if (element) {
                        element.outerHTML = data.fragments[selector];
                    }
                });
                jQuery(document.body).trigger('wc_fragments_refreshed');
            }
        }

        /* =====================================================
           AJAX REQUEST
        ===================================================== */

        async function request(action, payload) {
            if (requestInProgress) return;

            // Abort any in-flight request to prevent race conditions
            if (currentAbortController) {
                currentAbortController.abort();
            }
            currentAbortController = new AbortController();

            setLoading(true);

            const formData = new FormData();
            formData.append('action', action);
            formData.append('nonce', config.nonce || '');

            Object.keys(payload || {}).forEach(function (key) {
                formData.append(key, payload[key]);
            });

            try {
                const response = await fetch(config.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData,
                    signal: currentAbortController.signal,
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(
                        (data.data && data.data.message) ? data.data.message : 'خطایی رخ داد.'
                    );
                }

                return data.data;
            } catch (error) {
                // Ignore aborted requests - they're intentional
                if (error.name === 'AbortError') {
                    return;
                }
                showMessage(
                    error.message || 'خطایی در بروزرسانی سبد خرید رخ داد.',
                    'error'
                );
                throw error;
            } finally {
                setLoading(false);
                currentAbortController = null;
            }
        }

        /* =====================================================
           UPDATE QUANTITY
        ===================================================== */

        async function updateQuantity(cartKey, quantity) {
            quantity = parseInt(quantity, 10);

            if (!cartKey || isNaN(quantity) || quantity < 1) {
                return;
            }

            try {
                const data = await request('senoobar_cart_update_quantity', {
                    cart_item_key: cartKey,
                    quantity: quantity,
                });

                renderCart(data);
                showMessage('سبد خرید بروزرسانی شد.', 'success');
            } catch (error) {
                // Error already shown.
            }
        }

        /* =====================================================
           REMOVE ITEM
        ===================================================== */

        async function removeItem(cartKey) {
            if (!cartKey) return;

            const item = document.querySelector('.senoobar-cart-item[data-cart-key="' + CSS.escape(cartKey) + '"]');
            if (item) {
                item.classList.add('is-loading');
            }

            try {
                const data = await request('senoobar_cart_remove_item', {
                    cart_item_key: cartKey,
                });

                if (data.empty) {
                    window.location.reload();
                    return;
                }

                renderCart(data);
                showMessage('محصول از سبد خرید حذف شد.', 'success');
            } catch (error) {
                // Error already shown.
            }
        }

        /* =====================================================
           APPLY COUPON
        ===================================================== */

        async function applyCoupon(code) {
            if (!code) return;

            try {
                const data = await request('senoobar_apply_coupon', {
                    coupon_code: code,
                });

                renderCart(data);
                showMessage(data.message || 'کد تخفیف با موفقیت اعمال شد.', 'success');
            } catch (error) {
                // Error already shown.
            }
        }

        /* =====================================================
           EVENT DELEGATION
        ===================================================== */

        // Quantity +/-, Remove, Input change
        document.addEventListener('click', function (event) {
            // Plus button
            const plusButton = event.target.closest('.senoobar-qty-plus');
            if (plusButton) {
                const key = plusButton.dataset.key;
                const input = document.querySelector('.senoobar-qty-input[data-key="' + CSS.escape(key) + '"]');
                if (!input) return;

                let quantity = parseInt(input.value, 10) || 1;
                quantity++;
                input.value = quantity;
                updateQuantity(key, quantity);
                return;
            }

            // Minus button
            const minusButton = event.target.closest('.senoobar-qty-minus');
            if (minusButton) {
                const key = minusButton.dataset.key;
                const input = document.querySelector('.senoobar-qty-input[data-key="' + CSS.escape(key) + '"]');
                if (!input) return;

                let quantity = parseInt(input.value, 10) || 1;
                quantity = Math.max(1, quantity - 1);
                input.value = quantity;
                updateQuantity(key, quantity);
                return;
            }

            // Remove button
            const removeButton = event.target.closest('.senoobar-remove-button');
            if (removeButton) {
                event.preventDefault();
                const key = removeButton.dataset.key;
                removeItem(key);
            }
        });

        // Quantity input change
        document.addEventListener('change', function (event) {
            if (!event.target.matches('.senoobar-qty-input')) return;

            const input = event.target;
            const key = input.dataset.key;

            let quantity = parseInt(input.value, 10) || 1;
            quantity = Math.max(1, quantity);
            input.value = quantity;
            updateQuantity(key, quantity);
        });

        /* =====================================================
           COUPON
        ===================================================== */

        const couponToggle = document.querySelector('.senoobar-coupon-toggle');
        const coupon = document.querySelector('.senoobar-coupon');

        if (couponToggle && coupon) {
            couponToggle.addEventListener('click', function () {
                const open = coupon.classList.toggle('is-open');
                couponToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        const couponButton = document.getElementById('senoobar-apply-coupon');
        const couponInput = document.getElementById('senoobar-coupon-code');

        if (couponButton && couponInput) {
            couponButton.addEventListener('click', function () {
                const code = couponInput.value.trim();
                if (!code) {
                    showMessage('لطفاً کد تخفیف را وارد کنید.', 'error');
                    return;
                }
                applyCoupon(code);
            });

            couponInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    couponButton.click();
                }
            });
        }
    }
})();