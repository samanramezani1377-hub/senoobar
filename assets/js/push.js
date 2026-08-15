(function () {
  'use strict';

  var BTN_IDS = ['js-push-subscribe', 'js-push-subscribe-bottom'];

  function $all() {
    var out = [];
    for (var i = 0; i < BTN_IDS.length; i++) {
      var el = document.getElementById(BTN_IDS[i]);
      if (el) out.push(el);
    }
    return out;
  }

  // Bottom-nav buttons keep a compact icon only; header buttons keep full text.
  function isBottom(btn) {
    return btn.id === 'js-push-subscribe-bottom';
  }

  function bellSvg(size) {
    size = size || 22;
    return '<svg width="' + size + '" height="' + size + '" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>';
  }

  function bellOffSvg(size) {
    size = size || 22;
    return '<svg width="' + size + '" height="' + size + '" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0112 21.75a8.25 8.25 0 01-6.022-7.894M15.362 5.214A48.94 48.94 0 0112 3.75c-3.866 0-7.474 1.126-10.614 3.064M15.362 5.214A8.252 8.252 0 0112 21.75c0-.464.033-.92.096-1.364M15.362 5.214A48.888 48.888 0 0112 21.75c2.768 0 5.394-.47 7.782-1.324m-3.462-6.16a9 9 0 00-9 9m9-9a9 9 0 019 9m-9-9a9 9 0 00-9 9m9-9V3.75m0 16.5v-1.5m0-1.5h1.5m-1.5 0H12"/></svg>';
  }

  function applyState(subscribed) {
    var btns = $all();
    for (var i = 0; i < btns.length; i++) {
      var btn = btns[i];
      if (isBottom(btn)) {
        // Compact bottom-nav: icon only + active tint.
        var icon = btn.querySelector('.mbn-icon');
        var label = btn.querySelector('.mbn-label');
        if (icon) icon.innerHTML = subscribed ? bellOffSvg(22) : bellSvg(22);
        if (label) label.textContent = subscribed ? 'نوتیفیکیشن' : 'نوتیفیکیشن';
        btn.classList.toggle('is-active', subscribed);
      } else {
        // Header button: full icon + text.
        btn.innerHTML = (subscribed ? bellOffSvg(18) : bellSvg(18)) +
          '<span class="action-label">' +
          (subscribed
            ? (senoobarPush && senoobarPush.subscribedText ? senoobarPush.subscribedText : 'لغو نوتیفیکیشن')
            : (senoobarPush && senoobarPush.btnText ? senoobarPush.btnText : 'دریافت نوتیفیکیشن')) +
          '</span>';
        btn.classList.toggle('push-btn--active', subscribed);
      }
      btn.onclick = function (e) {
        e.preventDefault();
        if (subscribed) { unsubscribe(); } else { handleClick(); }
      };
    }
  }

  function init() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      console.warn('Push notifications not supported');
      return;
    }
    var btns = $all();
    if (!btns.length) return;
    for (var i = 0; i < btns.length; i++) btns[i].style.display = 'flex';
    updateButtonState();
  }

  async function handleClick() {
    try {
      var permission = await Notification.requestPermission();
      if (permission === 'granted') {
        await subscribe();
      } else {
        alert('دسترسی نوتیفیکیشن رد شد. لطفاً از تنظیمات مرورگر اجازه دهید.');
      }
    } catch (err) {
      console.error('Push error:', err);
      alert('خطا در فعال‌سازی نوتیفیکیشن. لطفاً دوباره تلاش کنید.');
    }
  }

  async function subscribe() {
    var publicKey = (window.senoobarPush && senoobarPush.publicKey) ? senoobarPush.publicKey : '';
    if (!publicKey) {
      alert('کلید عمومی VAPID تنظیم نشده است.');
      return;
    }

    try {
      var registration = await navigator.serviceWorker.ready;
      var subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(publicKey)
      });

      var endpoint = subscription.endpoint;
      var p256dh = arrayBufferToBase64(subscription.getKey('p256dh'));
      var auth = arrayBufferToBase64(subscription.getKey('auth'));

      var res = await fetch(senoobarPush.ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=senoobar_push_subscribe&nonce=' + encodeURIComponent(senoobarPush.nonce) + '&endpoint=' + encodeURIComponent(endpoint) + '&p256dh=' + encodeURIComponent(p256dh) + '&auth=' + encodeURIComponent(auth)
      });

      var data = await res.json();
      if (data.success) {
        updateButtonState();
        alert('نوتیفیکیشن با موفقیت فعال شد!');
      } else {
        throw new Error(data.data && data.data.message ? data.data.message : 'Subscribe failed');
      }
    } catch (err) {
      console.error('Subscribe error:', err);
      alert('خطا در عضویت. لطفاً دوباره تلاش کنید.');
    }
  }

  async function unsubscribe() {
    try {
      var registration = await navigator.serviceWorker.ready;
      var subscription = await registration.pushManager.getSubscription();
      if (!subscription) return;

      var endpoint = subscription.endpoint;
      await subscription.unsubscribe();

      var res = await fetch(senoobarPush.ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=senoobar_push_unsubscribe&nonce=' + encodeURIComponent(senoobarPush.nonce) + '&endpoint=' + encodeURIComponent(endpoint)
      });

      var data = await res.json();
      if (data.success) {
        updateButtonState();
        alert('نوتیفیکیشن غیرفعال شد.');
      }
    } catch (err) {
      console.error('Unsubscribe error:', err);
    }
  }

  async function updateButtonState() {
    try {
      var registration = await navigator.serviceWorker.ready;
      var subscription = await registration.pushManager.getSubscription();
      applyState(!!subscription);
    } catch (err) {
      console.error('State check error:', err);
    }
  }

  function urlBase64ToUint8Array(base64String) {
    var padding = '='.repeat((4 - base64String.length % 4) % 4);
    var base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    var raw = atob(base64);
    var output = new Uint8Array(raw.length);
    for (var i = 0; i < raw.length; ++i) {
      output[i] = raw.charCodeAt(i);
    }
    return output;
  }

  function arrayBufferToBase64(buffer) {
    var bytes = new Uint8Array(buffer);
    var binary = '';
    for (var i = 0; i < bytes.byteLength; i++) {
      binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
