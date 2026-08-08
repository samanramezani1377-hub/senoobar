<?php
$cats=get_terms(['taxonomy'=>'product_cat','hide_empty'=>true,'number'=>6,'parent'=>0]);
if(empty($cats)||is_wp_error($cats))return;
?>
<section class="cats-section section section--off-white"><div class="container"><div class="section__header"><h2 class="section__title section__title--lined">دسته‌بندی محصولات</h2></div><div class="cats-grid">
<?php foreach($cats as $cat):$thumb_id=get_term_meta($cat->term_id,'thumbnail_id',true);$img=wp_get_attachment_url($thumb_id);?>
<a href="<?php echo esc_url(get_term_link($cat));?>" class="cat-item"><div class="cat-item__thumb">
<?php if($img):?><img src="<?php echo esc_url($img);?>" alt="<?php echo esc_attr($cat->name);?>" loading="lazy">
<?php else:?><div class="placeholder">🪑</div><?php endif;?>
</div><span class="cat-item__title"><?php echo esc_html($cat->name);?></span></a>
<?php endforeach;?>
</div></div></section>