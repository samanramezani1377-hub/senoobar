<?php
/**
 * Senoobar Theme Core Class
 * کلاس اصلی قالب صنوبر
 */

final class Senoobar_Theme {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function init() {
        $this->setup_theme();
        $this->enqueue_assets();
        $this->woocommerce_support();
        $this->register_menus();
        $this->pwa_support();
        $this->performance_optimizations();
    }

    private function setup_theme() {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo', [
            'height'      => 60,
            'width'       => 180,
            'flex-height' => true,
            'flex-width'  => true,
        ]);
        add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
        add_theme_support('responsive-embeds');
        add_theme_support('wp-block-styles');
        add_theme_support('align-wide');

        add_image_size('senoobar-product-thumb', 400, 400, true);
        add_image_size('senoobar-product-medium', 600, 600, true);
        add_image_size('senoobar-product-large', 1200, 800, true);
        add_image_size('senoobar-hero', 1920, 800, true);
    }

    private function register_menus() {
        register_nav_menus([
            'primary' => __('منوی اصلی', 'senoobar'),
            'footer'  => __('منوی فوتر', 'senoobar'),
            'mobile'  => __('منوی موبایل', 'senoobar'),
        ]);
    }

    private function woocommerce_support() {
        if (!class_exists('WooCommerce')) return;

        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');

        add_filter('woocommerce_enqueue_styles', '__return_empty_array');
        add_filter('woocommerce_add_to_cart_fragments', [$this, 'cart_fragments']);
    }

    public function cart_fragments($fragments) {
        ob_start(); ?>
        <span class="cart-count" data-cart-count="<?php echo WC()->cart->get_cart_contents_count(); ?>">
            <?php echo WC()->cart->get_cart_contents_count(); ?>
        </span>
        <?php
        $fragments['.cart-count'] = ob_get_clean();
        return $fragments;
    }

    private function pwa_support() {
        add_action('wp_head', [$this, 'pwa_meta_tags']);
        add_action('wp_footer', [$this, 'pwa_register_script']);
    }

    public function pwa_meta_tags() {
        ?>
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="صنوبر">
        <link rel="apple-touch-icon" sizes="192x192" href="<?php echo SENOOBAR_URI; ?>/assets/icons/icon-192.png">
        <link rel="apple-touch-icon" sizes="512x512" href="<?php echo SENOOBAR_URI; ?>/assets/icons/icon-512.png">
        <?php
    }

    public function pwa_register_script() {
        ?>
        <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('<?php echo SENOOBAR_URI; ?>/sw.js').then(
                    function(registration) { console.log('SW registered:', registration.scope); },
                    function(err) { console.log('SW failed:', err); }
                );
            });
        }
        </script>
        <?php
    }

    private function performance_optimizations() {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        add_filter('wp_default_scripts', function($scripts) {
            if (!is_admin() && isset($scripts->registered['jquery'])) {
                $scripts->registered['jquery']->deps = array_diff(
                    $scripts->registered['jquery']->deps, ['jquery-migrate']
                );
            }
        });

        add_filter('script_loader_tag', function($tag, $handle) {
            if (in_array($handle, ['senoobar-app', 'senoobar-push'])) {
                return str_replace(' src', ' defer src', $tag);
            }
            return $tag;
        }, 10, 2);

        add_filter('wp_content_img_tag', function($content) {
            return str_replace('<img ', '<img loading="lazy" ', $content);
        });
    }

    private function enqueue_assets() {
        add_action('wp_enqueue_scripts', [$this, 'register_styles']);
        add_action('wp_enqueue_scripts', [$this, 'register_scripts']);
    }

    public function register_styles() {
        wp_enqueue_style('senoobar-critical', SENOOBAR_URI . '/assets/css/critical.css', [], SENOOBAR_VERSION);
        wp_enqueue_style('senoobar-main', SENOOBAR_URI . '/assets/css/main.css', [], SENOOBAR_VERSION);
        if (is_rtl()) {
            wp_enqueue_style('senoobar-rtl', SENOOBAR_URI . '/assets/css/rtl.css', ['senoobar-main'], SENOOBAR_VERSION);
        }
    }

    public function register_scripts() {
        wp_enqueue_script('senoobar-app', SENOOBAR_URI . '/assets/js/app.js', [], SENOOBAR_VERSION, true);
        wp_enqueue_script('senoobar-push', SENOOBAR_URI . '/assets/js/push.js', [], SENOOBAR_VERSION, true);

        $data = [
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('senoobar_nonce'),
            'isRTL'    => is_rtl(),
            'siteUrl'  => home_url(),
            'themeUrl' => SENOOBAR_URI,
        ];

        if (class_exists('WooCommerce')) {
            $data['cartUrl']     = wc_get_cart_url();
            $data['checkoutUrl'] = wc_get_checkout_url();
        }

        wp_localize_script('senoobar-app', 'senoobarData', $data);
    }
}
