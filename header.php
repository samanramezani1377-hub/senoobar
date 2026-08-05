<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#1a1a2e">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="صنوبر">
    <link rel="manifest" href="<?php echo SENOOBAR_URI; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo SENOOBAR_URI; ?>/assets/icons/icon-192.png">
    <link rel="preload" href="<?php echo SENOOBAR_URI; ?>/assets/fonts/IRANSansWeb.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?php echo SENOOBAR_URI; ?>/assets/css/critical.css" as="style">
    <?php wp_head(); ?>
</head>
<body <?php body_class('senoobar-body'); ?>>
    <?php wp_body_open(); ?>
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('رفتن به محتوا', 'senoobar'); ?></a>
    <div class="top-bar">
        <div class="container">
            <span class="top-bar__phone">📞 ۰۹۱۳۰۲۰۵۸۹۸</span>
            <span class="top-bar__shipping">🚚 ارسال به تمام نقاط ایران</span>
        </div>
    </div>
    <header id="masthead" class="site-header">
        <div class="container header-inner">
            <button class="mobile-menu-toggle" aria-label="منو" id="menuToggle">
                <span></span><span></span><span></span>
            </button>
            <div class="site-branding">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="custom-logo-link">
                    <span class="logo-text">صنوبر</span>
                    <span class="logo-sub">تشک طبی، فنری و کالای خواب</span>
                </a>
            </div>
            <nav id="site-navigation" class="main-navigation">
                <?php wp_nav_menu(['theme_location'=>'primary','menu_class'=>'main-menu','container'=>false,'fallback_cb'=>false]); ?>
            </nav>
            <div class="header-actions">
                <button class="search-toggle" aria-label="جستجو" id="searchToggle">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
                <?php if (class_exists('WooCommerce')) : ?>
                <a href="<?php echo wc_get_cart_url(); ?>" class="cart-icon" aria-label="سبد خرید">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                    <span class="cart-count" data-cart-count="<?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?>"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="search-overlay" id="searchOverlay">
            <div class="container"><?php get_search_form(); ?></div>
        </div>
    </header>
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu__header">
            <span class="logo-text">صنوبر</span>
            <button class="mobile-menu-close" id="menuClose">✕</button>
        </div>
        <?php wp_nav_menu(['theme_location'=>'primary','menu_class'=>'mobile-nav','container'=>false,'fallback_cb'=>false]); ?>
    </div>