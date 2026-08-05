<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#fff">
    <link rel="manifest" href="<?php echo SENOOBAR_URI; ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?php echo SENOOBAR_URI; ?>/assets/icons/icon-192.png">
    <?php wp_head(); ?>
</head>
<body <?php body_class('senoobar-body'); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary">رفتن به محتوا</a>

<div class="top-bar">🚚 ارسال رایگان به سراسر ایران  |  📞 ۰۹۱۳۰۲۰۵۸۹۸  |  📍 اصفهان، میدان لاله</div>

<header id="masthead" class="site-header">
    <div class="container header-row">
        <button class="hamburger" id="js-hamburger" aria-label="منو"><span></span><span></span><span></span></button>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="brand-logo" rel="home">🛏️ صنوبر</a>
        <div class="header-search">
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="search" name="s" placeholder="جستجوی تشک، سرویس خواب، بالش..." value="<?php echo get_search_query(); ?>">
                <button type="submit" aria-label="جستجو">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
            </form>
        </div>
        <div class="header-contact">
            <strong>۰۹۱۳۰۲۰۵۸۹۸</strong>
            <span>پاسخگویی ۱۰ تا ۲۱</span>
        </div>
        <div class="header-actions">
            <button class="header-action-btn" id="js-search-toggle-mob" aria-label="جستجو">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <?php if (class_exists('WooCommerce')): ?>
            <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="header-action-btn cart-link" aria-label="سبد خرید">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <nav class="nav-bar">
        <div class="container nav-bar__inner">
            <a href="#" class="nav-categories">☰ دسته‌بندی‌ها</a>
            <?php wp_nav_menu(['theme_location'=>'primary','menu_class'=>'','container'=>false,'fallback_cb'=>'__return_false','items_wrap'=>'%3$s']); ?>
        </div>
    </nav>
</header>

<div class="mobile-drawer" id="js-mobile-drawer">
    <div class="mobile-drawer__head">
        <span class="brand-logo">🛏️ صنوبر</span>
        <button class="mobile-drawer__close" id="js-drawer-close" aria-label="بستن">✕</button>
    </div>
    <?php wp_nav_menu(['theme_location'=>'primary','menu_class'=>'mobile-nav','container'=>false,'fallback_cb'=>'__return_false']); ?>
</div>
<div class="mobile-drawer-overlay" id="js-drawer-overlay"></div>
