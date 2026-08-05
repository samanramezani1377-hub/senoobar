/* Senoobar Dark Luxe — Push Notification placeholder */
(function(){
    'use strict';
    const btn = document.getElementById('js-push-subscribe');
    if(!btn) return;
    btn.style.display = 'block';
    btn.addEventListener('click',function(){
        alert('برای دریافت نوتیفیکیشن، لطفاً مرورگر خود را تأیید کنید.');
    });
})();
