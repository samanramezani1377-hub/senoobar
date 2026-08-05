<?php
/**
 * Template Part: Featured Products
 */
if (!class_exists('WooCommerce')) return;

$query = new WP_Query([
    'post_type'      => 'product',
    'posts_per_page' => 12,
    'meta_key'       => '_featured',
    'meta_value'     => 'yes',
]);

if (!$query->have_posts()) {
    $query = new WP_Query([
        'post_type'      => 'product',
        'posts_per_page' => 12,
    ]);
}

if (!$query->have_posts()) return;
?>
<section class="featured-products section">
    <div class="container">
        <div class="section-heading">
            <h2 class="section-heading__title">محصولات ویژه</h2>
            <p class="section-heading__desc">پرفروش‌ترین تشک‌ها و کالای خواب صنوبر</p>
        </div>

        <div class="products-grid">
            <?php while ($query->have_posts()): $query->the_post();
                wc_get_template_part('content', 'product');
            endwhile;
            wp_reset_postdata();
            ?>
        </div>

        <div class="section-footer">
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="btn btn--outline">
                مشاهده همه محصولات ←
            </a>
        </div>
    </div>
</section>
