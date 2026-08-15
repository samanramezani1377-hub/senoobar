/**
 * Senoobar — Checkout JS
 * Form validation, payment method handling, address toggle
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initCheckout();
    });

    function initCheckout() {
        const checkoutForm = document.querySelector('.woocommerce-checkout form.checkout');
        if (!checkoutForm) return;

        // Payment method selection
        initPaymentMethods();

        // Ship to different address toggle
        initShipToDifferentAddress();

        // Auth tab toggle (login/register)
        initAuthTabs();

        // Form validation enhancement
        initFormValidation();

        // Place order button loading state
        initPlaceOrderLoading();
    }

    function initPaymentMethods() {
        const methods = document.querySelectorAll('.senoobar-payment-method input[type="radio"]');
        methods.forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.senoobar-payment-method').forEach(m => m.classList.remove('selected'));
                this.closest('.senoobar-payment-method')?.classList.add('selected');
            });
        });
    }

    function initShipToDifferentAddress() {
        const checkbox = document.getElementById('ship_to_different_address');
        const fields = document.getElementById('shipping_address_fields');
        if (checkbox && fields) {
            checkbox.addEventListener('change', function() {
                fields.style.display = this.checked ? 'block' : 'none';
            });
        }
    }

    function initAuthTabs() {
        const tabs = document.querySelectorAll('.senoobar-auth-btn');
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const target = this.dataset.target;
                tabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                this.classList.add('active');
                this.setAttribute('aria-selected', 'true');

                document.querySelectorAll('.senoobar-auth-panel').forEach(p => {
                    p.hidden = true;
                });
                document.getElementById(target)?.hidden = false;
            });
        });
    }

    function initFormValidation() {
        const requiredInputs = checkoutForm.querySelectorAll('[required]');
        requiredInputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            input.addEventListener('input', function() {
                if (this.classList.contains('woocommerce-invalid')) {
                    validateField(this);
                }
            });
        });

        function validateField(field) {
            const group = field.closest('.form-row, .senoobar-form-group');
            if (!group) return;

            if (field.required && !field.value.trim()) {
                group.classList.add('has-error');
                field.classList.add('woocommerce-invalid');
            } else {
                group.classList.remove('has-error');
                field.classList.remove('woocommerce-invalid');
            }
        }
    }

    function initPlaceOrderLoading() {
        const placeOrderBtn = document.getElementById('place_order');
        if (!placeOrderBtn) return;

        checkoutForm.addEventListener('submit', function() {
            // Basic validation before showing loading
            let valid = true;
            requiredInputs = checkoutForm.querySelectorAll('[required]');
            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    valid = false;
                    const group = input.closest('.form-row, .senoobar-form-group');
                    if (group) group.classList.add('has-error');
                    input.classList.add('woocommerce-invalid');
                }
            });

            if (valid) {
                placeOrderBtn.disabled = true;
                placeOrderBtn.innerHTML = 'در حال پردازش... <span class="spinner"></span>';
            }
        });

        // Re-enable on error (woocommerce shows errors via AJAX)
        document.body.addEventListener('checkout_error', function() {
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = 'ثبت سفارش';
        });

        // Also listen for wc_checkout_error event
        if (window.jQuery) {
            jQuery(document.body).on('wc_checkout_error', function() {
                placeOrderBtn.disabled = false;
                placeOrderBtn.innerHTML = 'ثبت سفارش';
            });
        }
    }
})();