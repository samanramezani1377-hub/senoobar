<?php
$hero_title=get_theme_mod('senoobar_hero_title','میلمان خانه‌ای درخور شما');
$hero_subtitle=get_theme_mod('senoobar_hero_subtitle','تجربه‌ای متفاوت از راحتی و زیبایی');
$hero_img1=get_theme_mod('senoobar_hero_img1');
$hero_img2=get_theme_mod('senoobar_hero_img2');
$img1=$hero_img1?wp_get_attachment_url($hero_img1):'https://images.unsplash.com/photo-1554995207-c18c203602cb?w=900&h=600&fit=crop&auto=format';
$img2=$hero_img2?wp_get_attachment_url($hero_img2):'https://images.unsplash.com/photo-1696762932825-2737db830bbe?w=900&h=600&fit=crop&auto=format';
?><section class="hero"><div class="hero__grid"><div class="hero__image-wrap"><img src="<?php echo esc_url($img1); ?>" alt="نشیمن مدرن" loading="eager"></div><div class="hero__image-wrap"><img src="<?php echo esc_url($img2); ?>" alt="اتاق خواب" loading="eager"></div></div><div class="hero__content"><div class="hero__content-inner"><p class="hero__kicker">زیبایی در سادگی، کیفیت در جزئیات</p><h1 class="hero__title"><?php echo esc_html($hero_title); ?></h1><p class="hero__subtitle"><?php echo esc_html($hero_subtitle); ?></p><div class="hero__actions"><a href="<?php echo class_exists('WooCommerce')?get_permalink(wc_get_page_id('shop')):'#'; ?>" class="btn btn--primary">مشاهده محصولات</a><a href="#" class="hero__btn-outline">مشاهده مجموعه‌ها</a></div></div></div></section>