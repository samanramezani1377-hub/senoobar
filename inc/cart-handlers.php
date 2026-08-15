<?php
/**
 * Cart AJAX Handlers — Senoobar
 * Compatible with SenoobarCart JS config
 */

// ---- Update Quantity ----
add_action('wp_ajax_senoobar_cart_update_quantity', 'senoobar_cart_update_quantity');
add_action('wp_ajax_nopriv_senoobar_cart_update_quantity', 'senoobar_cart_update_quantity');

function senoobar_cart_update_quantity(): void {
    check_ajax_referer('senoobar_cart_nonce', 'nonce');

    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $quantity      = max(1, intval($_POST['quantity'] ?? 1));

    if (empty($cart_item_key)) {
        wp_send_json_error(['message' => 'Invalid cart item']);
    }

    $cart = WC()->cart;
    $cart->set_quantity($cart_item_key, $quantity, true);
    $cart->calculate_totals();

    // Return updated cart HTML and totals
    $cart_html = '';
    foreach ($cart->get_cart() as $key => $item) {
        $_product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $key);
        if (!$_product || !$_product->exists() || $item['quantity'] < 1) {
            continue;
        }

        $product_name = $_product->get_name();
        $product_permalink = $_product->is_visible() ? $_product->get_permalink($item) : '';
        $thumbnail = $_product->get_image('woocommerce_thumbnail');
        $unit_price = $cart->get_product_price($_product);
        $line_subtotal = $cart->get_product_subtotal($_product, $item['quantity']);

        $cart_html .= '<article class="senoobar-cart-item" data-cart-key="' . esc_attr($key) . '">';

        // Product
        $cart_html .= '<div class="senoobar-cart-product">';
        $cart_html .= '<div class="senoobar-cart-product-image">';
        if ($product_permalink) {
            $cart_html .= '<a href="' . esc_url($product_permalink) . '">' . $thumbnail . '</a>';
        } else {
            $cart_html .= $thumbnail;
        }
        $cart_html .= '</div>';

        $cart_html .= '<div class="senoobar-cart-product-info">';
        if ($product_permalink) {
            $cart_html .= '<a class="senoobar-cart-product-name" href="' . esc_url($product_permalink) . '">' . esc_html($product_name) . '</a>';
        } else {
            $cart_html .= '<span class="senoobar-cart-product-name">' . esc_html($product_name) . '</span>';
        }

        // Variation / metadata
        $variation_data = wc_get_formatted_cart_item_data($item);
        if (!empty($variation_data)) {
            $cart_html .= $variation_data;
        }

        if ($_product->get_sku()) {
            $cart_html .= '<span class="senoobar-product-sku">کد محصول: ' . esc_html($_product->get_sku()) . '</span>';
        }

        $cart_html .= '</div>'; // .senoobar-cart-product-info
        $cart_html .= '</div>'; // .senoobar-cart-product

        // Price
        $cart_html .= '<div class="senoobar-cart-price"><span class="senoobar-mobile-label">قیمت</span><span>' . wp_kses_post($unit_price) . '</span></div>';

        // Quantity
        $cart_html .= '<div class="senoobar-cart-quantity"><span class="senoobar-mobile-label">تعداد</span><div class="senoobar-quantity-control">';
        $cart_html .= '<button type="button" class="senoobar-qty-button senoobar-qty-plus" data-key="' . esc_attr($key) . '" aria-label="افزایش تعداد">+</button>';
        $cart_html .= '<input type="number" class="senoobar-qty-input" value="' . esc_attr($item['quantity']) . '" min="1" step="1" inputmode="numeric" data-key="' . esc_attr($key) . '" aria-label="تعداد محصول" />';
        $cart_html .= '<button type="button" class="senoobar-qty-button senoobar-qty-minus" data-key="' . esc_attr($key) . '" aria-label="کاهش تعداد">−</button>';
        $cart_html .= '</div></div>';

        // Subtotal
        $cart_html .= '<div class="senoobar-cart-subtotal"><span class="senoobar-mobile-label">جمع</span><span>' . wp_kses_post($line_subtotal) . '</span></div>';

        // Remove
        $cart_html .= '<div class="senoobar-cart-remove"><button type="button" class="senoobar-remove-button" data-key="' . esc_attr($key) . '" aria-label="حذف ' . esc_attr($product_name) . '"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg></button></div>';

        $cart_html .= '</article>';
    }

    $subtotal_html = $cart->get_cart_subtotal();
    $total_html = $cart->get_total();
    $discount_html = $cart->get_discount_total() > 0 ? '−' . wc_price($cart->get_discount_total()) : '';
    $count = $cart->get_cart_contents_count();

    wp_send_json_success([
        'cart_html'     => '<div id="senoobar-cart-items">' . $cart_html . '</div>',
        'subtotal_html' => $subtotal_html,
        'total_html'    => $total_html,
        'discount_html' => $discount_html,
        'count'         => $count,
    ]);
}

