/**
 * Senoobar — Push Notification Logic
 */
(function () {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    let swReg = null;

    navigator.serviceWorker.ready.then(function (registration) {
        swReg = registration;
        checkSubscription();
    });

    function checkSubscription() {
        swReg.pushManager.getSubscription().then(function (subscription) {
            const btn = document.getElementById('js-push-subscribe');
            if (!btn) return;

            if (subscription) {
                // Already subscribed
                btn.style.display = 'none';
            } else if (Notification.permission === 'granted') {
                // Permission granted but no subscription — subscribe silently
                subscribeUser();
            } else if (Notification.permission === 'default') {
                btn.style.display = 'block';
            }
        });
    }

    function subscribeUser() {
        if (!swReg) return;

        swReg.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(
                // VAPID public key — replace with your own in production
                'BJPWxrSNmVepKfFhv0OqpnGPNpCXtJqgfE5NykGnm_rtdVJYU0VGZRfUZLnnMn5R4RWxZLbvnHpZ3k-nOZYhP3Y'
            ),
        }).then(function (subscription) {
            const rawKey  = subscription.getKey('p256dh');
            const rawAuth = subscription.getKey('auth');

            // Send to WP backend
            fetch(Senoobar.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action:   'senoobar_push_subscribe',
                    nonce:    Senoobar.nonce,
                    endpoint: subscription.endpoint,
                    p256dh:   rawKey  ? btoa(String.fromCharCode(...new Uint8Array(rawKey)))  : '',
                    auth:     rawAuth ? btoa(String.fromCharCode(...new Uint8Array(rawAuth))) : '',
                }),
            });

            const btn = document.getElementById('js-push-subscribe');
            if (btn) btn.style.display = 'none';
        }).catch(function (err) {
            console.warn('Push subscribe failed:', err);
        });
    }

    // Listen for subscribe request from button
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('js-push-subscribe');
        if (btn) {
            btn.addEventListener('click', function () {
                subscribeUser();
            });
        }
    });

    function urlBase64ToUint8Array(base64) {
        var padding = '='.repeat((4 - (base64.length % 4)) % 4);
        var base64safe = (base64 + padding).replace(/-/g, '+').replace(/_/g, '/');
        var raw  = window.atob(base64safe);
        var arr  = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
        return arr;
    }
})();
