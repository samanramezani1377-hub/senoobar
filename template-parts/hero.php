<?php
$hero_image=get_theme_mod('senoobar_hero_image');
?>
<section class="hero"><div class="hero__bg"><div class="hero__overlay"></div>
<?php if($hero_image)echo wp_get_attachment_image($hero_image,'senoobar-hero',false,['class'=>'hero__img','loading'=>'eager','fetchpriority'=>'high']);?>
</div>
<div class="container hero__content">
<h1 class="hero__title"><?php echo esc_html(get_theme_mod('senoobar_hero_title','خوابی راحت، آرامشی پایدار')); ?></h1>
<p class="hero__subtitle"><?php echo esc_html(get_theme_mod('senoobar_hero_subtitle','انواع تشک‌های طبی، طبی فنری و کالای خواب با بالاترین کیفیت و قیمت رقابتی - ارسال به سراسر ایران')); ?></p>
<div class="hero__actions">
<a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn--primary btn--lg">مشاهده محصولات</a>
<a href="#categories" class="btn btn--outline btn--lg">دسته‌بندی‌ها</a>
</div></div></section>