<?php
/**
 * Template Part: Hero Section
 * هیرو صفحه اصلی
 */
?>
<section class="hero">
    <div class="hero__bg">
        <div class="hero__overlay"></div>
        <?php
        $hero_image = get_theme_mod('senoobar_hero_image');
        if ($hero_image) {
            echo wp_get_attachment_image($hero_image, 'senoobar-hero', false, ['class' => 'hero__img', 'loading' => 'eager', 'fetchpriority' => 'high']);
        }
        ?>
    </div>
    <div class="container hero__content">
        <h1 class="hero__title"><?php echo esc_html(get_theme_mod('senoobar_hero_title', 'زیبایی را به خانه بیاورید')); ?></h1>
        <p class="hero__subtitle"><?php echo esc_html(get_theme_mod('senoobar_hero_subtitle', 'مبلمان لوکس، سرویس خواب رویایی و تشک‌های طبی با بهترین کیفیت و قیمت')); ?></p>
        <div class="hero__actions">
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn--primary btn--lg">مشاهده محصولات</a>
            <a href="#categories" class="btn btn--outline btn--lg">دسته‌بندی‌ها</a>
        </div>
    </div>
</section>

<style>
.hero {
    position: relative;
    min-height: 70vh;
    display: flex;
    align-items: center;
    overflow: hidden;
    margin-top: -24px;
}

@media (min-width: 768px) {
    .hero { min-height: 85vh; margin-top: -40px; }
}

.hero__bg {
    position: absolute;
    inset: 0;
    background: var(--color-primary-dark);
}

.hero__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.5;
}

.hero__overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(26,26,46,0.9) 0%, rgba(26,26,46,0.4) 100%);
}

.hero__content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: var(--color-white);
    padding: 40px var(--container-pad);
}

.hero__title {
    font-size: 2rem;
    font-weight: 900;
    margin-bottom: 16px;
    line-height: 1.3;
}

@media (min-width: 768px) {
    .hero__title { font-size: 3rem; }
}

@media (min-width: 1024px) {
    .hero__title { font-size: 3.5rem; }
}

.hero__subtitle {
    font-size: 1rem;
    opacity: 0.9;
    margin-bottom: 32px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 2;
}

@media (min-width: 768px) {
    .hero__subtitle { font-size: 1.15rem; }
}

.hero__actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
}
</style>