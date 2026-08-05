<?php
/**
 * Template Part: Content (Single)
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('single-article'); ?>>
    <?php if (has_post_thumbnail()) : ?>
    <div class="single-thumbnail">
        <?php the_post_thumbnail('large', ['loading' => 'eager']); ?>
    </div>
    <?php endif; ?>
    
    <header class="single-header">
        <h1 class="single-title"><?php the_title(); ?></h1>
        <div class="single-meta">
            <span class="single-date"><?php echo get_the_date(); ?></span>
            <span class="single-author"><?php the_author(); ?></span>
        </div>
    </header>
    
    <div class="single-content">
        <?php the_content(); ?>
    </div>
</article>
