<?php $posts=get_posts(['numberposts'=>4]);if(empty($posts))return;?>
<section class="section"><div class="container"><div class="section__header"><h2 class="section__title section__title--lined">آخرین مقالات</h2></div><div class="blog-grid">
<?php foreach($posts as $post):setup_postdata($post);?>
<a href="<?php the_permalink();?>" class="blog-card"><div class="blog-card__img"><?php if(has_post_thumbnail()):the_post_thumbnail('medium',['loading'=>'lazy']);else:?><div style="background:var(--color-gray-200);width:100%;height:100%"></div><?php endif;?></div><div class="blog-card__body"><span class="blog-card__date"><?php echo get_the_date();?></span><h3 class="blog-card__title"><?php the_title();?></h3></div></a>
<?php endforeach;wp_reset_postdata();?>
</div><div class="section__footer"><a href="<?php echo get_permalink(get_option('page_for_posts'));?>" class="btn btn--outline">مشاهده همه</a></div></div></section>