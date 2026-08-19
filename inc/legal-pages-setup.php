<?php
/**
 * Senoobar — Legal / info pages setup (idempotent).
 *
 * Creates the five static pages linked from the mobile menu and footer
 * (درباره ما / تماس با ما / سوالات متداول / شرایط و ضوابط / حریم خصوصی)
 * using dedicated templates, once, then remembers their IDs so the menu
 * and footer can link to them reliably. Follows the same idempotent
 * pattern as wishlist-page-setup.php.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return the permalink of a legal/info page by its key, or '' if missing.
 *
 * @param string $key One of: about, contact, faq, terms, privacy.
 * @return string
 */
function senoobar_legal_page_url(string $key): string {
    $id = (int) get_option("senoobar_legal_page_{$key}_id");
    if ($id && 'publish' === get_post_status($id)) {
        return get_permalink($id);
    }
    return '';
}

/**
 * Create all five pages once, if they do not already exist.
 */
function senoobar_ensure_legal_pages(): void {
    if (get_option('senoobar_legal_pages_created')) {
        return;
    }

    // key => [ post_title, post_name (slug), template file ]
    $pages = [
        'about'   => [ 'درباره ما',     'about',             'page-about.php' ],
        'contact' => [ 'تماس با ما',    'contact',           'page-contact.php' ],
        'faq'     => [ 'سوالات متداول', 'faq',               'page-faq.php' ],
        'terms'   => [ 'شرایط و ضوابط', 'terms-and-conditions', 'page-terms.php' ],
        'privacy' => [ 'حریم خصوصی',   'privacy-policy',     'page-privacy.php' ],
    ];

    foreach ($pages as $key => $cfg) {
        [$title, $slug, $template] = $cfg;

        // Skip if this page was already remembered.
        if (get_option("senoobar_legal_page_{$key}_id")) {
            continue;
        }

        // Look for an existing page using the template.
        $existing = get_pages([
            'meta_key'   => '_wp_page_template',
            'meta_value' => $template,
            'number'     => 1,
        ]);
        if (!empty($existing)) {
            update_option("senoobar_legal_page_{$key}_id", $existing[0]->ID);
            continue;
        }

        // Create the page.
        $page_id = wp_insert_post([
            'post_title'   => $title,
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_name'    => $slug,
            'post_content' => '',
        ], true);

        if (!is_wp_error($page_id)) {
            update_post_meta($page_id, '_wp_page_template', $template);
            update_option("senoobar_legal_page_{$key}_id", $page_id);
        }
    }

    update_option('senoobar_legal_pages_created', 1);
}

add_action('after_setup_theme', 'senoobar_ensure_legal_pages', 30);