// ---- Remove Item ----
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

    if ($cart->is_empty()) {
        wp_send_json_success([
            'empty' => true,
        ]);
        return;
    }

    // Return updated cart HTML and totals
    $cart_html = '';
    foreach ($cart->get_cart() as $key => $item) {
        $_product = apply_filters('woocommerce_cart_item_product', $item['data'], $item, $key);
        if (!$_product || !$_product->exists() || $item['quantity'] < 1) {
            continue;
        }

        $product_name = $_product->get_name();
        $product_permalink = $_product->is_visible() ? $_product->get_permalink($item) : '';
        $thumbnail = $_product->get_image('woocommerce_thumbnail');
        $unit_price = $cart->get_product_price($_product);
        $line_subtotal = $cart->get_product_subtotal($_product, $item['quantity']);

        $cart_html .= '<article class="senoobar-cart-item" data-cart-key="' . esc_attr($key) . '">';

        // Product
        $cart_html .= '<div class="senoobar-cart-product">';
        $cart_html .= '<div class="senoobar-cart-product-image">';
        if ($product_permalink) {
            $cart_html .= '<a href="' . esc_url($product_permalink) . '">' . $thumbnail . '</a>';
        } else {
            $cart_html .= $thumbnail;
        }
        $cart_html .= '</div>';

        $cart_html .= '<div class="senoobar-cart-product-info">';
        if ($product_permalink) {
            $cart_html .= '<a class="senoobar-cart-product-name" href="' . esc_url($product_permalink) . '">' . esc_html($product_name) . '</a>';
        } else {
            $cart_html .= '<span class="senoobar-cart-product-name">' . esc_html($product_name) . '</span>';
        }

        // Variation / metadata
        $variation_data = wc_get_formatted_cart_item_data($item);
        if (!empty($variation_data)) {
            $cart_html .= $variation_data;
        }

        if ($_product->get_sku()) {
            $cart_html .= '<span class="senoobar-product-sku">کد محصول: ' . esc_html($_product->get_sku()) . '</span>';
        }

        $cart_html .= '</div>'; // .senoobar-cart-product-info
        $cart_html .= '</div>'; // .senoobar-cart-product

        // Price
        $cart_html .= '<div class="senoobar-cart-price"><span class="senoobar-mobile-label">قیمت</span><span>' . wp_kses_post($unit_price) . '</span></div>';

        // Quantity
        $cart_html .= '<div class="senoobar-cart-quantity"><span class="senoobar-mobile-label">تعداد</span><div class="senoobar-quantity-control">';
        $cart_html .= '<button type="button" class="senoobar-qty-button senoobar-qty-plus" data-key="' . esc_attr($key) . '" aria-label="افزایش تعداد">+</button>';
        $cart_html .= '<input type="number" class="senoobar-qty-input" value="' . esc_attr($item['quantity']) . '" min="1" step="1" inputmode="numeric" data-key="' . esc_attr($key) . '" aria-label="تعداد محصول" />';
        $cart_html .= '<button type="button" class="senoobar-qty-button senoobar-qty-minus" data-key="' . esc_attr($key) . '" aria-label="کاهش تعداد">−</button>';
        $cart_html .= '</div></div>';

        // Subtotal
        $cart_html .= '<div class="senoobar-cart-subtotal"><span class="senoobar-mobile-label">جمع</span><span>' . wp_kses_post($line_subtotal) . '</span></div>';

        // Remove
        $cart_html .= '<div class="senoobar-cart-remove"><button type="button" class="senoobar-remove-button" data-key="' . esc_attr($key) . '" aria-label="حذف ' . esc_attr($product_name) . '"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v5"/><path d="M14 11v5"/></svg></button></div>';

        $cart_html .= '</article>';
    }

    $subtotal_html = $cart->get_cart_subtotal();
    $total_html = $cart->get_total();
    $discount_html = $cart->get_discount_total() > 0 ? '−' . wc_price($cart->get_discount_total()) : '';
    $count = $cart->get_cart_contents_count();

    wp_send_json_success([
        'cart_html'     => '<div id="senoobar-cart-items">' . $cart_html . '</div>',
        'subtotal_html' => $subtotal_html,
        'total_html'    => $total_html,
        'discount_html' => $discount_html,
        'count'         => $count,
    ]);
}

// ---- Apply Coupon ----
add_action('wp_ajax_senoobar_cart_set_quantity', 'senoobar_cart_set_quantity');
add_action('wp_ajax_nopriv_senoobar_cart_set_quantity', 'senoobar_cart_set_quantity');

/**
 * SET the quantity (not add) for a product already in the cart, identified by
 * product_id (and optional variation_id). Returns the new cart contents count
 * plus refreshed fragments so badges update immediately.
 */
