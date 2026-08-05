<?php
if (have_posts()): ?>
    <header class="page-header">
        <?php the_archive_title('<h1 class="page-title">', '</h1>'); ?>
        <?php the_archive_description('<div class="archive-desc">', '</div>'); ?>
    </header>
    <div class="archive-posts">
        <?php while (have_posts()): the_post(); ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('archive-item'); ?>>
                <h2 class="archive-item__title">
                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                </h2>
                <div class="archive-item__excerpt"><?php the_excerpt(); ?></div>
            </article>
        <?php endwhile; ?>
    </div>
    <?php the_posts_navigation(); ?>
<?php else:
    get_template_part('template-parts/content', 'none');
endif;
