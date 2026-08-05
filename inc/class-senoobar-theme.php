<?php
/**
 * Senoobar Theme Core Class
 */

final class Senoobar_Theme
{
    private static ?self $instance = null;

    public static function get_instance(): self
    {
        return self::$instance ??= new self();
    }

    private function __construct() {}

    // ============================================================
    //  INIT
    // ============================================================

    public function init(): void
    {
        $this->setup_theme();
        $this->register_menus();
        $this->enqueue_frontend_assets();
        $this->add_pwa_support();
        $this->apply_performance_tweaks();
    }

    // ============================================================
    //  THEME SETUP
    // ============================================================

    private function setup_theme(): void
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('html5', [
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script',
        ]);
        add_theme_support('responsive-embeds');
        add_theme_support('wp-block-styles');
        add_theme_support('align-wide');
        add_theme_support('custom-logo', [
            'height'      => 60,
            'width'       => 200,
            'flex-height' => true,
            'flex-width'  => true,
        ]);

        // Image sizes
        add_image_size('senoobar-thumb', 400, 400, true);
        add_image_size('senoobar-medium', 600, 600, true);
        add_image_size('senoobar-large', 1200, 800, true);
        add_image_size('senoobar-hero', 1920, 900, true);
    }

    // ============================================================
    //  MENUS
    // ============================================================

    private function register_menus(): void
    {
        register_nav_menus([
            'primary' => 'منوی اصلی',
            'footer'  => 'منوی فوتر',
            'mobile'  => 'منوی موبایل',
        ]);
    }

    // ============================================================
    //  ASSETS
    // ============================================================

    private function enqueue_frontend_assets(): void
    {
        add_action('wp_enqueue_scripts', function () {
            // Styles
            wp_enqueue_style(
                'senoobar-critical',
                SENOOBAR_URI . '/assets/css/critical.css',
                [],
                SENOOBAR_VERSION
            );

            wp_enqueue_style(
                'senoobar-main',
                SENOOBAR_URI . '/assets/css/main.css',
                ['senoobar-critical'],
                SENOOBAR_VERSION
            );

            if (is_rtl()) {
                wp_enqueue_style(
                    'senoobar-rtl',
                    SENOOBAR_URI . '/assets/css/rtl.css',
                    ['senoobar-main'],
                    SENOOBAR_VERSION
                );
            }

            // Scripts
            wp_enqueue_script(
                'senoobar-app',
                SENOOBAR_URI . '/assets/js/app.js',
                [],
                SENOOBAR_VERSION,
                true
            );

            wp_enqueue_script(
                'senoobar-push',
                SENOOBAR_URI . '/assets/js/push.js',
                [],
                SENOOBAR_VERSION,
                true
            );

            // JS l10n
            $data = [
                'ajaxUrl'  => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('senoobar_nonce'),
                'siteUrl'  => home_url('/'),
                'themeUrl' => SENOOBAR_URI,
                'isRTL'    => is_rtl(),
            ];

            if (class_exists('WooCommerce')) {
                $data['cartUrl']     = wc_get_cart_url();
                $data['checkoutUrl'] = wc_get_checkout_url();
            }

            wp_localize_script('senoobar-app', 'Senoobar', $data);
        });
    }

    // ============================================================
    //  PWA
    // ============================================================

    private function add_pwa_support(): void
    {
        // <head> meta
        add_action('wp_head', function () {
            ?>
            <meta name="mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
            <meta name="apple-mobile-web-app-title" content="صنوبر">
            <link rel="apple-touch-icon" sizes="192x192" href="<?php echo SENOOBAR_URI; ?>/assets/icons/icon-192.png">
            <link rel="apple-touch-icon" sizes="512x512" href="<?php echo SENOOBAR_URI; ?>/assets/icons/icon-512.png">
            <?php
        });

        // Service Worker registration (inline, tiny)
        add_action('wp_footer', function () {
            ?>
            <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register('<?php echo SENOOBAR_URI; ?>/sw.js');
                });
            }
            </script>
            <?php
        }, 1);
    }

    // ============================================================
    //  PERFORMANCE
    // ============================================================

    private function apply_performance_tweaks(): void
    {
        // Kill emoji script
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Strip jQuery Migrate
        add_action('wp_default_scripts', function ($scripts) {
            if (!is_admin() && !empty($scripts->registered['jquery'])) {
                $scripts->registered['jquery']->deps = array_diff(
                    $scripts->registered['jquery']->deps,
                    ['jquery-migrate']
                );
            }
        });

        // Defer non-critical JS
        add_filter('script_loader_tag', function ($tag, $handle) {
            if (in_array($handle, ['senoobar-app', 'senoobar-push'], true)) {
                return str_replace(' src', ' defer src', $tag);
            }
            return $tag;
        }, 10, 2);

        // Native lazy loading
        add_filter('wp_content_img_tag', function ($img) {
            return str_replace('<img ', '<img loading="lazy" ', $img);
        });

        // Remove WP version
        remove_action('wp_head', 'wp_generator');

        // Disable XML-RPC
        add_filter('xmlrpc_enabled', '__return_false');

        // Remove REST API user endpoint (no user enumeration)
        add_filter('rest_endpoints', function ($endpoints) {
            if (isset($endpoints['/wp/v2/users'])) {
                unset($endpoints['/wp/v2/users']);
            }
            if (isset($endpoints['/wp/v2/users/(?P<id>[\\d]+)'])) {
                unset($endpoints['/wp/v2/users/(?P<id>[\\d]+)']);
            }
            return $endpoints;
        });
    }
}
