<?php if(have_posts()):?>
<header class="page-header"><?php the_archive_title('<h1 class="page-title">','</h1>');?></header>
<div class="archive-grid">
<?php while(have_posts()):the_post();get_template_part('template-parts/content','archive-item');endwhile;?>
</div>
<?php the_posts_navigation();
else:get_template_part('template-parts/content','none');
endif;?>