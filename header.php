<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#1c1c28">
    <link rel="manifest" href="<?php echo SENOOBAR_URI; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo SENOOBAR_URI; ?>/assets/icons/icon-192.png">
    <!-- Critical CSS preload -->
    <link rel="preload" href="<?php echo SENOOBAR_URI; ?>/assets/css/critical.css" as="style">
    <?php wp_head(); ?>
</head>
<body <?php body_class('senoobar-body'); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary">رفتن به محتوا</a>

<!-- === Top Bar === -->
<div class="top-bar">
    <div class="container top-bar__inner">
        <span>📞 ۰۹۱۳۰۲۰۵۸۹۸</span>
        <span>🚚 ارسال رایگان به سراسر ایران</span>
    </div>
</div>

<!-- === Site Header === -->
<header id="masthead" class="site-header" role="banner">
    <div class="container header-row">

        <!-- Hamburger (mobile) -->
        <button class="hamburger" id="js-hamburger" aria-label="باز کردن منو">
            <span></span><span></span><span></span>
        </button>

        <!-- Logo -->
        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="brand-link" rel="home">
                <span class="brand-name">صنوبر</span>
                <span class="brand-tagline">تشک طبی، فنری و کالای خواب</span>
            </a>
        </div>

        <!-- Desktop Nav -->
        <nav class="main-nav" role="navigation" aria-label="منوی اصلی">
            <?php
            wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => 'main-nav__list',
                'container'      => false,
                'fallback_cb'    => '__return_false',
            ]);
            ?>
        </nav>

        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Search toggle -->
            <button class="header-action-btn" id="js-search-toggle" aria-label="جستجو">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
            </button>

            <?php if (class_exists('WooCommerce')): ?>
            <!-- Cart -->
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="header-action-btn cart-link" aria-label="سبد خرید">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                </svg>
                <span class="cart-count" data-cart-count="<?php echo WC()->cart->get_cart_contents_count(); ?>">
                    <?php echo WC()->cart->get_cart_contents_count(); ?>
                </span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Search Overlay -->
    <div class="search-overlay" id="js-search-overlay">
        <div class="container">
            <?php get_search_form(); ?>
        </div>
    </div>
</header>

<!-- === Mobile Drawer === -->
<div class="mobile-drawer" id="js-mobile-drawer">
    <div class="mobile-drawer__head">
        <span class="brand-name">صنوبر</span>
        <button class="mobile-drawer__close" id="js-drawer-close" aria-label="بستن منو">✕</button>
    </div>
    <?php
    wp_nav_menu([
        'theme_location' => 'primary',
        'menu_class'     => 'mobile-nav',
        'container'      => false,
        'fallback_cb'    => '__return_false',
    ]);
    ?>
</div>
<div class="mobile-drawer-overlay" id="js-drawer-overlay"></div>
