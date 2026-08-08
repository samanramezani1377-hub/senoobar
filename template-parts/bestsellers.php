<?php
$q=new WP_Query(['post_type'=>'product','posts_per_page'=>8,'meta_key'=>'total_sales','orderby'=>'meta_value_num','order'=>'DESC']);
if(!$q->have_posts())return;
?>
<section class="section section--cream"><div class="container"><div class="section__header"><h2 class="section__title section__title--lined">پرفروش‌ترین‌ها</h2><p class="section__desc">محبوب‌ترین محصولات</p></div><div class="products-grid">
<?php while($q->have_posts()):$q->the_post();wc_get_template_part('content','product');endwhile;wp_reset_postdata();?>
</div><div class="section__footer"><a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop')));?>" class="btn btn--outline">مشاهده همه</a></div></div></section>