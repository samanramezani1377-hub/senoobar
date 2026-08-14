<?php
/**
 * Cart Page — Senoobar v2 AJAX Design
 * Fully AJAX-powered cart with floating mobile drawer
 * Uses real WooCommerce cart data
 */

if (!defined('ABSPATH')) exit;

get_header();

$cart = WC()->cart;
$cart_items = $cart->get_cart();
$cart_count = $cart->get_cart_contents_count();
$cart_subtotal = $cart->get_subtotal();
$cart_total = $cart->get_total();
$cart_discount = $cart->get_cart_discount_total();

// Customizer settings
$cart_title         = get_theme_mod('senoobar_cart_cart_title', 'سبد خرید شما');
$cart_empty_title   = get_theme_mod('senoobar_cart_cart_empty_title', 'سبد خرید شما خالی است');
$cart_empty_text    = get_theme_mod('senoobar_cart_cart_empty_text', 'محصولات مورد نظر خود را انتخاب کنید.');
$cart_empty_btn     = get_theme_mod('senoobar_cart_cart_empty_btn', 'مشاهده محصولات');
$checkout_btn       = get_theme_mod('senoobar_cart_checkout_btn', 'ادامه جهت تسویه حساب');
$continue_btn       = get_theme_mod('senoobar_cart_continue_btn', 'ادامه خرید');
$support_phone      = get_theme_mod('senoobar_cart_support_phone', '۰۹۱۳۰۲۰۵۸۹۸');
$support_title      = get_theme_mod('senoobar_cart_support_title', 'سوالی دارید؟ ما در کنار شما هستیم');
$support_text       = get_theme_mod('senoobar_cart_support_text', 'برای راهنمایی در خرید یا پیگیری سفارش‌تان با پشتیبانی ما تماس بگیرید.');
$support_btn        = get_theme_mod('senoobar_cart_support_btn', 'تماس با پشتیبانی');
?>

