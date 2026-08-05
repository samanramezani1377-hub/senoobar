<?php
/**
 * Template Part: Content (Archive)
 */
?>
<header class="archive-header">
    <?php
    the_archive_title('<h1 class="archive-title">', '</h1>');
    the_archive_description('<div class="archive-desc">', '</div>');
    ?>
</header>

<div class="archive-posts">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('archive-item'); ?>>
            <?php if (has_post_thumbnail()) : ?>
                <a href="<?php the_permalink(); ?>" class="archive-item__thumb">
                    <?php the_post_thumbnail('medium', ['loading' => 'lazy']); ?>
                </a>
            <?php endif; ?>
            <div class="archive-item__content">
                <h2 class="archive-item__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <div class="archive-item__excerpt"><?php the_excerpt(); ?></div>
            </div>
        </article>
    <?php endwhile; ?>
</div>

<?php the_posts_pagination(); ?>
