<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e3a2f">
    <link rel="manifest" href="<?php echo home_url('manifest.json'); ?>">
    <?php $custom_logo_id = get_theme_mod('custom_logo'); if($custom_logo_id){echo '<link rel="apple-touch-icon" href="'.esc_url(wp_get_attachment_url($custom_logo_id)).'">';} ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class('senoobar-body'); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('پرش به محتوا', 'senoobar'); ?></a>

<!-- Announcement Bar -->
<div class="ann-bar">
    <div class="container ann-bar__inner">
        <span><?php echo esc_html(get_theme_mod('senoobar_announcement', '🚚 ارسال به سراسر کشور | 💳 خرید اقساطی ۳ ماهه بدون کارمزد | 🕐 شنبه تا پنجشنبه ۱۰ صبح تا ۹ شب')); ?></span>
    </div>
</div>

<!-- Site Header -->
<header id="masthead" class="site-header">
    <div class="container header-inner">

        <!-- Branding -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-branding" style="display:flex;align-items:center;gap:8px;text-decoration:none;color:inherit;">
            <?php
            $logo_id = get_theme_mod('custom_logo');
            if ($logo_id):
                echo wp_get_attachment_image($logo_id, 'full', false, ['class' => 'site-logo', 'alt' => get_bloginfo('name')]);
            else:
            ?>
            <div class="logo-icon">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2L8 8H4l4 4-1.5 6L12 15l5.5 3L16 12l4-4h-4L12 2z"/>
                </svg>
            </div>
            <div>
                <div class="logo-text"><?php bloginfo('name'); ?></div>
                <div class="logo-sub"><?php bloginfo('description'); ?></div>
            </div>
            <?php endif; ?>
        </a>

        <!-- Search (Desktop) -->
        <div class="header-search">
            <form role="search" method="get" class="header-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" name="s" placeholder="<?php esc_attr_e('جستجو در محصولات...', 'senoobar'); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
                <button type="submit" aria-label="<?php esc_attr_e('جستجو', 'senoobar'); ?>">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Header Actions -->
        <div class="header-actions">
            <?php if (class_exists('WooCommerce')): ?>
            <!-- Cart -->
            <a href="<?php echo wc_get_cart_url(); ?>" class="header-action-btn">
                <div style="position:relative;" data-cart-fly="header">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    <?php $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
                    <span class="cart-badge<?php echo $count > 0 ? '' : ' is-hidden'; ?>" data-cart-count><?php echo $count; ?></span>
                </div>
                <span class="action-label">سبد خرید</span>
            </a>
            <!-- Wishlist -->
            <?php $senoobar_wishlist_url = function_exists('senoobar_wishlist_page_url') ? senoobar_wishlist_page_url() : ''; if (empty($senoobar_wishlist_url)) $senoobar_wishlist_url = home_url('/wishlist/'); ?>
            <a href="<?php echo esc_url($senoobar_wishlist_url); ?>" class="header-action-btn">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                </svg>
                <span class="action-label">علاقه‌مندی‌ها</span>
            </a>
            <!-- Account -->
            <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" class="header-action-btn">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
                <span class="action-label">حساب کاربری</span>
            </a>
            <?php endif; ?>
            <!-- Push Notifications -->
            <button id="js-push-subscribe" class="header-action-btn push-action-btn" aria-label="نوتیفیکیشن">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                </svg>
                <span class="action-label" id="pushActionLabel">دریافت نوتیفیکیشن</span>
            </button>

            <!-- Mobile Toggle -->
            <button class="mobile-toggle" aria-label="منو" id="menuToggle">
                <span></span><span></span><span></span>
            </button>
        </div>
    </div>

    <!-- Main Navigation (Desktop) -->
    <nav class="main-navigation" style="border-top:1px solid var(--color-gray-100);">
        <div class="container">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => 'main-menu',
                'container'      => false,
                'fallback_cb'    => false,
            ]);
            ?>
        </div>
    </nav>
</header>

<?php
// Helper: build hierarchical product categories (top-level + children)
$snb_shop_url = function_exists('wc_get_page_permalink') && class_exists('WooCommerce')
    ? wc_get_page_permalink('shop') : home_url('/shop/');
$snb_cat_tree = [];
if (class_exists('WooCommerce')) {
    $snb_all_cats = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);
    if (!is_wp_error($snb_all_cats)) {
        $snb_by_parent = [];
        foreach ($snb_all_cats as $c) {
            $snb_by_parent[(int)$c->parent][] = $c;
        }
        $snb_cat_tree = isset($snb_by_parent[0]) ? $snb_by_parent[0] : [];
        // attach children (referenced below during render)
        $GLOBALS['snb_cat_children'] = $snb_by_parent;
    }
}
$snb_children_map = isset($GLOBALS['snb_cat_children']) ? $GLOBALS['snb_cat_children'] : [];

