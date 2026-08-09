<?php
/**
 * Cart Page — Senoobar v2 Design
 * Exact replica of senoobar2 Cart.tsx
 * Uses native WooCommerce cart form for full functionality
 */
defined('ABSPATH') || exit;

get_header();
?>

<div style="background:#f9fafb;min-height:100vh;direction:rtl;font-family:Vazirmatn,sans-serif;">

  <!-- Breadcrumb + Title -->
  <div style="max-width:1280px;margin:0 auto;padding:24px 16px 0;">
    <div style="display:flex;align-items:center;gap:8px;font-size:0.85rem;color:#6b7280;margin-bottom:24px;">
      <a href="<?php echo home_url('/'); ?>" style="color:#6b7280;text-decoration:none;">خانه</a>
      <span>/</span>
      <span style="color:#1e3a2f;font-weight:600;">سبد خرید</span>
    </div>
    <h1 style="font-size:1.8rem;font-weight:800;color:#111827;margin:0 0 4px 0;">سبد خرید شما</h1>
    <p style="font-size:0.9rem;color:#6b7280;margin:0;">
      <?php echo WC()->cart->get_cart_contents_count(); ?> محصول در سبد خرید
    </p>
  </div>

  <div style="max-width:1280px;margin:0 auto;padding:24px 16px 32px;">

    <?php if (WC()->cart->is_empty()): ?>
      <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);text-align:center;padding:60px 20px;">
        <div style="font-size:4rem;margin-bottom:16px;">🛒</div>
        <h3 style="font-size:1.2rem;font-weight:700;color:#374151;margin-bottom:8px;">سبد خرید شما خالی است</h3>
        <p style="font-size:0.9rem;color:#6b7280;margin-bottom:24px;">محصولات مورد نظر خود را انتخاب کنید.</p>
        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" style="background:#1e3a2f;color:#fff;border-radius:12px;padding:12px 32px;font-weight:600;text-decoration:none;display:inline-block;">ادامه خرید</a>
      </div>

    <?php else: ?>

      <form action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>

        <!-- CART TABLE -->
        <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden;margin-bottom:24px;">

          <!-- Desktop Header -->
          <div style="display:none;grid-template-columns:5fr 2fr 2fr 2fr 1fr;gap:16px;padding:12px 24px;background:#f9fafb;border-bottom:1px solid #f3f4f6;font-size:0.85rem;font-weight:600;color:#4b5563;" class="dt-hdr">
            <div style="text-align:right;">محصول</div>
            <div style="text-align:center;">قیمت</div>
            <div style="text-align:center;">تعداد</div>
            <div style="text-align:center;">جمع جزء</div>
            <div style="text-align:center;">حذف</div>
          </div>

          <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item):
            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
            if (!$_product || !$_product->exists() || $cart_item['quantity'] < 1) continue;
            $pname   = $_product->get_name();
            $plink   = $_product->is_visible() ? get_permalink($product_id) : '';
            $pimg    = wp_get_attachment_image_url($_product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail');
            $pprice  = $_product->get_price();
            $psub    = $pprice * $cart_item['quantity'];

            $vtext = '';
            if (!empty($cart_item['variation'])) {
              $p = [];
              foreach ($cart_item['variation'] as $k => $v) {
                $p[] = wc_attribute_label(str_replace('attribute_', '', $k)) . ': ' . esc_html($v);
              }
              $vtext = implode(' | ', $p);
            }
          ?>
            <!-- Desktop Row -->
            <div style="display:none;grid-template-columns:5fr 2fr 2fr 2fr 1fr;gap:16px;align-items:center;padding:20px 24px;border-bottom:1px solid #f3f4f6;" class="dt-row">
              <div style="display:flex;align-items:center;gap:16px;">
                <?php if ($plink): ?><a href="<?php echo esc_url($plink); ?>"><?php endif; ?>
                  <img src="<?php echo esc_url($pimg); ?>" alt="<?php echo esc_attr($pname); ?>" style="width:80px;height:80px;border-radius:12px;object-fit:cover;">
                <?php if ($plink): ?></a><?php endif; ?>
                <div>
                  <?php if ($plink): ?><a href="<?php echo esc_url($plink); ?>" style="text-decoration:none;"><?php endif; ?>
                    <p style="font-size:0.9rem;font-weight:600;color:#111827;margin:0 0 4px 0;"><?php echo esc_html($pname); ?></p>
                  <?php if ($plink): ?></a><?php endif; ?>
                  <?php if ($vtext): ?><p style="font-size:0.78rem;color:#6b7280;margin:0;"><?php echo $vtext; ?></p><?php endif; ?>
                </div>
              </div>
              <div style="text-align:center;">
                <span style="font-weight:600;color:#374151;font-size:0.9rem;"><?php echo number_format($pprice); ?></span>
                <span style="display:block;font-size:0.75rem;color:#9ca3af;">تومان</span>
              </div>
              <div style="display:flex;justify-content:center;">
                <div style="display:flex;align-items:center;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                  <button type="button" onclick="var n=this.nextElementSibling;n.stepDown();this.closest('form').querySelector('button[name=update_cart]').click();" style="width:36px;height:40px;border:none;background:transparent;cursor:pointer;font-size:1.2rem;color:#4b5563;">−</button>
                  <input type="number" name="cart[<?php echo $cart_item_key; ?>][qty]" value="<?php echo $cart_item['quantity']; ?>" min="1" style="width:40px;text-align:center;border:none;font-size:0.85rem;font-weight:700;color:#111827;-moz-appearance:textfield;" onchange="this.closest('form').querySelector('button[name=update_cart]').click();">
                  <button type="button" onclick="var n=this.previousElementSibling;n.stepUp();this.closest('form').querySelector('button[name=update_cart]').click();" style="width:36px;height:40px;border:none;background:transparent;cursor:pointer;font-size:1.2rem;color:#4b5563;">+</button>
                </div>
              </div>
              <div style="text-align:center;">
                <span style="font-weight:700;color:#111827;font-size:0.9rem;"><?php echo number_format($psub); ?></span>
                <span style="display:block;font-size:0.75rem;color:#9ca3af;">تومان</span>
              </div>
              <div style="display:flex;justify-content:center;">
                <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>" style="width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#9ca3af;text-decoration:none;transition:all 0.2s;" onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2'" onmouseout="this.style.color='#9ca3af';this.style.background='transparent'"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></a>
              </div>
            </div>

            <!-- Mobile Row -->
            <div style="display:flex;gap:16px;padding:16px;border-bottom:1px solid #f3f4f6;" class="mb-row">
              <?php if ($plink): ?><a href="<?php echo esc_url($plink); ?>"><?php endif; ?>
                <img src="<?php echo esc_url($pimg); ?>" alt="<?php echo esc_attr($pname); ?>" style="width:80px;height:80px;border-radius:12px;object-fit:cover;flex-shrink:0;">
              <?php if ($plink): ?></a><?php endif; ?>
              <div style="flex:1;min-width:0;">
                <?php if ($plink): ?><a href="<?php echo esc_url($plink); ?>" style="text-decoration:none;"><?php endif; ?>
                  <p style="font-size:0.9rem;font-weight:600;color:#111827;margin:0 0 4px 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html($pname); ?></p>
                <?php if ($plink): ?></a><?php endif; ?>
                <?php if ($vtext): ?><p style="font-size:0.78rem;color:#6b7280;margin:0 0 8px 0;"><?php echo $vtext; ?></p><?php endif; ?>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                  <div style="display:flex;align-items:center;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                    <button type="button" onclick="var n=this.nextElementSibling;n.stepDown();this.closest('form').querySelector('button[name=update_cart]').click();" style="width:32px;height:32px;border:none;background:transparent;cursor:pointer;font-size:1.1rem;color:#4b5563;">−</button>
                    <input type="number" name="cart[<?php echo $cart_item_key; ?>][qty]" value="<?php echo $cart_item['quantity']; ?>" min="1" style="width:32px;text-align:center;border:none;font-size:0.85rem;font-weight:600;color:#111827;-moz-appearance:textfield;" onchange="this.closest('form').querySelector('button[name=update_cart]').click();">
                    <button type="button" onclick="var n=this.previousElementSibling;n.stepUp();this.closest('form').querySelector('button[name=update_cart]').click();" style="width:32px;height:32px;border:none;background:transparent;cursor:pointer;font-size:1.1rem;color:#4b5563;">+</button>
                  </div>
                  <span style="font-weight:700;color:#111827;font-size:0.9rem;"><?php echo number_format($psub); ?></span>
                  <a href="<?php echo esc_url(wc_get_cart_remove_url($cart_item_key)); ?>" style="color:#9ca3af;padding:4px;" onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#9ca3af'"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></a>
                </div>
              </div>
            </div>
          <?php endforeach; ?>

          <!-- Footer -->
          <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 24px;border-top:1px solid #f3f4f6;background:#f9fafb;">
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" style="display:inline-flex;align-items:center;gap:8px;font-size:0.85rem;font-weight:600;color:#4b5563;border:1px solid #e5e7eb;border-radius:12px;padding:10px 20px;background:#fff;text-decoration:none;">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg> ادامه خرید
            </a>
            <button type="submit" name="update_cart" value="1" style="background:#1e3a2f;color:#fff;border:none;border-radius:12px;padding:10px 24px;font-weight:600;font-size:0.85rem;cursor:pointer;">بروزرسانی سبد خرید</button>
          </div>
        </div>
      </form>

      <!-- COUPON + TOTALS -->
      <div style="display:grid;grid-template-columns:1fr;gap:20px;margin-bottom:24px;" class="btm-row">
        <!-- Coupon -->
        <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);overflow:hidden;">
          <button type="button" id="cpToggle" style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:16px 24px;background:none;border:none;cursor:pointer;font-size:1rem;font-weight:700;color:#111827;">
            کد تخفیف
            <svg id="cpArrow" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
          </button>
          <div id="cpBody" style="display:none;padding:0 24px 20px;border-top:1px solid #f3f4f6;">
            <p style="font-size:0.85rem;color:#6b7280;margin:16px 0 12px;">اگر کد تخفیف دارید، در این قسمت وارد کنید.</p>
            <form method="post" action="<?php echo esc_url(wc_get_cart_url()); ?>" style="display:flex;gap:8px;">
              <input type="text" name="coupon_code" placeholder="کد تخفیف را وارد کنید..." style="flex:1;border:1px solid #e5e7eb;border-radius:12px;padding:10px 16px;font-size:0.85rem;background:#f9fafb;outline:none;">
              <button type="submit" name="apply_coupon" value="1" style="background:#1e3a2f;color:#fff;border:none;border-radius:12px;padding:10px 16px;font-weight:600;font-size:0.85rem;cursor:pointer;white-space:nowrap;">اعمال تخفیف</button>
            </form>
            <?php foreach (WC()->cart->get_coupons() as $code => $coupon): ?>
              <div style="display:flex;align-items:center;gap:8px;margin-top:12px;background:#f0fdf4;border-radius:12px;padding:10px 16px;color:#16a34a;font-size:0.85rem;font-weight:600;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                کد "<?php echo esc_html($code); ?>" اعمال شد!
                <a href="<?php echo esc_url(add_query_arg('remove_coupon', $code, wc_get_cart_url())); ?>" style="margin-right:auto;color:#ef4444;text-decoration:none;font-size:0.8rem;">[حذف]</a>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Totals -->
        <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:24px;">
          <h3 style="font-size:1.1rem;font-weight:700;color:#111827;margin:0 0 16px 0;">جمع کل سبد خرید</h3>
          <div style="margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f3f4f6;">
              <span style="font-size:0.85rem;color:#4b5563;">جمع جزء</span>
              <span style="font-weight:600;color:#111827;font-size:0.85rem;"><?php echo wc_price(WC()->cart->get_subtotal()); ?></span>
            </div>
            <?php if (WC()->cart->get_cart_discount_total() > 0): ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f3f4f6;">
              <span style="font-size:0.85rem;color:#4b5563;">تخفیف</span>
              <span style="font-weight:600;color:#16a34a;font-size:0.85rem;">−<?php echo wc_price(WC()->cart->get_cart_discount_total()); ?></span>
            </div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f3f4f6;">
              <span style="font-size:0.85rem;color:#4b5563;">هزینه ارسال</span>
              <span style="font-weight:600;color:#16a34a;font-size:0.85rem;">محاسبه در تسویه حساب</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;">
              <span style="font-weight:700;color:#111827;">جمع کل</span>
              <div>
                <span style="font-size:1.5rem;font-weight:800;color:#1e3a2f;"><?php echo wc_price(WC()->cart->get_total()); ?></span>
                <span style="font-size:0.85rem;color:#6b7280;margin-right:4px;font-weight:400;">تومان</span>
              </div>
            </div>
          </div>
          <a href="<?php echo wc_get_checkout_url(); ?>" style="display:block;text-align:center;background:#1e3a2f;color:#fff;border-radius:12px;padding:14px;font-size:1rem;font-weight:700;text-decoration:none;">ادامه جهت تسویه حساب</a>
        </div>
      </div>

    <?php endif; ?>

    <!-- Service Bar -->
    <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:20px 24px;margin-bottom:24px;">
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;" class="svc-bar">
        <div style="display:flex;align-items:center;gap:12px;"><span style="font-size:1.5rem;">🚚</span><div><p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;">ارسال رایگان</p><p style="font-size:0.75rem;color:#6b7280;margin:2px 0 0 0;">برای سفارش‌های بالای [مبلغ]</p></div></div>
        <div style="display:flex;align-items:center;gap:12px;"><span style="font-size:1.5rem;">📞</span><div><p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;">پشتیبانی ۲۴ ساعته</p><p style="font-size:0.75rem;color:#6b7280;margin:2px 0 0 0;">در ۷ روز هفته</p></div></div>
        <div style="display:flex;align-items:center;gap:12px;"><span style="font-size:1.5rem;">🛡️</span><div><p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;">ضمانت بازگشت کالا</p><p style="font-size:0.75rem;color:#6b7280;margin:2px 0 0 0;">تا ۷ روز پس از دریافت</p></div></div>
        <div style="display:flex;align-items:center;gap:12px;"><span style="font-size:1.5rem;">🔒</span><div><p style="font-size:0.85rem;font-weight:700;color:#111827;margin:0;">پرداخت امن</p><p style="font-size:0.75rem;color:#6b7280;margin:2px 0 0 0;">پرداخت امن با استاندارد بالا</p></div></div>
      </div>
    </div>

    <!-- Cross-Sells / Suggested -->
    <?php
    $sug = [];
    $xs = WC()->cart->get_cross_sells();
    if (!empty($xs)) $sug = array_filter(array_map('wc_get_product', array_slice(array_unique($xs), 0, 4)));
    if (empty($sug)) $sug = wc_get_products(['limit' => 4, 'featured' => true, 'status' => 'publish']);
    ?>
    <?php if (!empty($sug)): ?>
    <div style="margin-bottom:24px;">
      <h2 style="font-size:1.2rem;font-weight:700;color:#111827;margin:0 0 16px 0;">شاید این محصولات را هم بپسندید</h2>
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;" class="sug-grid">
        <?php foreach ($sug as $sp): if (!$sp) continue; ?>
          <a href="<?php echo get_permalink($sp->get_id()); ?>" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.06);border:1px solid #f3f4f6;text-decoration:none;display:block;">
            <div style="position:relative;overflow:hidden;"><?php echo $sp->get_image('medium', ['style' => 'width:100%;height:180px;object-fit:cover;', 'loading' => 'lazy']); ?></div>
            <div style="padding:12px;">
              <p style="font-size:0.85rem;font-weight:600;color:#111827;margin:0 0 8px 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo esc_html($sp->get_name()); ?></p>
              <div style="display:flex;align-items:center;justify-content:space-between;">
                <p style="font-size:0.85rem;font-weight:700;color:#1e3a2f;margin:0;"><?php echo $sp->get_price_html(); ?></p>
                <span style="width:32px;height:32px;border-radius:12px;background:#1e3a2f;display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;" onclick="event.preventDefault();event.stopPropagation();window.location='<?php echo esc_url(add_query_arg('add-to-cart', $sp->get_id(), wc_get_cart_url())); ?>';"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Support Banner -->
    <div style="background:#fff;border-radius:16px;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:24px;display:flex;flex-direction:column;align-items:center;gap:16px;margin-bottom:16px;" class="sup-wrap">
      <div style="display:flex;align-items:center;gap:16px;">
        <div style="width:48px;height:48px;border-radius:12px;background:#f0f7f4;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;">💬</div>
        <div>
          <h3 style="font-weight:700;color:#111827;font-size:1rem;margin:0;">سوالی دارید؟ ما در کنار شما هستیم</h3>
          <p style="font-size:0.85rem;color:#6b7280;margin:2px 0 0 0;">برای راهنمایی در خرید یا پیگیری سفارش‌تان با پشتیبانی ما تماس بگیرید.</p>
        </div>
      </div>
      <button style="background:#1e3a2f;color:#fff;border:none;border-radius:12px;padding:12px 24px;font-weight:600;font-size:0.9rem;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:8px;">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
        تماس با پشتیبانی
      </button>
    </div>
  </div>
</div>

<style>
@media (min-width:768px){.dt-hdr{display:grid!important}.dt-row{display:grid!important}.mb-row{display:none!important}.btm-row{grid-template-columns:1fr 1fr!important}.svc-bar{grid-template-columns:repeat(4,1fr)!important}.sug-grid{grid-template-columns:repeat(4,1fr)!important}.sup-wrap{flex-direction:row!important;justify-content:space-between!important}}
input[type=number]::-webkit-outer-spin-button,input[type=number]::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
</style>

<script>
(function(){
  var t=document.getElementById('cpToggle'),b=document.getElementById('cpBody'),a=document.getElementById('cpArrow');
  if(t&&b){
    <?php if (!empty(WC()->cart->get_coupons())): ?>b.style.display='block';if(a)a.style.transform='rotate(180deg)';<?php endif; ?>
    t.addEventListener('click',function(){var o=b.style.display!=='none';b.style.display=o?'none':'block';if(a)a.style.transform=o?'':'rotate(180deg)';});
  }
})();
</script>

<?php get_footer(); ?>
