<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <meta name="theme-color" content="#2d2824">
    <link rel="manifest" href="<?php echo SENOOBAR_URI; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo SENOOBAR_URI; ?>/assets/images/logo.png">
    <link rel="preload" href="<?php echo SENOOBAR_URI; ?>/assets/fonts/IRANSansWeb.woff2" as="font" type="font/woff2" crossorigin>
    <?php wp_head(); ?>
</head>
<body <?php body_class('senoobar-body'); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('رفتن به محتوا', 'senoobar'); ?></a>

<!-- Announcement -->
<div class="ann-bar">
    <div class="container ann-bar__inner">
        <span>🚚</span>
        <span><?php echo esc_html(get_theme_mod('senoobar_announcement', 'ارسال رایگان برای سفارش‌های بالای ۵ میلیون تومان')); ?></span>
    </div>
</div>

<!-- Header -->
<header id="masthead" class="site-header">
    <div class="container header-inner">
        <button class="mobile-toggle" aria-label="منو" id="menuToggle">
            <span></span><span></span><span></span>
        </button>

        <div class="site-branding">
            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                <img src="<?php echo SENOOBAR_URI; ?>/assets/images/logo.png" alt="صنوبر" class="site-logo" width="120" height="60">
            </a>
        </div>

        <div class="header-search">
            <form role="search" method="get" class="header-search-form" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" name="s" placeholder="<?php esc_attr_e('جستجو در محصولات...', 'senoobar'); ?>" value="<?php echo get_search_query(); ?>">
                <button type="submit" aria-label="جستجو">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
            </form>
        </div>

        <nav id="site-navigation" class="main-navigation">
            <?php wp_nav_menu([
                'theme_location' => 'primary',
                'menu_class'     => 'main-menu',
                'container'      => false,
                'fallback_cb'    => false,
            ]); ?>
        </nav>

        <div class="header-actions">
            <?php if (class_exists('WooCommerce')) : ?>
            <a href="<?php echo wc_get_cart_url(); ?>" class="cart-icon" aria-label="سبد خرید">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span class="cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu__head">
        <img src="<?php echo SENOOBAR_URI; ?>/assets/images/logo.png" alt="صنوبر" class="site-logo" width="90" height="45">
        <button class="mobile-close" id="menuClose" aria-label="بستن">✕</button>
    </div>
    <?php wp_nav_menu([
        'theme_location' => 'primary',
        'menu_class'     => 'mobile-nav',
        'container'      => false,
        'fallback_cb'    => false,
    ]); ?>
</div>
<div class="menu-overlay" id="menuOverlay"></div>