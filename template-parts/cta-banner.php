<?php
/**
 * Template Part: CTA Banner
 */
$shop_url = (class_exists('WooCommerce') && function_exists('wc_get_page_id'))
    ? get_permalink(wc_get_page_id('shop'))
    : '#';
?>
<section class="cta section">
    <div class="container">
        <div class="cta__inner">
            <div class="cta__text">
                <h2 class="cta__title">تضمین بهترین قیمت</h2>
                <p class="cta__desc">خرید مستقیم از تولیدکننده — بدون واسطه، با گارانتی بازگشت وجه</p>
            </div>
            <a href="<?php echo esc_url($shop_url); ?>" class="btn btn--primary btn--lg">همین حالا خرید کنید</a>
        </div>
    </div>
</section>
