<?php
/**
 * Cart Page — Senoobar v2 Design
 * Based on senoobar2 Figma design — Cart.tsx
 * Deep Green Palette (#1e3a2f) + Vazirmatn
 *
 * OVERRIDES: WooCommerce cart.php template
 */
defined('ABSPATH') || exit;

get_header();

wp_enqueue_style('senoobar-cart', get_template_directory_uri() . '/assets/css/cart.css', [], SENOOBAR_VERSION);

// Get suggested products (cross-sells or random featured)
$suggested_ids = [];
if (WC()->cart) {
    $cart_items = WC()->cart->get_cart();
    $cross_sells = [];
    foreach ($cart_items as $item) {
        $product = $item['data'];
        $cross_sells = array_merge($cross_sells, $product->get_cross_sell_ids());
    }
    $cross_sells = array_unique($cross_sells);
    if (!empty($cross_sells)) {
        $suggested_ids = array_slice($cross_sells, 0, 5);
    } else {
        // Fallback: random featured products
        $featured = wc_get_products([
            'limit'   => 5,
            'featured' => true,
            'status'  => 'publish',
        ]);
        foreach ($featured as $fp) {
            $suggested_ids[] = $fp->get_id();
        }
    }
}
$suggested = array_filter(array_map('wc_get_product', $suggested_ids));
?>

