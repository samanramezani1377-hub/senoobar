<?php
$args=['post_type'=>'product','posts_per_page'=>12,'meta_key'=>'_featured','meta_value'=>'yes'];
$featured=new WP_Query($args);
if(!$featured->have_posts()){$args=['post_type'=>'product','posts_per_page'=>12];$featured=new WP_Query($args);}
if(!$featured->have_posts())return;
?>
<section class="featured-products section"><div class="container">
<div class="section__header"><h2 class="section__title">محصولات ویژه</h2><p class="section__desc">منتخبی از پرفروش‌ترین تشک‌ها و کالای خواب صنوبر با تخفیف‌های استثنایی</p></div>
<div class="products-grid"><?php while($featured->have_posts()):$featured->the_post();wc_get_template_part('content','product');endwhile;wp_reset_postdata();?></div>
<div class="section__footer"><a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn--outline">مشاهده همه محصولات</a></div>
</div></section>