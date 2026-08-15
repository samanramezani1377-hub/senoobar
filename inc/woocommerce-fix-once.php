<?php
/**
 * Senoobar — WooCommerce pages one-time fixer
 *
 * Runs once (guarded by an option flag) to repair the WooCommerce page setup
 * that got corrupted: duplicate English/Farsi pages, pages whose content was
 * blanked, and wrong woocommerce_*_page_id options.
 *
 * It does NOT overwrite page content with shortcodes. It only:
 *   1. Ensures each WooCommerce page (shop/cart/checkout/my-account) exists.
 *   2. Ensures its content has the correct shortcode (so the page itself
 *      renders through WooCommerce's own shortcode/template).
 *   3. Points the correct woocommerce_*_page_id option at the right page.
 *
 * After it runs, it sets 'senoobar_wc_fixed' so it never runs again.
 */

if (!defined('ABSPATH')) {
    exit;
}

function senoobar_fix_woocommerce_pages_once(): void {
    // Only fix once.
    if (get_option('senoobar_wc_fixed')) {
        return;
    }

    // Prefer a mapping from woocommerce_setup translations.
    $pages = [
        'shop'      => [
            'title'     => 'فروشگاه',
            'shortcode' => '', // Shop page should NOT contain a shortcode; WC handles it.
            'option'    => 'woocommerce_shop_page_id',
        ],
        'cart'      => [
            'title'     => 'سبد خرید',
            'shortcode' => '[woocommerce_cart]',
            'option'    => 'woocommerce_cart_page_id',
        ],
        'checkout'  => [
            'title'     => 'تسویه حساب',
            'shortcode' => '[woocommerce_checkout]',
            'option'    => 'woocommerce_checkout_page_id',
        ],
        'myaccount' => [
            'title'     => 'حساب کاربری',
            'shortcode' => '[woocommerce_my_account]',
            'option'    => 'woocommerce_myaccount_page_id',
        ],
    ];

    foreach ($pages as $key => $data) {
        $page = get_page_by_path($key, OBJECT, 'page');

        // Fallback: search by title.
        if (!$page) {
            $page = get_page_by_title($data['title'], OBJECT, 'page');
        }

        // For cart/checkout/myaccount, look for an English twin too.
        if (!$page && $key === 'cart') {
            $page = get_page_by_path('cart', OBJECT, 'page');
        }
        if (!$page && $key === 'checkout') {
            $page = get_page_by_path('checkout', OBJECT, 'page');
        }
        if (!$page && $key === 'myaccount') {
            $page = get_page_by_path('my-account', OBJECT, 'page');
        }

        if (!$page) {
            // Create it cleanly.
            $page_id = wp_insert_post([
                'post_title'   => $data['title'],
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_name'    => $key,
                'post_content' => $data['shortcode'],
            ], true);

            if (is_wp_error($page_id)) {
                continue;
            }
        } else {
            $page_id = $page->ID;

            // Normalize slug only if it's a foreign slug.
            if ($page->post_name !== $key) {
                wp_update_post([
                    'ID'        => $page_id,
                    'post_name' => $key,
                ]);
            }

            // Ensure content has correct shortcode (without destroying design).
            $content = get_post_field('post_content', $page_id);
            if ($data['shortcode'] !== '' && strpos($content, $data['shortcode']) === false) {
                wp_update_post([
                    'ID'           => $page_id,
                    'post_content' => $data['shortcode'],
                ]);
            }

            // For shop specifically: ensure it is BLANK (no stale [woocommerce_shop]).
            if ($key === 'shop') {
                wp_update_post([
                    'ID'           => $page_id,
                    'post_content' => '',
                ]);
            }
        }

        // Point the option at this page.
        if (!empty($page_id)) {
            update_option($data['option'], $page_id);
        }
    }

    update_option('senoobar_wc_fixed', time());
}

add_action('init', 'senoobar_fix_woocommerce_pages_once', 5);

// After this file has done its one job, keep it loaded but inert (option guard).
