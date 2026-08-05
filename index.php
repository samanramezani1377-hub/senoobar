<?php
/**
 * Main Template
 */
get_header();
?>

<div id="primary" class="site-content">

    <?php if (is_front_page()) : ?>
        <?php
        // Homepage sections — safe even without WooCommerce
        get_template_part('template-parts/hero');

        if (class_exists('WooCommerce')) {
            get_template_part('template-parts/categories-grid');
            get_template_part('template-parts/featured-products');
        }

        get_template_part('template-parts/cta-banner');
        ?>

    <?php elseif (is_shop() || is_product_category() || is_product_tag()) : ?>
        <?php woocommerce_content(); ?>

    <?php elseif (is_single()) : ?>
        <?php get_template_part('template-parts/content', 'single'); ?>

    <?php elseif (is_page()) : ?>
        <?php get_template_part('template-parts/content', 'page'); ?>

    <?php elseif (is_archive() || is_search()) : ?>
        <?php get_template_part('template-parts/content', 'archive'); ?>

    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>

</div><!-- #primary -->

<?php
get_footer();