<div style="background:#f9fafb;min-height:100vh;direction:rtl;font-family:Vazirmatn,sans-serif;">

  <!-- Breadcrumb + Title -->
  <div style="max-width:1280px;margin:0 auto;padding:24px 16px 0;">
    <div style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:#6b7280;margin-bottom:24px;">
      <a href="<?php echo home_url('/'); ?>" style="color:#6b7280;text-decoration:none;">خانه</a>
      <span>/</span>
      <span style="color:#1e3a2f;font-weight:600;">سبد خرید</span>
    </div>
    <h1 style="font-size:1.8rem;font-weight:800;color:#111827;margin:0 0 4px 0;"><?php echo esc_html($cart_title); ?></h1>
    <p style="font-size:0.9rem;color:#6b7280;margin:0;">
      <?php echo $cart_count; ?> محصول در سبد خرید
    </p>
  </div>

  <div style="max-width:1280px;margin:0 auto;padding:24px 16px 32px;" class="cart-page">

    <?php if ($cart->is_empty()): ?>
      <!-- Empty Cart -->
      <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);text-align:center;padding:60px 20px;max-width:500px;margin:0 auto;">
        <div style="font-size:4rem;margin-bottom:16px;">🛒</div>
        <h3 style="font-size:1.2rem;font-weight:700;color:#374151;margin-bottom:8px;"><?php echo esc_html($cart_empty_title); ?></h3>
        <p style="font-size:0.9rem;color:#6b7280;margin-bottom:24px;line-height:1.8;"><?php echo esc_html($cart_empty_text); ?></p>
        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" style="background:#1e3a2f;color:#fff;border-radius:12px;padding:12px 32px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          <?php echo esc_html($cart_empty_btn); ?>
        </a>
      </div>

    <?php else: ?>

      <div style="display:grid;grid-template-columns:1fr;gap:24px;" class="cart-layout">

        <!-- Cart Items -->
        <div class="cart-main">
          <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden;" class="cart-card">
            <div id="cartItemsContainer">
              <?php foreach ($cart_items as $cart_item_key => $cart_item):
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                if (!$_product || !$_product->exists() || $cart_item['quantity'] < 1) continue;

                $pname = $_product->get_name();
                $plink = $_product->is_visible() ? get_permalink($product_id) : '';
                $pimg = wp_get_attachment_image_url($_product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail');
                $pprice = $_product->get_price();
                $psub = $pprice * $cart_item['quantity'];

                $vtext = '';
                if (!empty($cart_item['variation'])) {
                  $p = [];
                  foreach ($cart_item['variation'] as $k => $v) {
                    $p[] = wc_attribute_label(str_replace('attribute_', '', $k)) . ': ' . esc_html($v);
                  }
                  $vtext = implode(' | ', $p);
                }
              ?>
              <div class="cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>">
                <div class="cart-item__img">
                  <?php if ($plink): ?><a href="<?php echo esc_url($plink); ?>"><?php endif; ?>
                    <img src="<?php echo esc_url($pimg); ?>" alt="<?php echo esc_attr($pname); ?>">
                  <?php if ($plink): ?></a><?php endif; ?>
                </div>
                <div class="cart-item__info">
                  <div class="cart-item__name">
                    <?php if ($plink): ?><a href="<?php echo esc_url($plink); ?>"><?php endif; ?>
                      <?php echo esc_html($pname); ?>
                    <?php if ($plink): ?></a><?php endif; ?>
                  </div>
                  <?php if ($vtext): ?>
                    <div class="cart-item__variation"><?php echo esc_html($vtext); ?></div>
                  <?php endif; ?>
                  <div class="cart-item__price">
                    <span class="cart-item__unit-price"><?php echo number_format($pprice); ?> تومان</span>
                    <span class="cart-item__subtotal"><?php echo number_format($psub); ?> تومان</span>
                  </div>
                  <div class="cart-item__actions">
                    <div class="qty-control">
                      <button class="qty-btn qty-minus" data-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="کاهش">−</button>
                      <input type="number" class="qty-input" value="<?php echo $cart_item['quantity']; ?>" min="1" data-key="<?php echo esc_attr($cart_item_key); ?>">
                      <button class="qty-btn qty-plus" data-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="افزایش">+</button>
                    </div>
                    <button class="cart-item__remove" data-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="حذف">
                      <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Cart Totals Sidebar -->
        <div class="cart-sidebar">
          <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:24px;position:sticky;top:80px;" class="cart-totals-card">
            <h3 style="font-size:1.1rem;font-weight:700;color:#111827;margin:0 0 20px 0;">جمع کل سبد خرید</h3>
            <div id="cartTotalsContainer">
              <div class="cart-totals">
                <div class="cart-totals__row">
                  <span>جمع جزء</span>
                  <span id="cartSubtotal"><?php echo wc_price($cart_subtotal); ?></span>
                </div>
                <?php if ($cart_discount > 0): ?>
                <div class="cart-totals__row cart-totals__discount">
                  <span>تخفیف</span>
                  <span id="cartDiscount">−<?php echo wc_price($cart_discount); ?></span>
                </div>
                <?php endif; ?>
                <div class="cart-totals__row cart-totals__shipping">
                  <span>هزینه ارسال</span>
                  <span>محاسبه در تسویه حساب</span>
                </div>
                <div class="cart-totals__row cart-totals__total">
                  <span>جمع کل</span>
                  <span class="cart-totals__total-price" id="cartTotal"><?php echo wc_price($cart_total); ?></span>
                </div>
                <a href="<?php echo wc_get_checkout_url(); ?>" class="btn-checkout"><?php echo esc_html($checkout_btn); ?></a>
                <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-continue"><?php echo esc_html($continue_btn); ?></a>
              </div>
            </div>

            <!-- Coupon -->
            <div style="margin-top:24px;padding-top:24px;border-top:1px solid #f3f4f6;">
              <button type="button" id="cpToggle" style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:0;background:none;border:none;cursor:pointer;font-size:0.95rem;font-weight:700;color:#111827;">
                کد تخفیف
                <svg id="cpArrow" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
              </button>
              <div id="cpBody" style="display:none;padding-top:16px;">
                <p style="font-size:0.85rem;color:#6b7280;margin:0 0 12px;">اگر کد تخفیف دارید، در این قسمت وارد کنید.</p>
                <form method="post" action="<?php echo esc_url(wc_get_cart_url()); ?>" style="display:flex;gap:8px;">
                  <input type="text" name="coupon_code" placeholder="کد تخفیف را وارد کنید..." style="flex:1;border:1px solid #e5e7eb;border-radius:12px;padding:10px 16px;font-size:0.85rem;background:#f9fafb;outline:none;font-family:Vazirmatn,sans-serif;">
                  <button type="submit" name="apply_coupon" value="1" style="background:#1e3a2f;color:#fff;border:none;border-radius:12px;padding:10px 16px;font-weight:600;font-size:0.85rem;cursor:pointer;white-space:nowrap;font-family:Vazirmatn,sans-serif;">اعمال</button>
                </form>
                <?php foreach ($cart->get_coupons() as $code => $coupon): ?>
                <div style="display:flex;align-items:center;gap:8px;margin-top:12px;background:#f0fdf4;border-radius:12px;padding:10px 16px;color:#16a34a;font-size:0.85rem;font-weight:600;">
                  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                  کد "<?php echo esc_html($code); ?>" اعمال شد!
                  <a href="<?php echo esc_url(add_query_arg('remove_coupon', $code, wc_get_cart_url())); ?>" style="margin-right:auto;color:#ef4444;text-decoration:none;font-size:0.8rem;">[حذف]</a>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Service Bar -->
      <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:20px 24px;margin-top:24px;">
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;" class="svc-bar">
          <div style="display:flex;align-items:center;gap:12px;"><span style="font-size:1.5rem;">🚚</span><div><p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;">ارسال رایگان</p><p style="font-size:0.75rem;color:#6b7280;margin:2px 0 0 0;">برای سفارش‌های بالای [مبلغ]</p></div></div>
          <div style="display:flex;align-items:center;gap:12px;"><span style="font-size:1.5rem;">📞</span><div><p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;">پشتیبانی ۲۴ ساعته</p><p style="font-size:0.75rem;color:#6b7280;margin:2px 0 0 0;">در ۷ روز هفته</p></div></div>
          <div style="display:flex;align-items:center;gap:12px;"><span style="font-size:1.5rem;">🛡️</span><div><p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;">ضمانت بازگشت کالا</p><p style="font-size:0.75rem;color:#6b7280;margin:2px 0 0 0;">تا ۷ روز پس از دریافت</p></div></div>
          <div style="display:flex;align-items:center;gap:12px;"><span style="font-size:1.5rem;">🔒</span><div><p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;">پرداخت امن</p><p style="font-size:0.75rem;color:#6b7280;margin:2px 0 0 0;">پرداخت امن با استاندارد بالا</p></div></div>
        </div>
      </div>

      <!-- Support Banner -->
      <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:24px;display:flex;flex-direction:column;align-items:center;gap:16px;margin-top:24px;" class="sup-wrap">
        <div style="display:flex;align-items:center;gap:16px;">
          <div style="width:48px;height:48px;border-radius:12px;background:#f0f7f4;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">💬</div>
          <div>
            <h3 style="font-weight:700;color:#111827;font-size:1rem;margin:0;"><?php echo esc_html($support_title); ?></h3>
            <p style="font-size:0.85rem;color:#6b7280;margin:2px 0 0 0;"><?php echo esc_html($support_text); ?></p>
          </div>
        </div>
        <a href="tel:<?php echo esc_attr($support_phone); ?>" style="background:#1e3a2f;color:#fff;border:none;border-radius:12px;padding:12px 24px;font-weight:600;font-size:0.9rem;text-decoration:none;white-space:nowrap;display:flex;align-items:center;gap:8px;font-family:Vazirmatn,sans-serif;">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c-.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
          <?php echo esc_html($support_btn); ?>
        </a>
      </div>

    <?php endif; ?>
  </div>
</div>

<!-- Floating Cart Button (Mobile) -->
<?php if (!$cart->is_empty()): ?>
<button id="floatingCartBtn" class="floating-cart-btn" aria-label="سبد خرید">
  <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
  </svg>
  <span class="floating-cart-count"><?php echo $cart_count; ?></span>
</button>

<!-- Floating Cart Drawer -->
<div id="cartDrawer" class="cart-drawer">
  <div class="cart-drawer__backdrop" id="cartDrawerBackdrop"></div>
  <div class="cart-drawer__panel">
    <div class="cart-drawer__header">
      <h3 class="cart-drawer__title">سبد خرید</h3>
      <button class="cart-drawer__close" id="cartDrawerClose" aria-label="بستن">
        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="cart-drawer__body" id="cartDrawerBody">
      <?php foreach ($cart_items as $cart_item_key => $cart_item):
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
        if (!$_product || !$_product->exists() || $cart_item['quantity'] < 1) continue;

        $pname = $_product->get_name();
        $plink = $_product->is_visible() ? get_permalink($product_id) : '';
        $pimg = wp_get_attachment_image_url($_product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail');
        $pprice = $_product->get_price();
        $psub = $pprice * $cart_item['quantity'];
      ?>
      <div class="cart-drawer__item" data-key="<?php echo esc_attr($cart_item_key); ?>">
        <div class="cart-drawer__item-img">
          <?php if ($plink): ?><a href="<?php echo esc_url($plink); ?>"><?php endif; ?>
            <img src="<?php echo esc_url($pimg); ?>" alt="<?php echo esc_attr($pname); ?>">
          <?php if ($plink): ?></a><?php endif; ?>
        </div>
        <div class="cart-drawer__item-info">
          <div class="cart-drawer__item-name">
            <?php if ($plink): ?><a href="<?php echo esc_url($plink); ?>"><?php endif; ?>
              <?php echo esc_html($pname); ?>
            <?php if ($plink): ?></a><?php endif; ?>
          </div>
          <div class="cart-drawer__item-price"><?php echo number_format($psub); ?> تومان</div>
          <div class="cart-drawer__item-actions">
            <div class="qty-control">
              <button class="qty-btn qty-minus" data-key="<?php echo esc_attr($cart_item_key); ?>">−</button>
              <input type="number" class="qty-input" value="<?php echo $cart_item['quantity']; ?>" min="1" data-key="<?php echo esc_attr($cart_item_key); ?>">
              <button class="qty-btn qty-plus" data-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
            </div>
            <button class="cart-drawer__item-remove" data-key="<?php echo esc_attr($cart_item_key); ?>">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="cart-drawer__footer">
      <div class="cart-drawer__totals">
        <div class="cart-drawer__total-row">
          <span>جمع کل</span>
          <span id="drawerCartTotal"><?php echo wc_price($cart_total); ?></span>
        </div>
      </div>
      <a href="<?php echo wc_get_checkout_url(); ?>" class="btn-checkout"><?php echo esc_html($checkout_btn); ?></a>
      <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-continue"><?php echo esc_html($continue_btn); ?></a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php get_footer(); ?>
