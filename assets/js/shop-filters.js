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
  const shopMain = () => $('#productsWrapper')?.closest('.shop-main');
  const ul = () => grid()?.querySelector('ul.products');
  const loader = () => $('#filterLoading');
  const trigger = () => $('#infiniteScrollTrigger');

  // The "all products" target: the shop page. If we're on a category page,
  // clicking "all" must navigate back to the shop URL (not the category URL).
  // Normalize the shop URL to a pathname (strip origin, since fetch + pushState
  // expect a path). Handles Persian slugs like /فروشگاه/.
  function toPath(u) {
    if (!u) return '/shop/';
    try {
      const url = new URL(u, location.origin);
      return url.pathname;
    } catch (_) {
      return u;
    }
  }
  const shopUrl = toPath(window.senoobarData && senoobarData.shopUrl);
  const isCategoryPage = document.body.classList.contains('tax-product_cat')
    || /\/product-category\/|\/product-tag\//.test(location.pathname);
  const isSearchPage = document.body.classList.contains('search')
    || new URLSearchParams(location.search).has('s')
    || document.body.classList.contains('search-results');

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

  function currentSearchTerm() {
    const p = new URLSearchParams(location.search);
    return p.get('s') || '';
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

  function pushUrlTo(basePath, qs) {
    const url = (basePath || location.pathname) + (qs ? '?' + qs : '');
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
        resetScroll();
        if (cat === 'all') {
          // "All products" -> go to the shop page (or keep the search term
          // on the search page) and clear category/price filters.
          if (isSearchPage) {
            const s = currentSearchTerm();
            loadTo('', s ? { s } : {});
          } else if (isCategoryPage) {
            loadTo(shopUrl, {});
          } else {
            load('');
          }
        } else {
          const extra = { product_cat: cat, min_price: '', max_price: '' };
          if (isSearchPage) {
            const s = currentSearchTerm();
            if (s) extra.s = s;
            loadTo('', extra);
          } else {
            loadTo('', extra);
          }
        }
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
  function animateViewSwitch(g) {
    // Fade out -> switch -> fade in for a smooth grid<->list transition.
    if (!g) return;
    if (g.classList.contains('view-animating')) return;
    g.classList.add('view-animating');
    // The CSS transition on .view-animating li.product fades them out; after
    // the fade completes we flip the view class and fade back in.
    setTimeout(() => {
      g.classList.remove('grid-view', 'list-view');
      g.classList.add(currentView === 'list' ? 'list-view' : 'grid-view');
      g.classList.remove('view-animating');
    }, 220);
  }

  function bindViewToggle() {
    $$('.view-btn[data-view]').forEach(btn => {
      const clone = btn.cloneNode(true);
      btn.replaceWith(clone);
      clone.addEventListener('click', () => {
        currentView = clone.dataset.view;
        $$('.view-btn').forEach(b => b.classList.remove('active'));
        clone.classList.add('active');
        const g = shopMain();
        if (g) {
          animateViewSwitch(g);
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
  // basePath: explicit path (e.g. the shop URL); params: object or query string.
  async function loadTo(basePath, params) {
    if (isLoading) return;
    let qs;
    if (typeof params === 'string') {
      qs = params;
    } else {
      const p = new URLSearchParams(location.search);
      // When we change context (e.g. category -> shop, or search page),
      // drop the previous category & price & sort filters entirely so the new
      // context shows its own clean results.
      ['product_cat', 'min_price', 'max_price', 'orderby', 'paged'].forEach(k => p.delete(k));
      for (const [k, v] of Object.entries(params || {})) {
        if (v) p.set(k, v); else p.delete(k);
      }
      qs = p.toString();
    }

    return loadInternal(basePath || location.pathname, qs, true);
  }

  async function load(qs) {
    return loadInternal(location.pathname, qs, true);
  }

  async function loadInternal(basePath, qs, replaceGrid = true) {
    if (isLoading) return;
    isLoading = true;
    showLoader();
    const g = grid();
    const sm = shopMain();
    const viewClass = sm?.classList.contains('list-view') ? 'list-view' : 'grid-view';
    try {
      const url = basePath + (qs ? '?' + qs : '');
      const res = await fetch(url, {
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

      pushUrlTo(basePath, qs);
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
