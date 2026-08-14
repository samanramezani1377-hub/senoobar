<?php
/**
 * Search Page — Senoobar v2 Design
 * Fully functional search with:
 * - Breadcrumb navigation
 * - Product-only search results (via pre_get_posts)
 * - Sidebar filters: categories, price range, sort
 * - 3-column product grid
 * - Empty state with suggestions
 * - Support banner
 * - RTL + Vazirmatn
 */

if (!defined('ABSPATH')) exit;

get_header();

global $wp_query;
$search_query = get_search_query();
$total_results = $wp_query->found_posts;

$product_categories = get_terms([
    'taxonomy'   => 'product_cat',
    'hide_empty' => true,
    'orderby'    => 'name',
]);
?>

<div style="background:#f9fafb;min-height:100vh;direction:rtl;font-family:Vazirmatn,sans-serif;">

  <!-- Breadcrumb + Title -->
  <div style="max-width:1280px;margin:0 auto;padding:24px 16px 0;">
    <div style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:#6b7280;margin-bottom:24px;">
      <a href="<?php echo home_url('/'); ?>" style="color:#6b7280;text-decoration:none;">خانه</a>
      <span>/</span>
      <span style="color:#1e3a2f;font-weight:600;">جستجو</span>
    </div>
    <h1 style="font-size:1.8rem;font-weight:800;color:#111827;margin:0 0 4px 0;">
      <?php if ($search_query): ?>
        نتایج جستجو برای «<?php echo esc_html($search_query); ?>»
      <?php else: ?>
        جستجو
      <?php endif; ?>
    </h1>
    <p style="font-size:0.9rem;color:#6b7280;margin:0;">
      <?php if ($total_results > 0): ?>
        <?php echo $total_results; ?> محصول پیدا شد
      <?php else: ?>
        نتیجه‌ای پیدا نشد
      <?php endif; ?>
    </p>
  </div>

  <div style="max-width:1280px;margin:0 auto;padding:24px 16px 32px;" class="shop-main">

    <?php if (have_posts()): ?>

      <div style="display:flex;gap:24px;">

        <!-- Sidebar Filters -->
        <aside style="width:260px;flex-shrink:0;" class="shop-sidebar search-sidebar">
          <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:20px;position:sticky;top:80px;">
            <!-- Filter Header (Mobile Toggle) -->
            <div class="filter-header" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
              <h4 style="font-size:1rem;font-weight:700;color:#111827;margin:0;">فیلترها</h4>
              <button type="button" class="filter-close" id="filterClose" aria-label="بستن فیلترها" style="background:none;border:none;cursor:pointer;padding:4px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
              </button>
            </div>
            
            <!-- Category Filter -->
            <div class="filter-group" style="margin-bottom:24px;">
              <h4 class="filter-group-title" style="font-size:0.9rem;font-weight:700;color:#111827;margin:0 0 12px;">دسته‌بندی</h4>
              <ul class="category-filter-list" style="list-style:none;padding:0;margin:0;">
                <li style="margin-bottom:8px;">
                  <a href="<?php echo add_query_arg(['s' => $search_query, 'product_cat' => ''], home_url('/')); ?>" 
                     class="category-filter-item <?php echo (!isset($_GET['product_cat']) || empty($_GET['product_cat'])) ? 'active' : ''; ?>"
                     style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-radius:10px;font-size:0.85rem;color:#374151;text-decoration:none;transition:all 0.2s;">
                    همه محصولات
                  </a>
                </li>
                <?php foreach ($product_categories as $cat): ?>
                  <li style="margin-bottom:8px;">
                    <a href="<?php echo add_query_arg(['s' => $search_query, 'product_cat' => $cat->slug], home_url('/')); ?>" 
                       class="category-filter-item <?php echo (isset($_GET['product_cat']) && $_GET['product_cat'] === $cat->slug) ? 'active' : ''; ?>"
                       style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;border-radius:10px;font-size:0.85rem;color:#374151;text-decoration:none;transition:all 0.2s;">
                      <span><?php echo esc_html($cat->name); ?></span>
                      <span class="cat-count" style="background:#f3f4f6;color:#6b7280;font-size:0.75rem;padding:2px 8px;border-radius:20px;"><?php echo $cat->count; ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>

            <!-- Price Range Filter -->
            <div class="filter-group" style="margin-bottom:24px;">
              <h4 class="filter-group-title" style="font-size:0.9rem;font-weight:700;color:#111827;margin:0 0 12px;">محدوده قیمت</h4>
              <form method="get" action="<?php echo esc_url(home_url('/')); ?>" style="display:flex;flex-direction:column;gap:8px;">
                <input type="hidden" name="s" value="<?php echo esc_attr($search_query); ?>">
                <?php if (isset($_GET['product_cat'])): ?>
                  <input type="hidden" name="product_cat" value="<?php echo esc_attr(sanitize_key($_GET['product_cat'])); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['orderby'])): ?>
                  <input type="hidden" name="orderby" value="<?php echo esc_attr(sanitize_key($_GET['orderby'])); ?>">
                <?php endif; ?>
                <div style="display:flex;gap:8px;align-items:center;">
                  <input type="number" name="min_price" placeholder="از" min="0" step="100000"
                         value="<?php echo isset($_GET['min_price']) ? intval($_GET['min_price']) : ''; ?>"
                         style="flex:1;border:1px solid #e5e7eb;border-radius:10px;padding:8px 12px;font-size:0.85rem;background:#f9fafb;outline:none;font-family:Vazirmatn,sans-serif;">
                  <span style="color:#9ca3af;font-size:0.85rem;">تا</span>
                  <input type="number" name="max_price" placeholder="تا" min="0" step="100000"
                         value="<?php echo isset($_GET['max_price']) ? intval($_GET['max_price']) : ''; ?>"
                         style="flex:1;border:1px solid #e5e7eb;border-radius:10px;padding:8px 12px;font-size:0.85rem;background:#f9fafb;outline:none;font-family:Vazirmatn,sans-serif;">
                </div>
                <button type="submit" style="background:#1e3a2f;color:#fff;border:none;border-radius:10px;padding:10px;font-weight:600;font-size:0.85rem;cursor:pointer;font-family:Vazirmatn,sans-serif;">اعمال فیلتر</button>
              </form>
            </div>

            <!-- Reset Filters -->
            <?php if (isset($_GET['min_price']) || isset($_GET['max_price']) || isset($_GET['product_cat'])): ?>
              <a href="<?php echo add_query_arg(['s' => $search_query, 'min_price' => '', 'max_price' => '', 'product_cat' => '', 'orderby' => ''], home_url('/')); ?>" 
                 style="display:flex;align-items:center;justify-content:center;gap:6px;color:#ef4444;font-size:0.85rem;text-decoration:none;font-weight:600;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M1 4v6h6M23 20v-6h-6"/><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"/></svg>
                حذف همه فیلترها
              </a>
            <?php endif; ?>

          </div>
        </aside>

        <!-- Mobile Filter Overlay -->
        <div class="filter-overlay" id="filterOverlay" style="display:none;"></div>

        <!-- Main Content -->
        <div style="flex:1;min-width:0;">
          
          <!-- Toolbar -->
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:16px 20px;">
            <div style="display:flex;align-items:center;gap:12px;">
              <span class="results-count" style="font-size:0.9rem;color:#6b7280;">
                <?php echo $total_results; ?> محصول
              </span>
              <!-- Mobile Filter Button -->
              <button type="button" class="btn-filter-toggle" id="filterToggle" style="display:flex;align-items:center;gap:6px;background:none;border:1px solid #e5e7eb;border-radius:10px;padding:8px 12px;font-size:0.85rem;color:#374151;cursor:pointer;font-family:Vazirmatn,sans-serif;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>
                </svg>
                فیلترها
              </button>
            </div>
            <div style="display:flex;align-items:center;gap:12px;">
              <!-- Sort -->
              <form method="get" action="<?php echo esc_url(home_url('/')); ?>" style="display:flex;align-items:center;gap:8px;">
                <input type="hidden" name="s" value="<?php echo esc_attr($search_query); ?>">
                <?php if (isset($_GET['product_cat'])): ?>
                  <input type="hidden" name="product_cat" value="<?php echo esc_attr(sanitize_key($_GET['product_cat'])); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['min_price'])): ?>
                  <input type="hidden" name="min_price" value="<?php echo esc_attr(intval($_GET['min_price'])); ?>">
                <?php endif; ?>
                <?php if (isset($_GET['max_price'])): ?>
                  <input type="hidden" name="max_price" value="<?php echo esc_attr(intval($_GET['max_price'])); ?>">
                <?php endif; ?>
                <label for="sortBy" style="font-size:0.85rem;color:#4b5563;">مرتب‌سازی:</label>
                <select id="sortBy" name="orderby" onchange="this.form.submit()" style="border:1px solid #e5e7eb;border-radius:10px;padding:8px 12px;font-size:0.85rem;background:#f9fafb;outline:none;font-family:Vazirmatn,sans-serif;">
                  <option value="menu_order" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'menu_order'); ?>>پیش‌فرض</option>
                  <option value="popularity" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'popularity'); ?>>محبوب‌ترین</option>
                  <option value="rating" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'rating'); ?>>بالاترین امتیاز</option>
                  <option value="date" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'date'); ?>>جدیدترین</option>
                  <option value="price" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'price'); ?>>قیمت: کم به زیاد</option>
                  <option value="price-desc" <?php selected(isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : '', 'price-desc'); ?>>قیمت: زیاد به کم</option>
                </select>
              </form>
            </div>
          </div>

          <!-- Products Grid -->
          <div class="products-wrapper" id="productsWrapper">
            <?php
            wc_set_loop_prop('columns', 3);
            ?>
            <ul class="products search-products-grid">
              <?php while (have_posts()): the_post(); ?>
                <?php wc_get_template_part('content', 'product'); ?>
              <?php endwhile; ?>
            </ul>
          </div>

          <!-- Pagination -->
          <div class="shop-pagination" id="shopPagination" style="margin-top:32px;display:flex;justify-content:center;">
            <?php woocommerce_pagination(); ?>
          </div>

        </div>
      </div>

    <?php else: ?>

      <!-- Empty State -->
      <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);text-align:center;padding:60px 20px;max-width:600px;margin:0 auto;">
        <div style="font-size:4rem;margin-bottom:16px;">🔍</div>
        <h3 style="font-size:1.2rem;font-weight:700;color:#374151;margin-bottom:8px;">نتیجه‌ای پیدا نشد</h3>
        <p style="font-size:0.9rem;color:#6b7280;margin-bottom:24px;line-height:1.8;">
          متأسفانه هیچ محصولی مطابق با عبارت «<?php echo esc_html($search_query); ?>» پیدا نشد.<br>
          پیشنهاد می‌کنیم عبارات دیگری را امتحان کنید یا از دسته‌بندی‌های زیر بازدید کنید.
        </p>
        <div style="display:flex;flex-direction:column;gap:12px;align-items:center;">
          <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="display:flex;gap:8px;width:100%;max-width:400px;">
            <input type="search" name="s" placeholder="جستجو در فروشگاه..." value="<?php echo esc_attr(get_search_query()); ?>" style="flex:1;border:1px solid #e5e7eb;border-radius:12px;padding:10px 16px;font-size:0.9rem;background:#f9fafb;outline:none;font-family:Vazirmatn,sans-serif;">
            <button type="submit" style="background:#1e3a2f;color:#fff;border:none;border-radius:12px;padding:10px 20px;font-weight:600;font-size:0.85rem;cursor:pointer;font-family:Vazirmatn,sans-serif;">جستجو</button>
          </form>
          <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" style="background:#fff;color:#1e3a2f;border:1px solid #1e3a2f;border-radius:12px;padding:10px 24px;font-weight:600;font-size:0.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
            مشاهده همه محصولات
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </a>
        </div>
      </div>

    <?php endif; ?>

    <!-- Support Banner -->
    <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:24px;display:flex;flex-direction:column;align-items:center;gap:16px;margin-top:24px;">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:48px;height:48px;border-radius:12px;background:#f0f7f4;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">💬</div>
        <div>
          <h3 style="font-weight:700;color:#111827;font-size:1rem;margin:0;">سوالی دارید؟ ما در کنار شما هستیم</h3>
          <p style="font-size:0.85rem;color:#6b7280;margin:2px 0 0 0;">برای راهنمایی در خرید یا پیگیری سفارش‌تان با پشتیبانی ما تماس بگیرید.</p>
        </div>
      </div>
      <button style="background:#1e3a2f;color:#fff;border:none;border-radius:12px;padding:12px 24px;font-weight:600;font-size:0.9rem;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:8px;font-family:Vazirmatn,sans-serif;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
        تماس با پشتیبانی
      </button>
    </div>

  </div>
</div>

<style>
@media (max-width: 767px) {
  .search-sidebar {
    display: none;
  }
}

.category-filter-item:hover {
  background: #f3f4f6;
}

.category-filter-item.active {
  background: #f0f7f4;
  color: #1e3a2f;
  font-weight: 600;
}

.search-products-grid {
  display: grid !important;
  grid-template-columns: repeat(3, 1fr) !important;
  gap: 16px !important;
  list-style: none !important;
  padding: 0 !important;
  margin: 0 !important;
}

.search-products-grid li.product {
  width: 100% !important;
  margin: 0 !important;
  float: none !important;
}

@media (max-width: 1023px) {
  .search-products-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }
}

@media (max-width: 639px) {
  .search-products-grid {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php get_footer(); ?>
