<?php
/**
 * Cart AJAX Handlers — Senoobar
 */

add_action('wp_ajax_senoobar_cart_update_quantity', 'senoobar_cart_update_quantity');
add_action('wp_ajax_nopriv_senoobar_cart_update_quantity', 'senoobar_cart_update_quantity');

function senoobar_cart_update_quantity(): void {
    check_ajax_referer('senoobar_cart_nonce', 'nonce');

    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    if (empty($cart_item_key)) {
        wp_send_json_error(['message' => 'Invalid cart item']);
    }

    $cart = WC()->cart;
    $cart->set_quantity($cart_item_key, $quantity, true);
    $cart->calculate_totals();

    ob_start();
    ?>
    <!-- Cart Items -->
    <div class="cart-items">
        <?php
        foreach ($cart->get_cart() as $key => $item) {
            $_product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $item['product_id'], $item, $key);
            if (!$_product || !$_product->exists() || $item['quantity'] < 1) continue;

            $pname = $_product->get_name();
            $plink = $_product->is_visible() ? get_permalink($product_id) : '';
            $pimg = wp_get_attachment_image_url($_product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail');
            $pprice = $_product->get_price();
            $psub = $pprice * $item['quantity'];

            $vtext = '';
            if (!empty($item['variation'])) {
                $p = [];
                foreach ($item['variation'] as $k => $v) {
                    $p[] = wc_attribute_label(str_replace('attribute_', '', $k)) . ': ' . esc_html($v);
                }
                $vtext = implode(' | ', $p);
            }
        ?>
        <div class="cart-item" data-key="<?php echo esc_attr($key); ?>">
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
                        <button class="qty-btn qty-minus" data-key="<?php echo esc_attr($key); ?>" aria-label="کاهش">−</button>
                        <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" data-key="<?php echo esc_attr($key); ?>">
                        <button class="qty-btn qty-plus" data-key="<?php echo esc_attr($key); ?>" aria-label="افزایش">+</button>
                    </div>
                    <button class="cart-item__remove" data-key="<?php echo esc_attr($key); ?>" aria-label="حذف">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <!-- Cart Totals -->
    <div class="cart-totals">
        <div class="cart-totals__row">
            <span>جمع جزء</span>
            <span><?php echo wc_price($cart->get_subtotal()); ?></span>
        </div>
        <?php if ($cart->get_cart_discount_total() > 0): ?>
        <div class="cart-totals__row cart-totals__discount">
            <span>تخفیف</span>
            <span>−<?php echo wc_price($cart->get_cart_discount_total()); ?></span>
        </div>
        <?php endif; ?>
        <div class="cart-totals__row cart-totals__shipping">
            <span>هزینه ارسال</span>
            <span>محاسبه در تسویه حساب</span>
        </div>
        <div class="cart-totals__row cart-totals__total">
            <span>جمع کل</span>
            <span class="cart-totals__total-price"><?php echo wc_price($cart->get_total()); ?></span>
        </div>
        <a href="<?php echo wc_get_checkout_url(); ?>" class="btn-checkout">ادامه جهت تسویه حساب</a>
        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-continue">ادامه خرید</a>
    </div>
    <?php

    wp_send_json_success([
        'cart_items' => ob_get_clean(),
        'count' => $cart->get_cart_contents_count(),
        'subtotal' => $cart->get_subtotal(),
        'total' => $cart->get_total(),
    ]);
}

add_action('wp_ajax_senoobar_cart_remove_item', 'senoobar_cart_remove_item');
add_action('wp_ajax_nopriv_senoobar_cart_remove_item', 'senoobar_cart_remove_item');

function senoobar_cart_remove_item(): void {
    check_ajax_referer('senoobar_cart_nonce', 'nonce');

    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');

    if (empty($cart_item_key)) {
        wp_send_json_error(['message' => 'Invalid cart item']);
    }

    $cart = WC()->cart;
    $cart->remove_cart_item($cart_item_key);
    $cart->calculate_totals();

    ob_start();
    ?>
    <!-- Cart Items -->
    <div class="cart-items">
        <?php
        foreach ($cart->get_cart() as $key => $item) {
            $_product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $key);
            $product_id = apply_filters('woocommerce_cart_item_product_id', $item['product_id'], $item, $key);
            if (!$_product || !$_product->exists() || $item['quantity'] < 1) continue;

            $pname = $_product->get_name();
            $plink = $_product->is_visible() ? get_permalink($product_id) : '';
            $pimg = wp_get_attachment_image_url($_product->get_image_id(), 'thumbnail') ?: wc_placeholder_img_src('thumbnail');
            $pprice = $_product->get_price();
            $psub = $pprice * $item['quantity'];

            $vtext = '';
            if (!empty($item['variation'])) {
                $p = [];
                foreach ($item['variation'] as $k => $v) {
                    $p[] = wc_attribute_label(str_replace('attribute_', '', $k)) . ': ' . esc_html($v);
                }
                $vtext = implode(' | ', $p);
            }
        ?>
        <div class="cart-item" data-key="<?php echo esc_attr($key); ?>">
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
                        <button class="qty-btn qty-minus" data-key="<?php echo esc_attr($key); ?>" aria-label="کاهش">−</button>
                        <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" min="1" data-key="<?php echo esc_attr($key); ?>">
                        <button class="qty-btn qty-plus" data-key="<?php echo esc_attr($key); ?>" aria-label="افزایش">+</button>
                    </div>
                    <button class="cart-item__remove" data-key="<?php echo esc_attr($key); ?>" aria-label="حذف">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <!-- Cart Totals -->
    <div class="cart-totals">
        <div class="cart-totals__row">
            <span>جمع جزء</span>
            <span><?php echo wc_price($cart->get_subtotal()); ?></span>
        </div>
        <?php if ($cart->get_cart_discount_total() > 0): ?>
        <div class="cart-totals__row cart-totals__discount">
            <span>تخفیف</span>
            <span>−<?php echo wc_price($cart->get_cart_discount_total()); ?></span>
        </div>
        <?php endif; ?>
        <div class="cart-totals__row cart-totals__shipping">
            <span>هزینه ارسال</span>
            <span>محاسبه در تسویه حساب</span>
        </div>
        <div class="cart-totals__row cart-totals__total">
            <span>جمع کل</span>
            <span class="cart-totals__total-price"><?php echo wc_price($cart->get_total()); ?></span>
        </div>
        <a href="<?php echo wc_get_checkout_url(); ?>" class="btn-checkout">ادامه جهت تسویه حساب</a>
        <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="btn-continue">ادامه خرید</a>
    </div>
    <?php

    wp_send_json_success([
        'cart_items' => ob_get_clean(),
        'count' => $cart->get_cart_contents_count(),
        'subtotal' => $cart->get_subtotal(),
        'total' => $cart->get_total(),
        'empty' => $cart->is_empty(),
    ]);
}
