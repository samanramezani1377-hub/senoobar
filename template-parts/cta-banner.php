<?php
/**
 * Template Part: CTA Banner
 */
$shop_url = (class_exists('WooCommerce') && function_exists('wc_get_page_id')) ? get_permalink(wc_get_page_id('shop')) : '#';
?>
<section class="cta-banner section">
    <div class="container">
        <div class="cta-banner__inner">
            <div class="cta-banner__content">
                <h2 class="cta-banner__title"><?php echo esc_html(get_theme_mod('senoobar_cta_title', 'تضمین بهترین قیمت')); ?></h2>
                <p class="cta-banner__desc"><?php echo esc_html(get_theme_mod('senoobar_cta_desc', 'خرید مستقیم از تولیدکننده - بدون واسطه')); ?></p>
            </div>
            <a href="<?php echo esc_url($shop_url); ?>" class="btn btn--primary btn--lg">همین حالا خرید کنید</a>
        </div>
    </div>
</section>