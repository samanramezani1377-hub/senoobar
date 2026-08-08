/**
 * Senoobar — Shop AJAX Filters + Infinite Scroll v3
 * Clean rewrite — no layout interference
 */
(function () {
  'use strict';

  const $ = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => [...(ctx || document).querySelectorAll(sel)];

  // ─── State ──────────────────────────────────────
  let currentView = 'grid';
  let currentPage = 1;
  let totalPages = 1;
  let isLoading = false;
  let observer = null;

  const grid = () => $('#productsWrapper');
  const ul = () => grid()?.querySelector('ul.products');
  const loader = () => $('#filterLoading');
  const trigger = () => $('#infiniteScrollTrigger');

  // ─── Helpers ────────────────────────────────────
  function showLoader() {
    const el = loader();
    if (el) { el.style.display = 'flex'; }
    const g = grid();
    if (g) g.style.opacity = '0.5';
  }
  function hideLoader() {
    const el = loader();
    if (el) { el.style.display = 'none'; }
    const g = grid();
    if (g) g.style.opacity = '1';
  }

  function readPagination() {
    const pag = $('#shopPagination');
    if (!pag) { totalPages = 1; currentPage = 1; return; }
    const nums = $$('a.page-numbers', pag)
      .map(a => parseInt(a.textContent, 10))
      .filter(n => !isNaN(n));
    totalPages = nums.length > 0 ? Math.max(...nums) : 1;
    const cur = $('span.page-numbers.current', pag);
    currentPage = cur ? (parseInt(cur.textContent, 10) || 1) : 1;
  }

  function buildQuery(extra = {}) {
    const p = new URLSearchParams(location.search);
    for (const [k, v] of Object.entries(extra)) {
      if (v) p.set(k, v); else p.delete(k);
    }
    return p.toString();
  }

  function pushUrl(qs) {
    const url = location.pathname + (qs ? '?' + qs : '');
    history.pushState({}, '', url);
  }

  // ─── Category links ────────────────────────────
  function bindCategories() {
    $$('.category-filter-item[data-cat]').forEach(link => {
      const clone = link.cloneNode(true);
      link.replaceWith(clone);
      clone.addEventListener('click', e => {
        e.preventDefault();
        $$('.category-filter-item').forEach(l => l.classList.remove('active'));
        clone.classList.add('active');
        closeMobile();
        const cat = clone.dataset.cat;
        const qs = cat === 'all' ? '' : buildQuery({ product_cat: cat, min_price: '', max_price: '' });
        resetScroll();
        load(qs);
      });
    });
  }

  // ─── Sort ──────────────────────────────────────
  function bindSort() {
    const sel = $('#sortBy');
    if (!sel) return;
    const clone = sel.cloneNode(true);
    sel.replaceWith(clone);
    clone.addEventListener('change', () => {
      resetScroll();
      load(buildQuery({ orderby: clone.value }));
    });
  }

  // ─── Price ──────────────────────────────────────
  function bindPrice() {
    const btn = $('#applyPriceFilter');
    if (!btn) return;
    const clone = btn.cloneNode(true);
    btn.replaceWith(clone);
    clone.addEventListener('click', () => {
      closeMobile();
      const min = $('#minPrice')?.value || '';
      const max = $('#maxPrice')?.value || '';
      resetScroll();
      load(buildQuery({ min_price: min, max_price: max }));
    });
    ['minPrice', 'maxPrice'].forEach(id => {
      const inp = $('#' + id);
      if (inp) {
        const c = inp.cloneNode(true);
        inp.replaceWith(c);
        c.addEventListener('keydown', e => { if (e.key === 'Enter') clone.click(); });
      }
    });
  }

  // ─── View toggle ───────────────────────────────
  function bindViewToggle() {
    $$('.view-btn[data-view]').forEach(btn => {
      const clone = btn.cloneNode(true);
      btn.replaceWith(clone);
      clone.addEventListener('click', () => {
        currentView = clone.dataset.view;
        $$('.view-btn').forEach(b => b.classList.remove('active'));
        clone.classList.add('active');
        const g = grid();
        if (g) {
          g.classList.remove('grid-view', 'list-view');
          g.classList.add(currentView === 'list' ? 'list-view' : 'grid-view');
        }
        try { localStorage.setItem('senoobar_shop_view', currentView); } catch (_) {}
      });
    });
    try {
      if (localStorage.getItem('senoobar_shop_view') === 'list') {
        $('.view-btn[data-view="list"]')?.click();
      }
    } catch (_) {}
  }

  // ─── Mobile filter ─────────────────────────────
  function bindMobile() {
    const toggle = $('#filterToggle');
    const close = $('#filterClose');
    const sidebar = $('#shopFilters');
    const overlay = $('#filterOverlay');
    if (!toggle || !sidebar) return;
    const t = toggle.cloneNode(true);
    toggle.replaceWith(t);
    t.addEventListener('click', () => {
      sidebar.classList.add('open');
      overlay?.classList.add('open');
      document.body.style.overflow = 'hidden';
    });
    if (close) {
      const c = close.cloneNode(true);
      close.replaceWith(c);
      c.addEventListener('click', closeMobile);
    }
    if (overlay) {
      const o = overlay.cloneNode(true);
      overlay.replaceWith(o);
      o.addEventListener('click', closeMobile);
    }
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMobile(); });
  }

  function closeMobile() {
    $('#shopFilters')?.classList.remove('open');
    $('#filterOverlay')?.classList.remove('open');
    document.body.style.overflow = '';
  }

  // ─── Reset ─────────────────────────────────────
  function bindReset() {
    const btn = $('#resetFilters');
    if (!btn) return;
    const clone = btn.cloneNode(true);
    btn.replaceWith(clone);
    clone.addEventListener('click', () => {
      ['minPrice', 'maxPrice'].forEach(id => { const el = $('#' + id); if (el) el.value = ''; });
      $$('.category-filter-item').forEach(l => l.classList.remove('active'));
      $('.category-filter-item[data-cat="all"]')?.classList.add('active');
      const s = $('#sortBy'); if (s) s.value = 'menu_order';
      resetScroll();
      load('');
    });
  }

  // ─── Reset scroll state ────────────────────────
  function resetScroll() {
    currentPage = 1;
    totalPages = 1;
    $('#infiniteEndMessage')?.remove();
    $('#infiniteLoader')?.remove();
    $('#infiniteScrollTrigger')?.remove();
    if (observer) { observer.disconnect(); observer = null; }
  }

  // ─── AJAX: full replace (filter/sort) ──────────
  async function load(qs) {
    if (isLoading) return;
    isLoading = true;
    showLoader();
    const g = grid();
    // Preserve view class
    const viewClass = g?.classList.contains('list-view') ? 'list-view' : 'grid-view';
    try {
      const res = await fetch(location.pathname + (qs ? '?' + qs : ''), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) throw new Error('Fetch failed');
      const html = await res.text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const newGrid = $('#productsWrapper', doc);
      const newPag = $('#shopPagination', doc);
      const newCnt = $('#resultsCount', doc);

      if (newGrid && g) {
        // Replace ul.products inside products-wrapper
        const newUl = newGrid.querySelector('ul.products');
        const oldUl = g.querySelector('ul.products');
        if (newUl && oldUl) {
          oldUl.replaceWith(newUl);
        } else {
          g.innerHTML = newGrid.innerHTML;
        }
      }
      if (newPag) {
        const pagEl = $('#shopPagination');
        if (pagEl) pagEl.innerHTML = newPag.innerHTML;
      }
      if (newCnt) {
        const cntEl = $('#resultsCount');
        if (cntEl) cntEl.textContent = newCnt.textContent;
      }

      pushUrl(qs);
      readPagination();
      bindAll();
    } catch (e) {
      console.error('Shop filter error:', e);
    } finally {
      hideLoader();
      isLoading = false;
    }
  }

  // ─── Infinite scroll (append) ──────────────────
  async function loadMore() {
    if (isLoading || currentPage >= totalPages) return;
    isLoading = true;
    showInfiniteLoader();
    try {
      const next = currentPage + 1;
      const qs = buildQuery({ paged: next });
      const res = await fetch(location.pathname + '?' + qs, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) throw new Error('Fetch failed');
      const html = await res.text();
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const newGrid = $('#productsWrapper', doc);
      const newPag = $('#shopPagination', doc);
      const productUl = ul();

      if (newGrid && productUl) {
        const items = $$('li.product', newGrid.querySelector('ul.products'));
        items.forEach((item, i) => {
          const clone = item.cloneNode(true);
          clone.style.opacity = '0';
          clone.style.transform = 'translateY(20px)';
          productUl.appendChild(clone);
          requestAnimationFrame(() => {
            clone.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            clone.style.opacity = '1';
            clone.style.transform = 'translateY(0)';
          });
        });
      }
      if (newPag) {
        const pagEl = $('#shopPagination');
        if (pagEl) pagEl.innerHTML = newPag.innerHTML;
      }
      currentPage = next;
      readPagination();
      initObserver();

      if (currentPage >= totalPages) showEndMessage();
    } catch (e) {
      console.error('Infinite scroll error:', e);
    } finally {
      hideInfiniteLoader();
      isLoading = false;
    }
  }

  // ─── Observer ──────────────────────────────────
  function initObserver() {
    if (observer) { observer.disconnect(); observer = null; }
    let t = trigger();
    if (!t) {
      t = document.createElement('div');
      t.id = 'infiniteScrollTrigger';
      t.className = 'infinite-scroll-trigger';
      const g = grid();
      g?.insertAdjacentElement('afterend', t);
    }
    // Only observe if more pages
    if (currentPage >= totalPages) { t.style.display = 'none'; return; }
    t.style.display = '';

    observer = new IntersectionObserver(entries => {
      if (entries[0].isIntersecting && !isLoading && currentPage < totalPages) {
        loadMore();
      }
    }, { rootMargin: '300px 0px', threshold: 0 });
    observer.observe(t);
  }

  function showInfiniteLoader() {
    let el = $('#infiniteLoader');
    if (!el) {
      el = document.createElement('div');
      el.id = 'infiniteLoader';
      el.className = 'infinite-loader';
      el.innerHTML = '<div class="spinner"></div><span>در حال بارگذاری محصولات بیشتر...</span>';
      grid()?.insertAdjacentElement('afterend', el);
    }
    el.style.display = 'flex';
  }

  function hideInfiniteLoader() {
    const el = $('#infiniteLoader');
    if (el) el.style.display = 'none';
  }

  function showEndMessage() {
    let el = $('#infiniteEndMessage');
    if (!el) {
      el = document.createElement('div');
      el.id = 'infiniteEndMessage';
      el.className = 'infinite-end-message';
      el.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg><span>همه محصولات نمایش داده شد</span>';
      grid()?.insertAdjacentElement('afterend', el);
    }
    el.style.display = 'flex';
    const t = trigger();
    if (t) t.style.display = 'none';
  }

  // ─── Bind all interactive elements ─────────────
  function bindAll() {
    bindCategories();
    bindSort();
    bindPrice();
    bindViewToggle();
    bindMobile();
    bindReset();
    initObserver();
  }

  // ─── Init ──────────────────────────────────────
  function init() {
    if (!grid()) return;
    readPagination();
    bindAll();
    if (currentPage >= totalPages) showEndMessage();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
