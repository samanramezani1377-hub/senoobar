<?php
/**
 * Senoobar Theme - Main Template
 * فروشگاه مبلمان، سرویس خواب و تشک صنوبر
 */
get_header(); ?>

<main id="primary" class="site-main">
    <?php if (is_front_page() && is_home()) : ?>
        <?php get_template_part('template-parts/hero'); ?>
        <?php get_template_part('template-parts/categories-grid'); ?>
        <?php get_template_part('template-parts/featured-products'); ?>
        <?php get_template_part('template-parts/cta-banner'); ?>
    <?php elseif (is_shop() || is_product_category()) : ?>
        <?php woocommerce_content(); ?>
    <?php elseif (is_single()) : ?>
        <?php get_template_part('template-parts/content', 'single'); ?>
    <?php elseif (is_page()) : ?>
        <?php get_template_part('template-parts/content', 'page'); ?>
    <?php elseif (is_archive()) : ?>
        <?php get_template_part('template-parts/content', 'archive'); ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>

<?php get_footer(); ?>