function senoobar_cart_set_quantity(): void {
    check_ajax_referer('senoobar_cart_nonce', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    $variation_id = absint($_POST['variation_id'] ?? 0);
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));

    if (! $product_id) {
        wp_send_json_error(['message' => 'Invalid product']);
    }

    $cart = WC()->cart;
    $found = false;

    foreach ($cart->get_cart() as $key => $item) {
        $pid = (int) ($item['product_id'] ?? 0);
        $vid = (int) ($item['variation_id'] ?? 0);
        if ($pid === $product_id && ($variation_id === 0 || $vid === $variation_id)) {
            $cart->set_quantity($key, $quantity);
            $found = true;
            break;
        }
    }

    if (! $found) {
        // Not in cart yet — add it at the requested quantity.
        $cart->add_to_cart($product_id, $quantity, $variation_id);
    }

    $cart->calculate_totals();

    $count = $cart->get_cart_contents_count();

    // Build refreshed fragments for the badges.
    $fragments = [];
    $fragments['.cart-badge[data-cart-count]'] = sprintf(
        '<span class="cart-badge%s" data-cart-count>%d</span>',
        $count > 0 ? '' : ' is-hidden',
        $count
    );
    $fragments['.mbn-badge[data-cart-count]'] = sprintf(
        '<span class="mbn-badge%s" data-cart-count>%d</span>',
        $count > 0 ? '' : ' is-hidden',
        $count
    );

    wp_send_json_success([
        'cart_count' => $count,
        'fragments'  => $fragments,
    ]);
}

add_action('wp_ajax_senoobar_cart_remove_by_product', 'senoobar_cart_remove_by_product');
add_action('wp_ajax_nopriv_senoobar_cart_remove_by_product', 'senoobar_cart_remove_by_product');

/**
 * Remove ALL cart items for a given product_id (used by the product page
 * buy-box "حذف" button, where we only know the product id, not the cart key).
 */
function senoobar_cart_remove_by_product(): void {
    check_ajax_referer('senoobar_cart_nonce', 'nonce');

    $product_id = absint($_POST['product_id'] ?? 0);
    $variation_id = absint($_POST['variation_id'] ?? 0);

    if (! $product_id) {
        wp_send_json_error(['message' => 'Invalid product']);
    }

    $cart = WC()->cart;
    foreach ($cart->get_cart() as $key => $item) {
        $pid = $item['product_id'] ?? 0;
        $vid = $item['variation_id'] ?? 0;
        if ($pid === $product_id && ($variation_id === 0 || $vid === $variation_id)) {
            $cart->remove_cart_item($key);
        }
    }
    $cart->calculate_totals();

    $count = $cart->get_cart_contents_count();
    $fragments = [];
    $fragments['.cart-badge[data-cart-count]'] = sprintf(
        '<span class="cart-badge%s" data-cart-count>%d</span>',
        $count > 0 ? '' : ' is-hidden',
        $count
    );
    $fragments['.mbn-badge[data-cart-count]'] = sprintf(
        '<span class="mbn-badge%s" data-cart-count>%d</span>',
        $count > 0 ? '' : ' is-hidden',
        $count
    );

    wp_send_json_success([
        'cart_count' => $count,
        'fragments'  => $fragments,
    ]);
}

add_action('wp_ajax_senoobar_apply_coupon', 'senoobar_apply_coupon');
add_action('wp_ajax_nopriv_senoobar_apply_coupon', 'senoobar_apply_coupon');

function senoobar_apply_coupon(): void {
    check_ajax_referer('senoobar_cart_nonce', 'nonce');

    $coupon_code = sanitize_text_field($_POST['coupon_code'] ?? '');

    if (empty($coupon_code)) {
        wp_send_json_error(['message' => 'لطفاً کد تخفیف را وارد کنید.']);
    }

    $cart = WC()->cart;
    $cart->apply_coupon($coupon_code);
    $cart->calculate_totals();

    $subtotal_html = $cart->get_cart_subtotal();
    $total_html = $cart->get_total();
    $discount_html = $cart->get_discount_total() > 0 ? '−' . wc_price($cart->get_discount_total()) : '';
    $count = $cart->get_cart_contents_count();

    // Check if coupon was applied successfully
    $applied_coupons = $cart->get_applied_coupons();
    $success = in_array($coupon_code, $applied_coupons);

    if ($success) {
        wp_send_json_success([
            'subtotal_html' => $subtotal_html,
            'total_html'    => $total_html,
            'discount_html' => $discount_html,
            'count'         => $count,
            'message'       => 'کد تخفیف با موفقیت اعمال شد.',
        ]);
    } else {
        wp_send_json_error([
            'message' => 'کد تخفیف معتبر نیست.',
        ]);
    }
}