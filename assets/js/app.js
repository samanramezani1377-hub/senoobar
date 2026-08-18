(function(){
  'use strict';
  function ready(fn){if(document.readyState!=='loading')fn();else document.addEventListener('DOMContentLoaded',fn);}
  
  ready(function(){
    initMobileMenu();
    initBackToTop();
    initStickyHeader();
  });

  function initMobileMenu(){
    var t=document.getElementById('menuToggle'),m=document.getElementById('mobileMenu'),c=document.getElementById('menuClose'),o=document.getElementById('menuOverlay');
    if(!t||!m||!o)return;
    function open(){m.classList.add('active');o.classList.add('active');document.body.style.overflow='hidden';}
    function close(){m.classList.remove('active');o.classList.remove('active');document.body.style.overflow='';}
    t.addEventListener('click',function(){m.classList.contains('active')?close():open();});
    if(c)c.addEventListener('click',close);
    o.addEventListener('click',close);
    document.addEventListener('keydown',function(e){if(e.key==='Escape')close();});
  }

  function initBackToTop(){
    var b=document.getElementById('backToTop');if(!b)return;
    window.addEventListener('scroll',function(){if(window.scrollY>500)b.classList.add('visible');else b.classList.remove('visible');},{passive:true});
    b.addEventListener('click',function(){window.scrollTo({top:0,behavior:'smooth'});});
  }

  function initStickyHeader(){
    var h=document.querySelector('.site-header');if(!h)return;
    window.addEventListener('scroll',function(){if(window.scrollY>10)h.classList.add('scrolled');else h.classList.remove('scrolled');},{passive:true});
  }
})();

// Register service worker (so PWA + push work). Wrapped in a guard so it
// never throws on older browsers.
(function () {
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('/sw.js').catch(function (err) {
        // Non-fatal: the site works fine without cached assets.
        console.warn('[SW] registration failed:', err);
      });
    });
  }
})();
