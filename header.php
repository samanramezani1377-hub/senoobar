<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#0a0a0f">
    <link rel="manifest" href="<?php echo SENOOBAR_URI; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo SENOOBAR_URI; ?>/assets/icons/icon-192.png">
    <link rel="preload" href="<?php echo SENOOBAR_URI; ?>/assets/css/critical.css" as="style">
    <?php wp_head(); ?>
</head>
<body <?php body_class('senoobar-body'); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary">رفتن به محتوا</a>

<!-- Top Bar -->
<div class="top-bar">
    <div class="container top-bar__inner">
        <span><span class="top-bar__dot"></span> ۰۹۱۳۰۲۰۵۸۹۸</span>
        <span>🚚 ارسال رایگان به سراسر ایران</span>
    </div>
</div>

<!-- Header -->
<header id="masthead" class="site-header" role="banner">
    <div class="container header-row">

        <button class="hamburger" id="js-hamburger" aria-label="باز کردن منو">
            <span></span><span></span><span></span>
        </button>

        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="brand-link" rel="home">
                <span class="brand-name">صنوبر</span>
                <span class="brand-tagline">تشک · طبی · فنری · کالای خواب</span>
            </a>
        </div>

        <nav class="main-nav" role="navigation" aria-label="منوی اصلی">
            <?php wp_nav_menu(['theme_location'=>'primary','menu_class'=>'main-nav__list','container'=>false,'fallback_cb'=>'__return_false']); ?>
        </nav>

        <div class="header-actions">
            <button class="header-action-btn" id="js-search-toggle" aria-label="جستجو">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <?php if (class_exists('WooCommerce')): ?>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="header-action-btn cart-link" aria-label="سبد خرید">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <span class="cart-count" data-cart-count="<?php echo WC()->cart->get_cart_contents_count(); ?>"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
            </a>
            <?php endif; ?>
        </div>
    </div>

    <div class="search-overlay" id="js-search-overlay">
        <div class="container"><?php get_search_form(); ?></div>
    </div>
</header>

<!-- Mobile Drawer -->
<div class="mobile-drawer" id="js-mobile-drawer">
    <div class="mobile-drawer__head">
        <span class="brand-name">صنوبر</span>
        <button class="mobile-drawer__close" id="js-drawer-close" aria-label="بستن منو">✕</button>
    </div>
    <?php wp_nav_menu(['theme_location'=>'primary','menu_class'=>'mobile-nav','container'=>false,'fallback_cb'=>'__return_false']); ?>
</div>
<div class="mobile-drawer-overlay" id="js-drawer-overlay"></div>
