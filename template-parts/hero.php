<?php
/**
 * Template Part: Hero
 */
$shop_url = (class_exists('WooCommerce') && function_exists('wc_get_page_id'))
    ? get_permalink(wc_get_page_id('shop'))
    : '#';
?>
<section class="hero">
    <div class="hero__bg">
        <div class="hero__overlay"></div>
    </div>
    <div class="container hero__content">
        <h1 class="hero__title">خوابی راحت، آرامشی پایدار</h1>
        <p class="hero__sub">انواع تشک‌های طبی، طبی فنری و کالای خواب — کیفیت بالا، قیمت رقابتی، ارسال سراسر ایران</p>
        <div class="hero__btns">
            <a href="<?php echo esc_url($shop_url); ?>" class="btn btn--primary btn--lg">مشاهده محصولات</a>
            <a href="#categories" class="btn btn--outline-light btn--lg">دسته‌بندی‌ها</a>
        </div>
    </div>
</section>
