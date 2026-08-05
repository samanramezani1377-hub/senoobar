/**
 * Senoobar Push Notification System
 * Firebase Cloud Messaging + Browser Push API
 */
(function() {
  'use strict';

  const PUSH_CONFIG = {
    // Firebase config - replace with actual values
    apiKey: 'YOUR_FIREBASE_API_KEY',
    authDomain: 'YOUR_PROJECT.firebaseapp.com',
    projectId: 'YOUR_PROJECT_ID',
    storageBucket: 'YOUR_PROJECT.appspot.com',
    messagingSenderId: 'YOUR_SENDER_ID',
    appId: 'YOUR_APP_ID',
    vapidKey: 'YOUR_VAPID_KEY',
  };

  let pushSupported = false;
  let pushSubscribed = false;

  function init() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
      console.log('[Push] Not supported');
      return;
    }
    pushSupported = true;
    checkSubscription();
    showSubscribeButton();
  }

  function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
  }

  function checkSubscription() {
    navigator.serviceWorker.ready.then(registration => {
      return registration.pushManager.getSubscription();
    }).then(subscription => {
      pushSubscribed = !(subscription === null);
      updateButtonState();
    });
  }

  function showSubscribeButton() {
    const btn = document.getElementById('pushSubscribe');
    if (!btn) return;

    // Don't show if already subscribed
    if (pushSubscribed) return;

    // Show after 15 seconds on site
    setTimeout(() => {
      if (!pushSubscribed && !localStorage.getItem('senoobar_push_dismissed')) {
        btn.style.display = 'block';
      }
    }, 15000);
  }

  function updateButtonState() {
    const btn = document.getElementById('pushSubscribe');
    if (!btn) return;
    btn.style.display = pushSubscribed ? 'none' : 'block';
  }

  async function subscribe() {
    if (!pushSupported) {
      alert('مرورگر شما از نوتیفیکیشن پشتیبانی نمی‌کند.');
      return;
    }

    try {
      const permission = await Notification.requestPermission();
      if (permission !== 'granted') {
        console.log('[Push] Permission denied');
        return;
      }

      const registration = await navigator.serviceWorker.ready;
      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(PUSH_CONFIG.vapidKey),
      });

      // Send subscription to server
      await sendSubscriptionToServer(subscription);

      pushSubscribed = true;
      updateButtonState();
      console.log('[Push] Subscribed successfully');
    } catch (err) {
      console.error('[Push] Subscription failed:', err);
    }
  }

  async function unsubscribe() {
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();
    if (subscription) {
      await subscription.unsubscribe();
      await sendUnsubscriptionToServer(subscription);
      pushSubscribed = false;
      updateButtonState();
    }
  }

  async function sendSubscriptionToServer(subscription) {
    const endpoint = subscription.endpoint;
    const key = subscription.getKey('p256dh');
    const auth = subscription.getKey('auth');

    try {
      await fetch(senoobarData.ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'senoobar_push_subscribe',
          nonce: senoobarData.nonce,
          endpoint: endpoint,
          p256dh: key ? btoa(String.fromCharCode(...new Uint8Array(key))) : '',
          auth: auth ? btoa(String.fromCharCode(...new Uint8Array(auth))) : '',
        }),
      });
    } catch (err) {
      console.error('[Push] Server subscription failed:', err);
    }
  }

  async function sendUnsubscriptionToServer(subscription) {
    try {
      await fetch(senoobarData.ajaxUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'senoobar_push_unsubscribe',
          nonce: senoobarData.nonce,
          endpoint: subscription.endpoint,
        }),
      });
    } catch (err) {
      console.error('[Push] Server unsubscription failed:', err);
    }
  }

  // Event listeners
  document.addEventListener('DOMContentLoaded', init);

  document.addEventListener('click', function(e) {
    if (e.target.closest('#pushSubscribe')) {
      subscribe();
    }
  });

})();
