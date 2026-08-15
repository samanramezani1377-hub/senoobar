<?php
/**
 * Senoobar - WooCommerce Wrapper Template
 * Custom layout for all WooCommerce pages
 *
 * @package Senoobar
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container page-content">
        <?php
        /**
         * Hook: woocommerce_before_main_content.
         */
        do_action( 'woocommerce_before_main_content' );
        ?>

        <?php
        /**
         * Render the actual WooCommerce content.
         * woocommerce_content() renders the product loop on shop/archive pages
         * and the product detail on single-product pages.
         */
        if ( function_exists( 'woocommerce_content' ) ) {
            woocommerce_content();
        } else {
            do_action( 'woocommerce_content' );
        }
        ?>

        <?php
        /**
         * Hook: woocommerce_after_main_content.
         */
        do_action( 'woocommerce_after_main_content' );
        ?>
    </div>
</main>

<?php
get_footer();