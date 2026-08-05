(function(){if(!('serviceWorker' in navigator)||!('PushManager' in window))return;
let swRegistration=null;
navigator.serviceWorker.ready.then(function(reg){swRegistration=reg;checkSubscription();});
function checkSubscription(){swRegistration.pushManager.getSubscription().then(function(sub){const btn=document.getElementById('pushSubscribe');if(btn){if(sub){btn.style.display='none'}else if(Notification.permission==='default'){btn.style.display='block'}}});}
})();