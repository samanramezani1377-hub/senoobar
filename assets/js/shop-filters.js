/**
 * Senoobar — Shop AJAX Filters + Infinite Scroll v2
 * 
 * Handles:
 * - Category filtering
 * - Price range filtering
 * - Sorting
 * - Grid/List view toggle
 * - Mobile filter panel
 * - URL sync (browser history)
 * - Infinite scroll (load more on scroll)
 */
(function () {
  'use strict';

  const shop = {
    container: null,
    loadingEl: null,
    scrollTrigger: null,
    observer: null,
    isFiltering: false,
    isLoadingMore: false,
    currentView: 'grid',
    currentPage: 1,
    totalPages: 1,
    hasMore: false,

    init() {
      this.container = document.getElementById('productsGrid');
      this.loadingEl = document.getElementById('filterLoading');
      if (!this.container) return;

      // Read pagination state from initial page
      this.readPaginationState();

      this.bindCategoryFilters();
      this.bindSort();
      this.bindPriceFilter();
      this.bindViewToggle();
      this.bindMobileFilter();
      this.bindResetFilters();
      this.initInfiniteScroll();
    },

    // ─── Read pagination info from DOM ─────────────
    readPaginationState() {
      const pagination = document.getElementById('shopPagination');
      if (!pagination) return;

      const links = pagination.querySelectorAll('a.page-numbers');
      const nums = [];
      links.forEach(link => {
        const n = parseInt(link.textContent, 10);
        if (!isNaN(n)) nums.push(n);
      });

      this.totalPages = nums.length > 0 ? Math.max(...nums) : 1;
      this.currentPage = 1;

      // Check if current page has "current" class
      const currentSpan = pagination.querySelector('span.page-numbers.current');
      if (currentSpan) {
        this.currentPage = parseInt(currentSpan.textContent, 10) || 1;
      }

      this.hasMore = this.currentPage < this.totalPages;
    },

    // ─── Show/hide loading ────────────────────────
    showLoading() {
      if (this.loadingEl) this.loadingEl.style.display = 'flex';
      if (this.container) this.container.style.opacity = '0.5';
    },

    hideLoading() {
      if (this.loadingEl) this.loadingEl.style.display = 'none';
      if (this.container) this.container.style.opacity = '1';
    },

    // ─── Build query string ───────────────────────
    buildQuery(extraParams = {}) {
      const params = new URLSearchParams(window.location.search);

      Object.entries(extraParams).forEach(([key, value]) => {
        if (value) {
          params.set(key, value);
        } else {
          params.delete(key);
        }
      });

      return params.toString();
    },

    // ─── Fetch products via AJAX ──────────────────
    async fetchProducts(queryString) {
      this.showLoading();
      this.isFiltering = true;

      try {
        const url = `${window.location.pathname}?${queryString}`;
        const response = await fetch(url, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) throw new Error('Network error');

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newGrid = doc.querySelector('#productsGrid');
        const newPagination = doc.querySelector('#shopPagination');
        const newCount = doc.querySelector('#resultsCount');

        if (newGrid) {
          this.container.innerHTML = newGrid.innerHTML;
          this.container.className = newGrid.className;
          this.container.classList.add(this.currentView === 'list' ? 'list-view' : 'grid-view');
        }

        // Update pagination (hidden but used for state tracking)
        if (newPagination) {
          const paginationEl = document.getElementById('shopPagination');
          if (paginationEl) paginationEl.innerHTML = newPagination.innerHTML;
        }

        if (newCount) {
          const countEl = document.getElementById('resultsCount');
          if (countEl) countEl.textContent = newCount.textContent;
        }

        // Update pagination state
        this.readPaginationState();

        // Update URL
        const newUrl = `${window.location.pathname}${queryString ? '?' + queryString : ''}`;
        window.history.pushState({}, '', newUrl);

        // Re-bind + re-init observer
        this.bindCategoryFilters();
        this.bindViewToggle();
        this.bindResetFilters();
        this.initInfiniteScroll();

        // Show/hide end message
        this.updateEndMessage();
      } catch (error) {
        console.error('Shop filter error:', error);
      } finally {
        this.hideLoading();
        this.isFiltering = false;
      }
    },

    // ─── Load more (infinite scroll) ──────────────
    async loadMore() {
      if (this.isLoadingMore || !this.hasMore || this.isFiltering) return;

      this.isLoadingMore = true;
      this.showInfiniteLoader();

      try {
        const nextPage = this.currentPage + 1;
        const queryString = this.buildQuery({ paged: nextPage });
        const url = `${window.location.pathname}?${queryString}`;

        const response = await fetch(url, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (!response.ok) throw new Error('Network error');

        const html = await response.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Get new products
        const newGrid = doc.querySelector('#productsGrid');
        const newPagination = doc.querySelector('#shopPagination');

        if (newGrid) {
          const newProducts = newGrid.querySelectorAll('ul.products > li.product');
          const ul = this.container.querySelector('ul.products');

          if (ul && newProducts.length > 0) {
            newProducts.forEach(product => {
              const clone = product.cloneNode(true);
              clone.style.opacity = '0';
              clone.style.transform = 'translateY(20px)';
              clone.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
              ul.appendChild(clone);

              // Animate in
              requestAnimationFrame(() => {
                clone.style.opacity = '1';
                clone.style.transform = 'translateY(0)';
              });
            });
          }
        }

        // Update state
        if (newPagination) {
          const paginationEl = document.getElementById('shopPagination');
          if (paginationEl) paginationEl.innerHTML = newPagination.innerHTML;
        }

        this.currentPage = nextPage;
        this.readPaginationState();

        // Re-init observer (may have new trigger)
        this.initInfiniteScroll();

        // Show/hide end message
        this.updateEndMessage();
      } catch (error) {
        console.error('Infinite scroll error:', error);
      } finally {
        this.hideInfiniteLoader();
        this.isLoadingMore = false;
      }
    },

    // ─── Intersection Observer for Infinite Scroll ─
    initInfiniteScroll() {
      // Destroy previous observer
      if (this.observer) {
        this.observer.disconnect();
        this.observer = null;
      }

      // Only set up if there are more pages
      if (!this.hasMore) return;

      // Find or create scroll trigger
      let trigger = document.getElementById('infiniteScrollTrigger');

      if (!trigger) {
        trigger = document.createElement('div');
        trigger.id = 'infiniteScrollTrigger';
        trigger.className = 'infinite-scroll-trigger';

        // Insert after products grid
        const wrapper = document.querySelector('.products-wrapper');
        if (wrapper) {
          wrapper.insertAdjacentElement('afterend', trigger);
        }
      }

      // Always show trigger until we have no more
      trigger.style.display = '';

      // Set up Intersection Observer
      this.observer = new IntersectionObserver(
        (entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting && this.hasMore && !this.isLoadingMore && !this.isFiltering) {
              this.loadMore();
            }
          });
        },
        {
          rootMargin: '300px 0px',
          threshold: 0,
        }
      );

      this.observer.observe(trigger);
    },

    // ─── Infinite Scroll Loader ───────────────────
    showInfiniteLoader() {
      let loader = document.getElementById('infiniteLoader');
      if (!loader) {
        loader = document.createElement('div');
        loader.id = 'infiniteLoader';
        loader.className = 'infinite-loader';
        loader.innerHTML = `
          <div class="infinite-loader__spinner">
            <div class="spinner"></div>
          </div>
          <span class="infinite-loader__text">در حال بارگذاری محصولات بیشتر...</span>
        `;
        const trigger = document.getElementById('infiniteScrollTrigger');
        if (trigger) {
          trigger.insertAdjacentElement('beforebegin', loader);
        } else {
          this.container.insertAdjacentElement('afterend', loader);
        }
      }
      loader.style.display = 'flex';
    },

    hideInfiniteLoader() {
      const loader = document.getElementById('infiniteLoader');
      if (loader) loader.style.display = 'none';
    },

    // ─── End-of-products message ──────────────────
    updateEndMessage() {
      let endMsg = document.getElementById('infiniteEndMessage');
      const trigger = document.getElementById('infiniteScrollTrigger');

      if (!this.hasMore) {
        // Remove trigger
        if (trigger) trigger.style.display = 'none';

        // Show end message
        if (!endMsg) {
          endMsg = document.createElement('div');
          endMsg.id = 'infiniteEndMessage';
          endMsg.className = 'infinite-end-message';
          endMsg.innerHTML = `
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <path d="M9 12l2 2 4-4"/>
            </svg>
            <span>همه محصولات نمایش داده شد</span>
          `;
          if (trigger) {
            trigger.insertAdjacentElement('beforebegin', endMsg);
          } else {
            this.container.insertAdjacentElement('afterend', endMsg);
          }
        }
        endMsg.style.display = 'flex';
      } else {
        if (endMsg) endMsg.style.display = 'none';
        if (trigger) trigger.style.display = '';
      }
    },

    // ─── Category Filters ─────────────────────────
    bindCategoryFilters() {
      const links = document.querySelectorAll('.category-filter-item[data-cat]');
      links.forEach(link => {
        const newLink = link.cloneNode(true);
        link.parentNode.replaceChild(newLink, link);

        newLink.addEventListener('click', (e) => {
          e.preventDefault();
          const cat = newLink.dataset.cat;

          // Update active
          document.querySelectorAll('.category-filter-item').forEach(l => l.classList.remove('active'));
          newLink.classList.add('active');

          // Build query
          let queryString;
          if (cat === 'all') {
            // Go to base shop URL with NO filters
            queryString = '';
          } else {
            // Keep current sort/price but switch category
            const params = new URLSearchParams(window.location.search);
            params.delete('min_price');
            params.delete('max_price');
            params.set('product_cat', cat);
            queryString = params.toString();
          }

          // Close mobile filter
          if (window.innerWidth < 992) {
            document.getElementById('shopFilters')?.classList.remove('open');
            document.getElementById('filterOverlay')?.classList.remove('open');
            document.body.style.overflow = '';
          }

          // Reset scroll and load fresh
          this.resetInfiniteState();
          this.fetchProducts(queryString);
        });
      });
    },

    // ─── Sort ─────────────────────────────────────
    bindSort() {
      const sortSelect = document.getElementById('sortBy');
      if (!sortSelect) return;

      const newSelect = sortSelect.cloneNode(true);
      sortSelect.parentNode.replaceChild(newSelect, sortSelect);

      newSelect.addEventListener('change', () => {
        this.resetInfiniteState();
        this.fetchProducts(this.buildQuery({ orderby: newSelect.value }));
      });
    },

    // ─── Price Filter ─────────────────────────────
    bindPriceFilter() {
      const applyBtn = document.getElementById('applyPriceFilter');
      if (!applyBtn) return;

      const newBtn = applyBtn.cloneNode(true);
      applyBtn.parentNode.replaceChild(newBtn, applyBtn);

      newBtn.addEventListener('click', () => {
        const minPrice = document.getElementById('minPrice')?.value || '';
        const maxPrice = document.getElementById('maxPrice')?.value || '';

        if (window.innerWidth < 992) {
          document.getElementById('shopFilters')?.classList.remove('open');
          document.getElementById('filterOverlay')?.classList.remove('open');
          document.body.style.overflow = '';
        }

        this.resetInfiniteState();
        this.fetchProducts(this.buildQuery({ min_price: minPrice, max_price: maxPrice }));
      });

      ['minPrice', 'maxPrice'].forEach(id => {
        const input = document.getElementById(id);
        if (input) {
          const newInput = input.cloneNode(true);
          input.parentNode.replaceChild(newInput, input);
          newInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') newBtn.click();
          });
        }
      });
    },

    // ─── View Toggle ──────────────────────────────
    bindViewToggle() {
      const buttons = document.querySelectorAll('.view-btn[data-view]');
      buttons.forEach(btn => {
        const newBtn = btn.cloneNode(true);
        btn.parentNode.replaceChild(newBtn, btn);

        newBtn.addEventListener('click', () => {
          const view = newBtn.dataset.view;
          this.currentView = view;

          document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
          newBtn.classList.add('active');

          const grid = document.getElementById('productsGrid');
          if (grid) {
            grid.classList.remove('grid-view', 'list-view');
            grid.classList.add(view === 'list' ? 'list-view' : 'grid-view');
          }

          try {
            localStorage.setItem('senoobar_shop_view', view);
          } catch (e) {}
        });
      });

      // Restore preference
      try {
        const saved = localStorage.getItem('senoobar_shop_view');
        if (saved === 'list') {
          const listBtn = document.querySelector('.view-btn[data-view="list"]');
          if (listBtn) listBtn.click();
        }
      } catch (e) {}
    },

    // ─── Mobile Filter Panel ──────────────────────
    bindMobileFilter() {
      const toggleBtn = document.getElementById('filterToggle');
      const closeBtn = document.getElementById('filterClose');
      const sidebar = document.getElementById('shopFilters');
      const overlay = document.getElementById('filterOverlay');

      if (!toggleBtn || !sidebar) return;

      const newToggle = toggleBtn.cloneNode(true);
      toggleBtn.parentNode.replaceChild(newToggle, toggleBtn);
      newToggle.addEventListener('click', () => {
        sidebar.classList.add('open');
        overlay?.classList.add('open');
        document.body.style.overflow = 'hidden';
      });

      if (closeBtn) {
        const newClose = closeBtn.cloneNode(true);
        closeBtn.parentNode.replaceChild(newClose, closeBtn);
        newClose.addEventListener('click', () => this.closeMobileFilter());
      }

      if (overlay) {
        const newOverlay = overlay.cloneNode(true);
        overlay.parentNode.replaceChild(newOverlay, overlay);
        newOverlay.addEventListener('click', () => this.closeMobileFilter());
      }

      // Close on Escape key
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') this.closeMobileFilter();
      });
    },

    closeMobileFilter() {
      document.getElementById('shopFilters')?.classList.remove('open');
      document.getElementById('filterOverlay')?.classList.remove('open');
      document.body.style.overflow = '';
    },

    // ─── Reset Filters ────────────────────────────
    bindResetFilters() {
      const resetBtn = document.getElementById('resetFilters');
      if (!resetBtn) return;

      const newBtn = resetBtn.cloneNode(true);
      resetBtn.parentNode.replaceChild(newBtn, resetBtn);

      newBtn.addEventListener('click', () => {
        const minPrice = document.getElementById('minPrice');
        const maxPrice = document.getElementById('maxPrice');
        if (minPrice) minPrice.value = '';
        if (maxPrice) maxPrice.value = '';

        document.querySelectorAll('.category-filter-item').forEach(l => l.classList.remove('active'));
        const allCat = document.querySelector('.category-filter-item[data-cat="all"]');
        if (allCat) allCat.classList.add('active');

        const sortSelect = document.getElementById('sortBy');
        if (sortSelect) sortSelect.value = 'menu_order';

        this.resetInfiniteState();
        this.fetchProducts('');
      });
    },

    // ─── Reset infinite scroll state ──────────────
    resetInfiniteState() {
      this.currentPage = 1;
      this.hasMore = false;
      this.totalPages = 1;

      // Remove end message & loader
      const endMsg = document.getElementById('infiniteEndMessage');
      if (endMsg) endMsg.style.display = 'none';

      const loader = document.getElementById('infiniteLoader');
      if (loader) loader.style.display = 'none';

      // Disconnect old observer
      if (this.observer) {
        this.observer.disconnect();
        this.observer = null;
      }
    },
  };

  // Initialize
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => shop.init());
  } else {
    shop.init();
  }
})();
