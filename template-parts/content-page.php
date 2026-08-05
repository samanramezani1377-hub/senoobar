<?php
while (have_posts()): the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class('page-content'); ?>>
    <header class="entry-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>
    <div class="entry-content">
        <?php the_content(); ?>
    </div>
</article>
<?php endwhile; ?>
