<?php
$categories=get_terms(['taxonomy'=>'product_cat','hide_empty'=>true,'number'=>6,'parent'=>0]);
if(empty($categories)||is_wp_error($categories))return;
?>
<section class="categories-section section" id="categories"><div class="container">
<div class="section__header"><h2 class="section__title">دسته‌بندی محصولات</h2><p class="section__desc">تشک و کالای خواب خود را بر اساس نیازتان انتخاب کنید</p></div>
<div class="categories-grid">
<?php foreach($categories as $cat):$thumbnail_id=get_term_meta($cat->term_id,'thumbnail_id',true);$image=wp_get_attachment_url($thumbnail_id);?>
<a href="<?php echo esc_url(get_term_link($cat)); ?>" class="category-card">
<div class="category-card__image"><?php if($image):?><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($cat->name); ?>" loading="lazy"><?php else:?><div class="category-card__placeholder"></div><?php endif;?></div>
<div class="category-card__info"><h3 class="category-card__title"><?php echo esc_html($cat->name); ?></h3><span class="category-card__count"><?php echo esc_html($cat->count); ?> محصول</span></div>
</a>
<?php endforeach; ?>
</div></div></section>