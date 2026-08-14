<?php
/**
 * Search Page — Senoobar v2 Design
 * Fully functional search with:
 * - Breadcrumb navigation
 * - Search results for products and posts
 * - Product cards with add-to-cart
 * - Blog post cards
 * - Empty state with suggestions
 * - RTL + Vazirmatn
 */

if (!defined('ABSPATH')) exit;

get_header();

$search_query = get_search_query();
$total_results = $wp_query->found_posts;
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
        <?php echo $total_results; ?> نتیجه پیدا شد
      <?php else: ?>
        نتیجه‌ای پیدا نشد
      <?php endif; ?>
    </p>
  </div>

  <div style="max-width:1280px;margin:0 auto;padding:24px 16px 32px;">

    <?php if (have_posts()): ?>

      <!-- Search Results -->
      <div style="display:flex;flex-direction:column;gap:16px;">

        <?php while (have_posts()): the_post(); ?>
          <?php $post_type = get_post_type(); ?>

          <?php if ($post_type === 'product' && class_exists('WooCommerce')): ?>
            <?php
            global $product;
            $_product = wc_get_product(get_the_ID());
            if (!$_product || !$_product->is_visible()) continue;
            $pimg = wp_get_attachment_image_url($_product->get_image_id(), 'medium') ?: wc_placeholder_img_src('medium');
            $pprice = $_product->get_price();
            $pname = $_product->get_name();
            $plink = get_permalink(get_the_ID());
            ?>
            <!-- Product Card -->
            <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden;display:flex;flex-direction:column;border:1px solid #f3f4f6;" class="search-product-card">
              <div style="display:flex;flex-direction:column;md:flex-row;gap:16px;padding:16px;">
                <div style="flex-shrink:0;">
                  <a href="<?php echo esc_url($plink); ?>" style="display:block;text-decoration:none;">
                    <img src="<?php echo esc_url($pimg); ?>" alt="<?php echo esc_attr($pname); ?>" style="width:100%;max-width:200px;height:auto;aspect-ratio:1;object-fit:cover;border-radius:12px;">
                  </a>
                </div>
                <div style="flex:1;min-width:0;display:flex;flex-direction:column;justify-content:center;">
                  <div style="margin-bottom:8px;">
                    <span style="display:inline-block;background:rgba(30,58,47,0.06);color:#1e3a2f;font-size:0.75rem;font-weight:600;padding:4px 10px;border-radius:20px;">محصول</span>
                  </div>
                  <a href="<?php echo esc_url($plink); ?>" style="text-decoration:none;display:block;margin-bottom:8px;">
                    <h3 style="font-size:1.1rem;font-weight:700;color:#111827;margin:0;line-height:1.5;"><?php echo esc_html($pname); ?></h3>
                  </a>
                  <p style="font-size:0.85rem;color:#6b7280;margin:0 0 12px;line-height:1.6;">
                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                  </p>
                  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                      <span style="font-size:1.2rem;font-weight:800;color:#1e3a2f;"><?php echo number_format($pprice); ?></span>
                      <span style="font-size:0.8rem;color:#6b7280;margin-right:4px;">تومان</span>
                    </div>
                    <a href="<?php echo esc_url($plink); ?>" style="background:#1e3a2f;color:#fff;border:none;border-radius:12px;padding:10px 24px;font-weight:600;font-size:0.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                      مشاهده محصول
                      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                  </div>
                </div>
              </div>
            </div>

          <?php elseif ($post_type === 'post'): ?>
            <?php
            $pimg = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium') : 'https://images.unsplash.com/photo-1673300881006-3bd384aa1949?w=400&h=300&fit=crop&auto=format';
            $pdate = get_the_date('j F Y');
            $pauthor = get_the_author();
            ?>
            <!-- Blog Card -->
            <a href="<?php the_permalink(); ?>" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #f3f4f6;text-decoration:none;display:flex;flex-direction:column;md:flex-row;" class="search-blog-card">
              <div style="flex-shrink:0;overflow:hidden;">
                <img src="<?php echo esc_url($pimg); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" style="width:100%;max-width:240px;height:auto;aspect-ratio:4/3;object-fit:cover;display:block;" loading="lazy">
              </div>
              <div style="flex:1;min-width:0;padding:16px;display:flex;flex-direction:column;justify-content:center;">
                <div style="margin-bottom:8px;">
                  <span style="display:inline-block;background:rgba(30,58,47,0.06);color:#1e3a2f;font-size:0.75rem;font-weight:600;padding:4px 10px;border-radius:20px;">مقاله</span>
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;color:#111827;margin:0 0 8px;line-height:1.5;"><?php the_title(); ?></h3>
                <p style="font-size:0.85rem;color:#6b7280;margin:0 0 12px;line-height:1.6;">
                  <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                </p>
                <div style="display:flex;align-items:center;gap:12px;font-size:0.8rem;color:#9ca3af;">
                  <span>📅 <?php echo esc_html($pdate); ?></span>
                  <span>✍️ <?php echo esc_html($pauthor); ?></span>
                </div>
              </div>
            </a>

          <?php else: ?>
            <!-- Generic Post Card -->
            <a href="<?php the_permalink(); ?>" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #f3f4f6;text-decoration:none;display:block;padding:20px;" class="search-generic-card">
              <div style="margin-bottom:8px;">
                <span style="display:inline-block;background:rgba(30,58,47,0.06);color:#1e3a2f;font-size:0.75rem;font-weight:600;padding:4px 10px;border-radius:20px;"><?php echo esc_html($post_type); ?></span>
              </div>
              <h3 style="font-size:1.1rem;font-weight:700;color:#111827;margin:0 0 8px;line-height:1.5;"><?php the_title(); ?></h3>
              <p style="font-size:0.85rem;color:#6b7280;margin:0;line-height:1.6;">
                <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
              </p>
            </a>
          <?php endif; ?>

        <?php endwhile; ?>
      </div>

      <!-- Pagination -->
      <div style="margin-top:32px;display:flex;justify-content:center;">
        <?php the_posts_pagination(['prev_text' => 'قبلی', 'next_text' => 'بعدی']); ?>
      </div>

    <?php else: ?>

      <!-- Empty State -->
      <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);text-align:center;padding:60px 20px;max-width:600px;margin:0 auto;">
        <div style="font-size:4rem;margin-bottom:16px;">🔍</div>
        <h3 style="font-size:1.2rem;font-weight:700;color:#374151;margin-bottom:8px;">نتیجه‌ای پیدا نشد</h3>
        <p style="font-size:0.9rem;color:#6b7280;margin-bottom:24px;line-height:1.8;">
          متأسفانه هیچ موردی مطابق با عبارت «<?php echo esc_html($search_query); ?>» پیدا نشد.<br>
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
@media (min-width: 768px) {
  .search-product-card,
  .search-blog-card {
    flex-direction: row !important;
  }
  .search-product-card > div:first-child,
  .search-blog-card > div:first-child {
    width: 240px;
    flex-shrink: 0;
  }
  .search-product-card img,
  .search-blog-card img {
    max-width: 240px;
    width: 100%;
    height: 100%;
    aspect-ratio: 1;
  }
}
</style>

<?php get_footer(); ?>
