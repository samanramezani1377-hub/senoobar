<?php
/**
 * Senoobar — Wishlist engine.
 *
 * Storage strategy:
 *  - Logged-in user  -> user meta 'senoobar_wishlist' (array of product IDs).
 *  - Guest           -> handled client-side in localStorage (the server only
 *                       merges/returns user data when logged in).
 *
 * AJAX endpoints (wp_ajax_ / wp_ajax_nopriv_):
 *  - senoobar_wishlist_toggle   { product_id }
 *  - senoobar_wishlist_get      (returns the wishlist product IDs)
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
    return;
}

/* ──────────────────────────────────────────────────────────────
 * Helpers
 * ────────────────────────────────────────────────────────────── */

function senoobar_wishlist_ids(): array {
    if ( is_user_logged_in() ) {
        $ids = get_user_meta( get_current_user_id(), 'senoobar_wishlist', true );
        return is_array( $ids ) ? array_values( array_unique( array_map( 'intval', $ids ) ) ) : [];
    }
    return [];
}

function senoobar_wishlist_save( array $ids ): void {
    if ( ! is_user_logged_in() ) {
        return;
    }
    $ids = array_values( array_unique( array_map( 'intval', $ids ) ) );
    update_user_meta( get_current_user_id(), 'senoobar_wishlist', $ids );
}

function senoobar_wishlist_add( int $product_id ): bool {
    if ( ! wc_get_product( $product_id ) ) {
        return false;
    }
    $ids = senoobar_wishlist_ids();
    if ( ! in_array( $product_id, $ids, true ) ) {
        $ids[] = $product_id;
        senoobar_wishlist_save( $ids );
    }
    return true;
}

function senoobar_wishlist_remove( int $product_id ): void {
    $ids = senoobar_wishlist_ids();
    $ids = array_values( array_diff( $ids, [ $product_id ] ) );
    senoobar_wishlist_save( $ids );
}

/* ──────────────────────────────────────────────────────────────
 * AJAX — toggle (add or remove) a product
 * ────────────────────────────────────────────────────────────── */

function senoobar_wishlist_toggle_handler() {
    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    if ( ! $product_id ) {
        wp_send_json_error( [ 'message' => 'شناسه محصول نامعتبر است.' ] );
    }

    if ( ! is_user_logged_in() ) {
        // Guests keep their list client-side; we just echo back the id so the
        // front-end can update localStorage.
        wp_send_json_success( [
            'in_wishlist' => true,
            'product_id'  => $product_id,
            'logged_in'   => false,
        ] );
    }

    $ids      = senoobar_wishlist_ids();
    $removed  = in_array( $product_id, $ids, true );

    if ( $removed ) {
        senoobar_wishlist_remove( $product_id );
    } else {
        senoobar_wishlist_add( $product_id );
    }

    wp_send_json_success( [
        'in_wishlist' => ! $removed,
        'product_id'  => $product_id,
        'logged_in'   => true,
        'count'       => count( senoobar_wishlist_ids() ),
    ] );
}
add_action( 'wp_ajax_senoobar_wishlist_toggle', 'senoobar_wishlist_toggle_handler' );
add_action( 'wp_ajax_nopriv_senoobar_wishlist_toggle', 'senoobar_wishlist_toggle_handler' );

/* ──────────────────────────────────────────────────────────────
 * AJAX — get the wishlist product IDs (server truth for logged-in)
 * ────────────────────────────────────────────────────────────── */

function senoobar_wishlist_get_handler() {
    wp_send_json_success( [
        'ids'       => senoobar_wishlist_ids(),
        'logged_in' => is_user_logged_in(),
    ] );
}
add_action( 'wp_ajax_senoobar_wishlist_get', 'senoobar_wishlist_get_handler' );
add_action( 'wp_ajax_nopriv_senoobar_wishlist_get', 'senoobar_wishlist_get_handler' );

/* ──────────────────────────────────────────────────────────────
 * AJAX — render full product cards for a list of IDs
 * Used by the wishlist page (guest localStorage + logged-in user).
 * ────────────────────────────────────────────────────────────── */

function senoobar_wishlist_render_handler() {
    $ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : [];
    $ids = array_values( array_unique( array_filter( $ids ) ) );

    $items = [];
    foreach ( $ids as $pid ) {
        $product = wc_get_product( $pid );
        if ( ! $product ) {
            continue;
        }
        $image_id = $product->get_image_id();
        $items[] = [
            'id'       => $pid,
            'name'     => $product->get_name(),
            'permalink'=> get_permalink( $pid ),
            'image'    => $image_id ? wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) : wc_placeholder_img_src(),
            'price'    => $product->get_price(),
            'regular_price' => $product->get_regular_price(),
            'sale_price'    => $product->get_sale_price(),
            'in_stock' => $product->is_in_stock(),
            'add_to_cart_url' => $product->add_to_cart_url(),
            'cats'     => wp_get_post_terms( $pid, 'product_cat', [ 'fields' => 'names' ] ),
        ];
    }

    wp_send_json_success( [ 'items' => $items ] );
}
add_action( 'wp_ajax_senoobar_wishlist_render', 'senoobar_wishlist_render_handler' );
add_action( 'wp_ajax_nopriv_senoobar_wishlist_render', 'senoobar_wishlist_render_handler' );

/* ──────────────────────────────────────────────────────────────
 * AJAX — add a product to the cart (used from the wishlist page)
 * ────────────────────────────────────────────────────────────── */

function senoobar_wishlist_add_to_cart_handler() {
    $product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
    $qty        = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;
    if ( ! $qty ) { $qty = 1; }

    $product = wc_get_product( $product_id );
    if ( ! $product ) {
        wp_send_json_error( [ 'message' => 'محصول یافت نشد.' ] );
    }
    if ( ! $product->is_in_stock() ) {
        wp_send_json_error( [ 'message' => 'محصول ناموجود است.' ] );
    }

    $added = WC()->cart->add_to_cart( $product_id, $qty );
    if ( ! $added ) {
        wp_send_json_error( [ 'message' => 'افزودن به سبد ناموفق بود.' ] );
    }

    wp_send_json_success( [
        'message'     => 'به سبد خرید اضافه شد.',
        'cart_count'  => WC()->cart->get_cart_contents_count(),
        'cart_total'  => WC()->cart->get_cart_subtotal(),
    ] );
}
add_action( 'wp_ajax_senoobar_add_to_cart', 'senoobar_wishlist_add_to_cart_handler' );
add_action( 'wp_ajax_nopriv_senoobar_add_to_cart', 'senoobar_wishlist_add_to_cart_handler' );

/* ──────────────────────────────────────────────────────────────
 * 6. Heart button on product cards (shop / archive loops)
 * ────────────────────────────────────────────────────────────── */

add_action( 'woocommerce_before_shop_loop_item_title', function () {
    global $product;
    if ( ! is_a( $product, 'WC_Product' ) ) {
        return;
    }
    $pid = $product->get_id();
    echo '<button type="button" class="snb-card-heart" data-wishlist-btn="' . esc_attr( $pid ) . '" aria-label="افزودن به علاقه‌مندی">';
    echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>';
    echo '</button>';
}, 5 );
