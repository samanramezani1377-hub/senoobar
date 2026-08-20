(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); }
    else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function () {
    initScrollReveal();
    initRailNav();
    initLightbox();
  });

  // ── انیمیشن ورود هنگام اسکرول (IntersectionObserver) ──
  function initScrollReveal() {
    var els = document.querySelectorAll('.showroom-animate');
    if (!('IntersectionObserver' in window) || els.length === 0) {
      els.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });
    els.forEach(function (el) { io.observe(el); });
  }

  // ── فلش‌های پیمایش گالری افقی (سازگار با RTL) ──
  function initRailNav() {
    document.querySelectorAll('.showroom-rail-wrap').forEach(function (wrap) {
      var rail = wrap.querySelector('.showroom-rail');
      var prev = wrap.querySelector('.showroom-arrow--prev');
      var next = wrap.querySelector('.showroom-arrow--next');
      if (!rail || (!prev && !next)) return;

      var isRTL = getComputedStyle(rail).direction === 'rtl';

      var step = function () {
        var panel = rail.querySelector('.showroom-panel');
        return panel ? panel.getBoundingClientRect().width + 16 : 320;
      };

      // در RTL مقدار scrollLeft منفی تا صفر حرکت می‌کند؛ برای سازگاری
      // از مقادیر مطلق scrollLeft استفاده می‌کنیم تا جهت همیشه درست بماند.
      if (next) next.addEventListener('click', function () {
        var d = isRTL ? -1 : 1;
        rail.scrollBy({ left: d * step(), behavior: 'smooth' });
      });
      if (prev) prev.addEventListener('click', function () {
        var d = isRTL ? 1 : -1;
        rail.scrollBy({ left: d * step(), behavior: 'smooth' });
      });
    });
  }

  // ── لایت‌باکس ──
  function initLightbox() {
    var lb = document.getElementById('showroomLightbox');
    if (!lb) return;

    var media = lb.querySelector('.showroom-lightbox__media');
    var title = lb.querySelector('.showroom-lightbox__title');
    var caption = lb.querySelector('.showroom-lightbox__caption');
    var price = lb.querySelector('.showroom-lightbox__price');
    var actions = lb.querySelector('.showroom-lightbox__actions');
    var close = lb.querySelector('.showroom-lightbox__close');

    function open(data) {
      media.innerHTML = '<img src="' + data.image + '" alt="' + (data.title || '') + '">';
      title.textContent = data.title || '';
      caption.textContent = data.caption || '';
      price.innerHTML = data.price || '';
      if (data.link) {
        actions.innerHTML = '<a class="btn btn--white" href="' + data.link + '">مشاهده محصول</a>';
      } else {
        actions.innerHTML = '';
      }
      lb.classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeFn() {
      lb.classList.remove('open');
      document.body.style.overflow = '';
    }

    // دکمه‌های دارای data-showroom-lightbox
    document.querySelectorAll('[data-showroom-lightbox]').forEach(function (el) {
      el.addEventListener('click', function () {
        open({
          image: el.getAttribute('data-image') || '',
          title: el.getAttribute('data-title') || '',
          caption: el.getAttribute('data-caption') || '',
          price: el.getAttribute('data-price') || '',
          link: el.getAttribute('data-link') || ''
        });
      });
    });

    if (close) close.addEventListener('click', closeFn);
    lb.addEventListener('click', function (e) {
      if (e.target === lb) closeFn();
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeFn();
    });
  }
})();
