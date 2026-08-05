<?php
/**
 * Template Part: Content (Page)
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('page-article'); ?>>
    <header class="page-header">
        <h1 class="page-title"><?php the_title(); ?></h1>
    </header>
    
    <div class="page-content">
        <?php the_content(); ?>
    </div>
</article>