<div class="cart-page">

    <div class="cart-container" style="padding-top:24px;padding-bottom:24px;">
        <!-- Breadcrumb -->
        <nav class="cart-breadcrumb">
            <a href="<?php echo home_url('/'); ?>">خانه</a>
            <span>/</span>
            <span class="cart-breadcrumb--current">سبد خرید</span>
        </nav>
    </div>

    <div class="cart-container">
        <div class="cart-page-header">
            <h1 class="cart-page-title">سبد خرید شما</h1>
            <p class="cart-page-count">
                <?php echo WC()->cart->get_cart_contents_count(); ?> محصول در سبد خرید
            </p>
        </div>

        <?php if (WC()->cart->is_empty()): ?>
            <!-- Empty Cart -->
            <div class="cart-table-wrap">
                <div class="cart-empty">
                    <div class="cart-empty-icon">🛒</div>
                    <h3 class="cart-empty-title">سبد خرید شما خالی است</h3>
                    <p class="cart-empty-text">محصولات مورد نظر خود را انتخاب کنید.</p>
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="cart-btn-primary" style="padding:12px 32px;">ادامه خرید</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Cart Table -->
            <div class="cart-table-wrap">
                <!-- Header -->
                <div class="cart-table-header">
                    <div style="text-align:right;">محصول</div>
                    <div>قیمت</div>
                    <div>تعداد</div>
                    <div>جمع جزء</div>
                    <div>حذف</div>
                </div>

                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
                    $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                    $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                    $product_name    = $_product->get_name();
                    $product_price   = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                    $product_permalink = $_product->is_visible() ? get_permalink($product_id) : '';
                    $thumbnail       = $_product->get_image('thumbnail', ['class' => 'cart-item-desktop__img', 'loading' => 'lazy']);
                    $variant_text    = '';
                    if (!empty($cart_item['variation'])) {
                        $variant_parts = [];
                        foreach ($cart_item['variation'] as $key => $val) {
                            $label = wc_attribute_label(str_replace('attribute_', '', $key));
                            $variant_parts[] = $label . ': ' . $val;
                        }
                        $variant_text = implode(' | ', $variant_parts);
                    }
                    $line_total = apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
                ?>
                    <!-- Desktop Row -->
                    <div class="cart-item cart-item-desktop">
                        <div class="cart-item-desktop__product">
                            <?php if ($product_permalink): ?><a href="<?php echo esc_url($product_permalink); ?>"><?php endif; ?>
                                <?php echo $thumbnail; ?>
                            <?php if ($product_permalink): ?></a><?php endif; ?>
                            <div>
                                <?php if ($product_permalink): ?><a href="<?php echo esc_url($product_permalink); ?>" style="text-decoration:none;"><?php endif; ?>
                                    <p class="cart-item-desktop__name"><?php echo esc_html($product_name); ?></p>
                                <?php if ($product_permalink): ?></a><?php endif; ?>
                                <?php if ($variant_text): ?><p class="cart-item-desktop__variant"><?php echo esc_html($variant_text); ?></p><?php endif; ?>
                            </div>
                        </div>
                        <div class="cart-item-desktop__price">
                            <span class="cart-item-desktop__price-val"><?php echo wc_price($_product->get_price()); ?></span>
                            <span class="cart-item-desktop__price-unit">تومان</span>
                        </div>
                        <div class="cart-item-desktop__qty">
                            <div class="cart-qty">
                                <button type="button" class="cart-qty__btn cart-qty-minus" data-key="<?php echo esc_attr($cart_item_key); ?>">−</button>
                                <span class="cart-qty__val"><?php echo $cart_item['quantity']; ?></span>
                                <button type="button" class="cart-qty__btn cart-qty-plus" data-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
                            </div>
                        </div>
                        <div class="cart-item-desktop__subtotal">
                            <span class="cart-item-desktop__subtotal-val"><?php echo wc_price($_product->get_price() * $cart_item['quantity']); ?></span>
                            <span class="cart-item-desktop__subtotal-unit">تومان</span>
                        </div>
                        <div class="cart-item-desktop__remove">
                            <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>" class="cart-remove-btn" aria-label="حذف">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Mobile Row -->
                    <div class="cart-item cart-item-mobile">
                        <?php if ($product_permalink): ?><a href="<?php echo esc_url($product_permalink); ?>"><?php endif; ?>
                            <img src="<?php echo esc_url(wp_get_attachment_image_url($_product->get_image_id(), 'thumbnail')); ?>" alt="<?php echo esc_attr($product_name); ?>" class="cart-item-mobile__img" loading="lazy"/>
                        <?php if ($product_permalink): ?></a><?php endif; ?>
                        <div class="cart-item-mobile__info">
                            <?php if ($product_permalink): ?><a href="<?php echo esc_url($product_permalink); ?>" style="text-decoration:none;"><?php endif; ?>
                                <p class="cart-item-mobile__name"><?php echo esc_html($product_name); ?></p>
                            <?php if ($product_permalink): ?></a><?php endif; ?>
                            <?php if ($variant_text): ?><p class="cart-item-mobile__variant"><?php echo esc_html($variant_text); ?></p><?php endif; ?>
                            <div class="cart-item-mobile__actions">
                                <div class="cart-qty">
                                    <button type="button" class="cart-qty__btn cart-qty-minus" data-key="<?php echo esc_attr($cart_item_key); ?>">−</button>
                                    <span class="cart-qty__val"><?php echo $cart_item['quantity']; ?></span>
                                    <button type="button" class="cart-qty__btn cart-qty-plus" data-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
                                </div>
                                <span class="cart-item-mobile__total"><?php echo wc_price($_product->get_price() * $cart_item['quantity']); ?></span>
                                <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>" class="cart-item-mobile__remove" aria-label="حذف">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Footer -->
                <div class="cart-table-footer">
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="cart-btn-outline">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        ادامه خرید
                    </a>
                    <button type="button" class="cart-btn-primary" id="cartUpdateBtn">بروزرسانی سبد خرید</button>
                </div>
            </div>

            <!-- Coupon + Totals -->
            <div class="cart-bottom-grid">
                <!-- Coupon -->
                <div class="cart-coupon-block" id="cartCouponBlock">
                    <button class="cart-coupon-toggle" id="cartCouponToggle" type="button">
                        <span>کد تخفیف</span>
                        <svg class="cart-coupon-arrow" id="cartCouponArrow" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <div class="cart-coupon-body" id="cartCouponBody" style="display:none;">
                        <p class="cart-coupon-hint">اگر کد تخفیف دارید، در این قسمت وارد کنید.</p>
                        <div class="cart-coupon-input-group">
                            <input type="text" name="coupon_code" class="cart-coupon-input" placeholder="کد تخفیف را وارد کنید..." id="couponCode"/>
                            <button type="button" class="cart-btn-primary" id="applyCoupon">اعمال تخفیف</button>
                        </div>
                        <div class="cart-coupon-success" id="couponMsg" style="display:none;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>کد تخفیف با موفقیت اعمال شد!</span>
                        </div>
                    </div>
                </div>

                <!-- Totals -->
                <div class="cart-totals-block">
                    <h3 class="cart-totals-title">جمع کل سبد خرید</h3>
                    <div class="cart-totals-rows">
                        <div class="cart-totals-row">
                            <span class="cart-totals-label">جمع جزء</span>
                            <span class="cart-totals-value"><?php echo wc_price(WC()->cart->get_subtotal()); ?></span>
                        </div>
                        <?php if (WC()->cart->get_cart_discount_total() > 0): ?>
                        <div class="cart-totals-row">
                            <span class="cart-totals-label">تخفیف</span>
                            <span class="cart-totals-value cart-totals-value--discount">−<?php echo wc_price(WC()->cart->get_cart_discount_total()); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (WC()->cart->needs_shipping()): ?>
                        <div class="cart-totals-row">
                            <span class="cart-totals-label">هزینه ارسال</span>
                            <span class="cart-totals-value cart-totals-value--free">محاسبه در تسویه حساب</span>
                        </div>
                        <?php endif; ?>
                        <div class="cart-totals-row cart-totals-divider">
                            <span class="cart-totals-label">جمع کل</span>
                            <div>
                                <span class="cart-totals-value"><?php echo wc_price(WC()->cart->get_total()); ?></span>
                                <span class="cart-totals-unit">تومان</span>
                            </div>
                        </div>
                    </div>
                    <a href="<?php echo wc_get_checkout_url(); ?>" class="cart-checkout-btn" style="display:block;text-align:center;text-decoration:none;">
                        ادامه جهت تسویه حساب
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($suggested)): ?>
    <!-- Suggested Products -->
    <div class="cart-container">
        <div class="cart-suggested">
            <div class="cart-suggested-header">
                <h2 class="cart-suggested-title">شاید این محصولات را هم بپسندید</h2>
            </div>
            <div class="cart-suggested-grid">
                <?php foreach (array_slice($suggested, 0, 4) as $sp): ?>
                    <a href="<?php echo esc_url(get_permalink($sp->get_id())); ?>" class="cart-suggestion-card">
                        <div class="cart-suggestion-card__img-wrap">
                            <?php echo $sp->get_image('medium', ['class' => 'cart-suggestion-card__img', 'loading' => 'lazy']); ?>
                            <span class="cart-suggestion-card__wishlist" onclick="event.preventDefault();">♥</span>
                        </div>
                        <div class="cart-suggestion-card__body">
                            <p class="cart-suggestion-card__name"><?php echo esc_html($sp->get_name()); ?></p>
                            <div class="cart-suggestion-card__footer">
                                <p class="cart-suggestion-card__price"><?php echo $sp->get_price_html(); ?></p>
                                <span class="cart-suggestion-card__add" onclick="event.preventDefault();event.stopPropagation();">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                </span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Service Bar -->
    <div class="cart-container">
        <div class="cart-service-bar">
            <div class="cart-service-grid">
                <div class="cart-service-item">
                    <span class="cart-service-icon">🚚</span>
                    <div><p class="cart-service-title">ارسال رایگان</p><p class="cart-service-desc">برای سفارش‌های بالای [مبلغ]</p></div>
                </div>
                <div class="cart-service-item">
                    <span class="cart-service-icon">📞</span>
                    <div><p class="cart-service-title">پشتیبانی ۲۴ ساعته</p><p class="cart-service-desc">در ۷ روز هفته</p></div>
                </div>
                <div class="cart-service-item">
                    <span class="cart-service-icon">🛡️</span>
                    <div><p class="cart-service-title">ضمانت بازگشت کالا</p><p class="cart-service-desc">تا ۷ روز پس از دریافت</p></div>
                </div>
                <div class="cart-service-item">
                    <span class="cart-service-icon">🔒</span>
                    <div><p class="cart-service-title">پرداخت امن</p><p class="cart-service-desc">پرداخت امن با استاندارد بالا</p></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Support Banner -->
    <div class="cart-container">
        <div class="cart-support-banner">
            <div class="cart-support-left">
                <div class="cart-support-icon-wrap">💬</div>
                <div>
                    <h3 class="cart-support-title">سوالی دارید؟ ما در کنار شما هستیم</h3>
                    <p class="cart-support-desc">برای راهنمایی در خرید یا پیگیری سفارش‌تان با پشتیبانی ما تماس بگیرید.</p>
                </div>
            </div>
            <button class="cart-btn-primary" style="padding:12px 24px;white-space:nowrap;display:flex;align-items:center;gap:8px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                تماس با پشتیبانی
            </button>
        </div>
    </div>
</div>

<script>
(function(){
    // Coupon toggle
    var couponToggle = document.getElementById('cartCouponToggle');
    var couponBody = document.getElementById('cartCouponBody');
    var couponArrow = document.getElementById('cartCouponArrow');
    if (couponToggle && couponBody) {
        couponToggle.addEventListener('click', function(){
            var isOpen = couponBody.style.display !== 'none';
            couponBody.style.display = isOpen ? 'none' : 'block';
            if (couponArrow) couponArrow.classList.toggle('cart-coupon-arrow--open', !isOpen);
        });
    }
    // Cart update button
    var updateBtn = document.getElementById('cartUpdateBtn');
    if (updateBtn) {
        updateBtn.addEventListener('click', function(){
            document.querySelector('input[name="update_cart"]')?.click();
        });
    }
    // Qty buttons (can be extended with AJAX)
})();
</script>

<?php get_footer(); ?>
