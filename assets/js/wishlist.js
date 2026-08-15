/**
 * Senoobar — Wishlist page + product-card heart button.
 *
 * Storage:
 *  - Logged-in -> server (user meta), synced via AJAX.
 *  - Guest     -> localStorage under 'senoobar_wishlist'.
 */
(function () {
  'use strict';

  const LS_KEY = 'senoobar_wishlist';
  const $ = (s, c) => (c || document).querySelector(s);
  const $$ = (s, c) => [...(c || document).querySelectorAll(s)];

  const ajaxUrl = (window.senoobarData && senoobarData.ajaxUrl) || '/wp-admin/admin-ajax.php';

  // ── Local list (guest) ────────────────────────────────
  function getLocal() {
    try {
      const v = JSON.parse(localStorage.getItem(LS_KEY) || '[]');
      return Array.isArray(v) ? v.map(Number) : [];
    } catch (_) { return []; }
  }
  function setLocal(ids) {
    try { localStorage.setItem(LS_KEY, JSON.stringify(ids.map(Number))); } catch (_) {}
  }

  function isLoggedIn() {
    return !!(window.SENOOBAR_WISHLIST && window.SENOOBAR_WISHLIST.loggedIn)
      || document.body.classList.contains('logged-in');
  }

  // Server-exposed wishlist ids for logged-in users (set by page-wishlist.php)
  function getServerIds() {
    return (window.SENOOBAR_WISHLIST && Array.isArray(window.SENOOBAR_WISHLIST.ids))
      ? window.SENOOBAR_WISHLIST.ids.map(Number)
      : null;
  }

  // ── Network ─────────────────────────────────────────
  async function post(action, data) {
    const body = new FormData();
    for (const k in data) body.append(k, data[k]);
    body.append('action', action);
    const res = await fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body });
    return res.json();
  }

  // ── Toggle a product in the wishlist (product page heart) ──
  async function toggle(productId) {
    if (isLoggedIn()) {
      const r = await post('senoobar_wishlist_toggle', { product_id: productId });
      if (r && r.success) return r.data.in_wishlist;
      return false;
    } else {
      let ids = getLocal();
      let added;
      if (ids.includes(productId)) {
        ids = ids.filter((x) => x !== productId);
        added = false;
      } else {
        ids.push(productId);
        added = true;
      }
      setLocal(ids);
      return added;
    }
  }

  // ── Read current wishlist ids (merged) ─────────────
  async function getIds() {
    if (isLoggedIn()) {
      const server = getServerIds();
      if (server !== null) return server;
      const r = await post('senoobar_wishlist_get', {});
      return (r && r.success && r.data.ids) || [];
    }
    return getLocal();
  }

  // ── Format price to Persian ────────────────────────
  function fmt(n) {
    const num = Number(n) || 0;
    return num.toLocaleString('fa-IR');
  }
  function escapeHtml(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
  }

  // ── Render cards into the grid ─────────────────────
  function render(items, view) {
    const grid = $('#snbWishGrid');
    if (!grid) return;

    const empty = $('#snbWishEmpty');
    const cta = $('#snbWishCta');
    const countEl = $('#snbWishCount');

    if (!items.length) {
      grid.innerHTML = '';
      grid.querySelectorAll('*').forEach((n) => n.remove());
      if (empty) empty.style.display = 'flex';
      if (cta) cta.style.display = 'none';
      if (countEl) countEl.textContent = '0 محصول ذخیره شده';
      return;
    }

    if (empty) empty.style.display = 'none';
    if (cta) cta.style.display = 'flex';
    if (countEl) {
      const word = items.length === 1 ? 'محصول' : 'محصول';
      countEl.textContent = items.length + ' ' + word + ' ذخیره شده';
    }

    grid.innerHTML = items.map((it) => {
      const cat = (it.cats && it.cats[0]) || '';
      const sale = it.sale_price && Number(it.sale_price) < Number(it.regular_price);
      const price = sale ? it.sale_price : it.price;
      const old = sale ? it.regular_price : '';

      if (view === 'list') {
        return `
          <div class="snb-wish-item snb-wish-item--list" data-id="${it.id}">
            <a href="${escapeHtml(it.permalink)}" class="snb-wish-img-wrap">
              <img src="${escapeHtml(it.image)}" alt="${escapeHtml(it.name)}" loading="lazy">
              ${!it.in_stock ? '<span class="snb-wish-nostock">ناموجود</span>' : ''}
            </a>
            <div class="snb-wish-body snb-wish-body--list">
              <div class="snb-wish-info">
                ${cat ? `<span class="snb-wish-cat">${escapeHtml(cat)}</span>` : ''}
                <a href="${escapeHtml(it.permalink)}" class="snb-wish-name">${escapeHtml(it.name)}</a>
              </div>
              <div class="snb-wish-pricebox">
                <span class="snb-wish-price">${fmt(price)} <small>تومان</small></span>
                ${old ? `<span class="snb-wish-old">${fmt(old)}</span>` : ''}
              </div>
              <div class="snb-wish-actions">
                <button type="button" class="snb-wish-addcart" data-addcart="${it.id}" ${!it.in_stock ? 'disabled' : ''}>
                  افزودن به سبد
                </button>
                <button type="button" class="snb-wish-remove" data-remove="${it.id}">حذف</button>
              </div>
            </div>
            <button type="button" class="snb-wish-x" data-remove="${it.id}" aria-label="حذف">✕</button>
          </div>`;
      }

      return `
        <div class="snb-wish-item" data-id="${it.id}">
          <button type="button" class="snb-wish-x" data-remove="${it.id}" aria-label="حذف">✕</button>
          ${!it.in_stock ? '<span class="snb-wish-nostock-badge">ناموجود</span>' : ''}
          <a href="${escapeHtml(it.permalink)}" class="snb-wish-img-wrap">
            <img src="${escapeHtml(it.image)}" alt="${escapeHtml(it.name)}" loading="lazy">
          </a>
          <div class="snb-wish-body">
            ${cat ? `<span class="snb-wish-cat">${escapeHtml(cat)}</span>` : ''}
            <a href="${escapeHtml(it.permalink)}" class="snb-wish-name">${escapeHtml(it.name)}</a>
            <div class="snb-wish-pricebox">
              <span class="snb-wish-price">${fmt(price)} <small>تومان</small></span>
              ${old ? `<span class="snb-wish-old">${fmt(old)}</span>` : ''}
            </div>
            <button type="button" class="snb-wish-addcart" data-addcart="${it.id}" ${!it.in_stock ? 'disabled' : ''}>
              افزودن به سبد خرید
            </button>
          </div>
        </div>`;
    }).join('');
  }

  // ── Init wishlist page ─────────────────────────────
  async function initPage() {
    const grid = $('#snbWishGrid');
    if (!grid) return;

    let currentView = 'grid';
    let currentSort = 'default';
    let items = [];

    // Fetch merged wishlist ids + full items
    const ids = await getIds();
    if (ids.length) {
      const r = await post('senoobar_wishlist_render', { ids });
      if (r && r.success) items = r.data.items || [];
    }

    function applySort(list) {
      const arr = [...list];
      if (currentSort === 'price-asc') arr.sort((a, b) => Number(a.price) - Number(b.price));
      else if (currentSort === 'price-desc') arr.sort((a, b) => Number(b.price) - Number(a.price));
      return arr;
    }

    function refresh() {
      const sorted = applySort(items);
      render(sorted, currentView);
      grid.dataset.view = currentView;
    }

    // Sort
    const sortSel = $('#snbWishSort');
    if (sortSel) {
      sortSel.addEventListener('change', () => {
        currentSort = sortSel.value;
        refresh();
      });
    }

    // View toggle
    $$('.snb-wish-view-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        currentView = btn.dataset.view;
        $$('.snb-wish-view-btn').forEach((b) => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        refresh();
      });
    });

    // Delegated remove
    grid.addEventListener('click', async (e) => {
      const rm = e.target.closest('[data-remove]');
      if (rm) {
        e.preventDefault();
        const id = Number(rm.dataset.remove);
        if (isLoggedIn()) {
          await post('senoobar_wishlist_toggle', { product_id: id });
        } else {
          setLocal(getLocal().filter((x) => x !== id));
        }
        items = items.filter((it) => Number(it.id) !== id);
        refresh();
      }
    });

    // Delegated add-to-cart (use WooCommerce AJAX)
    grid.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-addcart]');
      if (!btn) return;
      e.preventDefault();
      const id = btn.dataset.addcart;
      btn.disabled = true;
      btn.textContent = 'در حال افزودن...';
      try {
        // jQuery-based wc add_to_cart is available globally on the site.
        if (window.jQuery) {
          window.jQuery('body').trigger('added_to_cart', []);
        }
        const r = await post('senoobar_add_to_cart', { product_id: id });
        if (r && r.success) {
          btn.textContent = '✓ اضافه شد';
          if (window.jQuery) window.jQuery(document.body).trigger('wc_fragment_refresh');
        } else {
          btn.textContent = 'خطا، دوباره تلاش کنید';
          btn.disabled = false;
        }
      } catch (_) {
        btn.textContent = 'خطا، دوباره تلاش کنید';
        btn.disabled = false;
      }
    });

    refresh();
  }

  // ── Product/card heart button toggle ────────────────
  // Uses document-level delegation so it keeps working after AJAX filter /
  // infinite-scroll replace the product cards.
  function initHeartButtons() {
    async function paint(btn) {
      const id = Number(btn.getAttribute('data-wishlist-btn'));
      const inList = isLoggedIn()
        ? (await getIds()).includes(id)
        : getLocal().includes(id);
      btn.classList.toggle('is-active', inList);
      const label = btn.querySelector('[data-wishlist-label]');
      if (label) label.textContent = inList ? 'در علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی';
    }

    // Paint hearts that already exist on the page.
    $$('[data-wishlist-btn]').forEach(paint);

    // Delegated click handler (runs once, survives DOM replacement).
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('[data-wishlist-btn]');
      if (!btn) return;

      // Don't let the click reach the wrapping product link.
      e.preventDefault();
      e.stopPropagation();

      const id = Number(btn.getAttribute('data-wishlist-btn'));
      const nowActive = await toggle(id);

      // Reflect the new state immediately.
      btn.classList.toggle('is-active', nowActive);
      const label = btn.querySelector('[data-wishlist-label]');
      if (label) label.textContent = nowActive ? 'در علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی';

      // Also update any other heart with the same product id in the DOM.
      $$('[data-wishlist-btn="' + id + '"]').forEach((b) => {
        if (b !== btn) {
          b.classList.toggle('is-active', nowActive);
          const l = b.querySelector('[data-wishlist-label]');
          if (l) l.textContent = nowActive ? 'در علاقه‌مندی‌ها' : 'افزودن به علاقه‌مندی';
        }
      });
    });
  }

  // ── Boot ───────────────────────────────────────────
  function boot() {
    initPage();
    initHeartButtons();
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
