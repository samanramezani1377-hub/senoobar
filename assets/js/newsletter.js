/**
 * Senoobar — Newsletter AJAX Subscription
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        initNewsletter();
    });

    function initNewsletter() {
        const forms = document.querySelectorAll('.newsletter-form');
        if (!forms.length) return;

        forms.forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                const submitBtn = form.querySelector('button[type="submit"]');
                const emailInput = form.querySelector('input[type="email"]');
                const messageBox = form.querySelector('.newsletter-message');
                const nonce = form.dataset.nonce;

                if (!emailInput || !emailInput.value.trim()) {
                    showMessage(messageBox, 'لطفاً آدرس ایمیل خود را وارد کنید.', 'error');
                    return;
                }

                const email = emailInput.value.trim();
                if (!isValidEmail(email)) {
                    showMessage(messageBox, 'فرمت ایمیل معتبر نیست.', 'error');
                    return;
                }

                // Disable during request
                if (submitBtn) submitBtn.disabled = true;
                const originalBtnText = submitBtn ? submitBtn.textContent : '';
                if (submitBtn) submitBtn.textContent = 'در حال ثبت...';

                const formData = new FormData();
                formData.append('action', 'senoobar_newsletter_subscribe');
                formData.append('nonce', nonce || '');
                formData.append('email', email);

                fetch(senoobarData.ajaxUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData,
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (data.success) {
                            showMessage(messageBox, data.data?.message || 'با موفقیت ثبت‌نام کردید.', 'success');
                            if (emailInput) emailInput.value = '';
                        } else {
                            showMessage(messageBox, data.data?.message || 'خطا در ثبت‌نام.', 'error');
                        }
                    })
                    .catch(function () {
                        showMessage(messageBox, 'خطای شبکه. لطفاً مجدداً تلاش کنید.', 'error');
                    })
                    .finally(function () {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.textContent = originalBtnText;
                        }
                    });
            });
        });
    }

    function isValidEmail(email) {
        // Basic email validation
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showMessage(element, message, type) {
        if (!element) return;
        element.textContent = message;
        element.className = 'newsletter-message ' + type;
        element.style.display = 'block';
    }
})();