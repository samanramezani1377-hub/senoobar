<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e3a2f">
    <link rel="manifest" href="<?php echo SENOOBAR_URI; ?>/manifest.json">
    <?php $custom_logo_id = get_theme_mod('custom_logo'); if($custom_logo_id){echo '<link rel="apple-touch-icon" href="'.esc_url(wp_get_attachment_url($custom_logo_id)).'">';} ?>
    <link rel="preload" href="<?php echo SENOOBAR_URI; ?>/assets/fonts/Vazirmatn.woff2" as="font" type="font/woff2" crossorigin>
    <?php wp_head(); ?>
</head>
<body <?php body_class('senoobar-body'); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('پرش به محتوا', 'senoobar'); ?></a>

<!-- Announcement Bar -->
<div class="ann-bar">
    <div class="container ann-bar__inner">
        <span>🚚</span>
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
                <input type="search" name="s" placeholder="<?php esc_attr_e('جستجو در محصولات...', 'senoobar'); ?>" value="<?php echo get_search_query(); ?>">
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
                <div style="position:relative;">
                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                    </svg>
                    <?php $count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>
                    <?php if ($count > 0): ?>
                    <span class="cart-badge"><?php echo $count; ?></span>
                    <?php endif; ?>
                </div>
                <span class="action-label">سبد خرید</span>
            </a>
            <!-- Wishlist -->
            <button class="header-action-btn">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                </svg>
                <span class="action-label">علاقه‌مندی‌ها</span>
            </button>
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

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu__head">
        <?php
        $logo_id = get_theme_mod('custom_logo');
        if ($logo_id):
            echo wp_get_attachment_image($logo_id, 'full', false, ['class' => 'site-logo', 'style' => 'height:45px;width:auto', 'alt' => get_bloginfo('name')]);
        else:
        ?>
        <span class="logo-text"><?php bloginfo('name'); ?></span>
        <?php endif; ?>
        <button class="mobile-close" id="menuClose" aria-label="بستن">✕</button>
    </div>
    <!-- Mobile Search -->
    <div style="padding: 12px 16px;">
        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="display:flex;background:var(--color-gray-50);border-radius:var(--radius-xl);overflow:hidden;border:1px solid var(--color-gray-200);">
            <input type="search" name="s" placeholder="<?php esc_attr_e('جستجو در محصولات...', 'senoobar'); ?>" style="flex:1;border:none;background:transparent;padding:10px 16px;font-size:0.9rem;outline:none;">
            <button type="submit" style="padding:10px 16px;color:var(--color-gray-600);">🔍</button>
        </form>
    </div>
    <?php
    wp_nav_menu([
        'theme_location' => 'primary',
        'menu_class'     => 'mobile-nav',
        'container'      => false,
        'fallback_cb'    => false,
    ]);
    ?>
</div>
<div class="menu-overlay" id="menuOverlay"></div>