// Header actions (account / wishlist / cart)
$snb_account_url = get_permalink(get_option('woocommerce_myaccount_page_id'));
$snb_cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart/');
$snb_wish_url = function_exists('senoobar_wishlist_page_url') ? senoobar_wishlist_page_url() : home_url('/wishlist/');

// Footer-style links (pages)
$snb_terms_url  = home_url('/terms/');
$snb_privacy_url= home_url('/privacy-policy/');
$snb_about_url  = home_url('/about/');
$snb_contact_url= home_url('/contact/');
$snb_faq_url    = home_url('/faq/');

$snb_newsletter_nonce = wp_create_nonce('senoobar_newsletter_nonce');
?>
<div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="منوی موبایل">
    <div class="mobile-menu__head">
        <?php
        $logo_id = get_theme_mod('custom_logo');
        if ($logo_id):
            echo wp_get_attachment_image($logo_id, 'full', false, ['class' => 'site-logo', 'style' => 'height:40px;width:auto', 'alt' => get_bloginfo('name')]);
        else:
        ?>
        <span class="logo-text"><?php bloginfo('name'); ?></span>
        <?php endif; ?>
        <button class="mobile-close" id="menuClose" aria-label="بستن منو">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="mobile-menu__body">
        <!-- Search -->
        <div class="mobile-menu__search">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" name="s" placeholder="<?php esc_attr_e('جستجو در محصولات...', 'senoobar'); ?>" value="<?php echo esc_attr(get_search_query()); ?>">
                <button type="submit" aria-label="جستجو">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </button>
            </form>
        </div>

        <!-- Categories (hierarchical) -->
        <nav class="mobile-cats" aria-label="دسته‌بندی محصولات">
            <div class="mobile-cats__label">دسته‌بندی محصولات</div>

            <a href="<?php echo esc_url($snb_shop_url); ?>" class="mobile-cat-link mobile-cat-link--all">
                <span>همه محصولات</span>
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>

            <?php foreach ($snb_cat_tree as $cat): ?>
                <?php $kids = isset($snb_children_map[$cat->term_id]) ? $snb_children_map[$cat->term_id] : []; ?>
                <div class="mobile-cat-group">
                    <a href="<?php echo esc_url(get_term_link($cat)); ?>" class="mobile-cat-link">
                        <span><?php echo esc_html($cat->name); ?></span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <?php if ($kids): ?>
                        <div class="mobile-cat-children">
                            <?php foreach ($kids as $child): ?>
                                <a href="<?php echo esc_url(get_term_link($child)); ?>" class="mobile-cat-child"><?php echo esc_html($child->name); ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- Quick links -->
        <nav class="mobile-links" aria-label="صفحات">
            <a href="<?php echo esc_url($snb_account_url); ?>" class="mobile-link-item">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                <span>حساب کاربری</span>
            </a>
            <a href="<?php echo esc_url($snb_wish_url); ?>" class="mobile-link-item">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                <span>علاقه‌مندی‌ها</span>
            </a>
            <a href="<?php echo esc_url($snb_cart_url); ?>" class="mobile-link-item">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272"/></svg>
                <span>سبد خرید</span>
            </a>
            <div class="mobile-links__divider"></div>
            <a href="<?php echo esc_url($snb_about_url); ?>" class="mobile-link-item"><span>درباره ما</span></a>
            <a href="<?php echo esc_url($snb_contact_url); ?>" class="mobile-link-item"><span>تماس با ما</span></a>
            <a href="<?php echo esc_url($snb_faq_url); ?>" class="mobile-link-item"><span>سوالات متداول</span></a>
            <a href="<?php echo esc_url($snb_terms_url); ?>" class="mobile-link-item"><span>شرایط و ضوابط</span></a>
            <a href="<?php echo esc_url($snb_privacy_url); ?>" class="mobile-link-item"><span>حریم خصوصی</span></a>
        </nav>

        <!-- Newsletter -->
        <div class="mobile-newsletter">
            <div class="mobile-newsletter__title">📬 خبرنامه صنوبر</div>
            <p class="mobile-newsletter__desc">از تخفیف‌ها و جدیدترین محصولات باخبر شوید.</p>
            <form class="mobile-newsletter-form" method="post" action="#" data-nonce="<?php echo esc_attr($snb_newsletter_nonce); ?>">
                <input type="email" name="email" placeholder="ایمیل خود را وارد کنید..." required autocomplete="email">
                <button type="submit">عضویت</button>
                <div class="mobile-newsletter-message" role="alert" aria-live="polite"></div>
            </form>
        </div>
    </div>
</div>
<div class="menu-overlay" id="menuOverlay"></div>

