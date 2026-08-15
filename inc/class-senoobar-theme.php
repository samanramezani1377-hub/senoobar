<?php
/**
 * Senoobar Theme — Main Class (v2)
 * Deep Green Palette + Vazirmatn
 * Based on Figma Make / senoobar2
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
        $this->setup();
        $this->assets();
        $this->woo();
        $this->menus();
        $this->customizer();
        $this->pwa();
        $this->sw_rewrite();
        $this->perf();
    }

    // ─── Theme Setup ──────────────────────────────
    private function setup() {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('custom-logo', [
            'height'      => 60,
            'width'       => 180,
            'flex-height' => true,
            'flex-width'  => true,
        ]);
        add_theme_support('html5', [
            'search-form', 'comment-form', 'comment-list',
            'gallery', 'caption', 'style', 'script',
        ]);
        add_theme_support('responsive-embeds');
        add_theme_support('wp-block-styles');
        add_theme_support('align-wide');

        // Image sizes
        add_image_size('senoobar-product-thumb', 400, 400, true);
        add_image_size('senoobar-product-medium', 600, 600, true);
        add_image_size('senoobar-product-large', 1200, 800, true);
        add_image_size('senoobar-hero', 800, 1000, true);

        add_action('pre_get_posts', function ($query) {
            if (!is_admin() && $query->is_main_query() && $query->is_search()) {
                $query->set('post_type', 'product');
                $query->set('posts_per_page', 30);
                
                // Persian search normalization
                $search_term = $query->get('s');
                if (!empty($search_term)) {
                    $normalized = senoobar_normalize_persian_search($search_term);
                    if ($normalized !== $search_term) {
                        $query->set('s', $normalized);
                    }
                }
            }
        });
    }

    // ─── Navigation ───────────────────────────────
    private function menus() {
        register_nav_menus([
            'primary' => __('منوی اصلی', 'senoobar'),
            'footer'  => __('منوی فوتر', 'senoobar'),
        ]);
    }

    // ─── WooCommerce ──────────────────────────────
    private function woo() {
        if (!class_exists('WooCommerce')) return;

        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');

        // Kill default Woo styles
        add_filter('woocommerce_enqueue_styles', '__return_empty_array');

        // Cart count in header (AJAX fragments)
        add_filter('woocommerce_add_to_cart_fragments', function ($f) {
            ob_start();
            echo '<span class="cart-badge">' . WC()->cart->get_cart_contents_count() . '</span>';
            $f['.cart-badge'] = ob_get_clean();
            return $f;
        });
    }

    // ─── PWA ──────────────────────────────────────
    private function pwa() {
        add_action('wp_head', function () {
            echo '<meta name="mobile-web-app-capable" content="yes">';
            echo '<meta name="apple-mobile-web-app-capable" content="yes">';
            echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr(get_bloginfo('name')) . '">';
            echo '<link rel="apple-touch-icon" href="' . esc_url(SENOOBAR_URI . '/assets/images/logo.png') . '">';
        });
}
    
    // ─── Service Worker Rewrite ─────────────────────
    private function sw_rewrite() {
        add_action('init', function () {
            add_rewrite_rule('^sw\.js$', 'index.php?senoobar_sw=1', 'top');
            add_rewrite_rule('^manifest\.json$', 'index.php?senoobar_manifest=1', 'top');
        });
        
        add_filter('query_vars', function ($vars) {
            $vars[] = 'senoobar_sw';
            $vars[] = 'senoobar_manifest';
            return $vars;
        });
        
        add_action('template_redirect', function () {
            if (get_query_var('senoobar_sw') === '1') {
                $sw_file = get_template_directory() . '/sw.php';
                if (file_exists($sw_file)) {
                    include $sw_file;
                    exit;
                }
            }
            
            if (get_query_var('senoobar_manifest') === '1') {
                $manifest_file = get_template_directory() . '/manifest.php';
                if (file_exists($manifest_file)) {
                    include $manifest_file;
                    exit;
                }
            }
        });
    }
    
    // ─── Performance ──────────────────────────────
    private function perf() {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');

        // Defer theme JS
        add_filter('script_loader_tag', function ($t, $h) {
            if (in_array($h, ['senoobar-app', 'senoobar-push'])) {
                return str_replace(' src', ' defer src', $t);
            }
            return $t;
        }, 10, 2);
    }

    // ─── Customizer ───────────────────────────────
    private function customizer() {
        add_action('customize_register', function ($c) {
            // Announcement
            $c->add_setting('senoobar_announcement', [
                'default' => '🚚 ارسال به سراسر کشور | 💳 خرید اقساطی ۳ ماهه بدون کارمزد | 🕐 شنبه تا پنجشنبه ۱۰ صبح تا ۹ شب',
            ]);
            $c->add_control('senoobar_announcement', [
                'label'   => 'متن اعلان',
                'section' => 'title_tagline',
                'type'    => 'text',
            ]);

            // Hero
            $c->add_section('senoobar_hero', [
                'title'    => 'هیرو',
                'priority' => 30,
            ]);
            $c->add_setting('senoobar_hero_title', ['default' => 'میلمان خانه‌ای درخور شما']);
            $c->add_control('senoobar_hero_title', [
                'label'   => 'عنوان',
                'section' => 'senoobar_hero',
                'type'    => 'text',
            ]);
            $c->add_setting('senoobar_hero_subtitle', ['default' => 'تجربه‌ای متفاوت از راحتی و زیبایی']);
            $c->add_control('senoobar_hero_subtitle', [
                'label'   => 'زیرعنوان',
                'section' => 'senoobar_hero',
                'type'    => 'textarea',
            ]);
            foreach (['senoobar_hero_img1' => 'تصویر یک', 'senoobar_hero_img2' => 'تصویر دو'] as $id => $l) {
                $c->add_setting($id);
                $c->add_control(new WP_Customize_Media_Control($c, $id, [
                    'label'   => $l,
                    'section' => 'senoobar_hero',
                ]));
            }

            // Promo Banners
            $c->add_section('senoobar_promo', [
                'title'    => 'بنرهای تبلیغاتی',
                'priority' => 35,
            ]);
            foreach (['senoobar_promo_img1' => 'بنر ۱', 'senoobar_promo_img2' => 'بنر ۲'] as $id => $l) {
                $c->add_setting($id);
                $c->add_control(new WP_Customize_Media_Control($c, $id, [
                    'label'   => $l,
                    'section' => 'senoobar_promo',
                ]));
            }

            // Gallery
            $c->add_section('senoobar_gallery', [
                'title'    => 'گالری',
                'priority' => 40,
            ]);
            for ($i = 1; $i <= 8; $i++) {
                $c->add_setting("senoobar_gallery_img{$i}");
                $c->add_control(new WP_Customize_Media_Control($c, "senoobar_gallery_img{$i}", [
                    'label'   => "تصویر {$i}",
                    'section' => 'senoobar_gallery',
                ]));
            }

            // Video thumbnail
            $c->add_setting('senoobar_video_thumb');
            $c->add_control(new WP_Customize_Media_Control($c, 'senoobar_video_thumb', [
                'label'   => 'تصویر ویدیو',
                'section' => 'senoobar_hero',
            ]));

            // Services
            $c->add_section('senoobar_services', [
                'title'    => 'خدمات',
                'priority' => 32,
            ]);
            $service_defaults = [
                ['icon' => '🚚', 'title' => 'ارسال به سراسر کشور', 'desc' => 'ارسال محصولات به سراسر ایران'],
                ['icon' => '💳', 'title' => 'خرید اقساطی', 'desc' => 'امکان خرید اقساطی ۳ ماهه بدون کارمزد'],
                ['icon' => '🛡️', 'title' => 'ضمانت اصالت کالا', 'desc' => '۷ روز ضمانت بازگشت'],
                ['icon' => '🕐', 'title' => 'ساعات کاری', 'desc' => 'شنبه تا پنجشنبه، ۱۰ صبح تا ۹ شب'],
            ];
            $si = 1;
            foreach ($service_defaults as $svc) {
                foreach (['icon', 'title', 'desc'] as $k) {
                    $c->add_setting("senoobar_service{$si}_{$k}", ['default' => $svc[$k]]);
                    $c->add_control("senoobar_service{$si}_{$k}", [
                        'label'   => "خدمت {$si} - " . ($k === 'icon' ? 'آیکون' : ($k === 'title' ? 'عنوان' : 'توضیح')),
                        'section' => 'senoobar_services',
                        'type'    => $k === 'desc' ? 'textarea' : 'text',
                    ]);
                }
                $si++;
            }

            // Testimonials
            $c->add_section('senoobar_tm', [
                'title'    => 'نظرات مشتریان',
                'priority' => 38,
            ]);
            for ($i = 1; $i <= 3; $i++) {
                foreach (['stars' => '5', 'text' => '', 'author' => ''] as $k => $df) {
                    $c->add_setting("senoobar_tm{$i}_{$k}", ['default' => $df]);
                    $c->add_control("senoobar_tm{$i}_{$k}", [
                        'label'   => "نظر {$i} - {$k}",
                        'section' => 'senoobar_tm',
                        'type'    => $k === 'text' ? 'textarea' : 'text',
                    ]);
                }
            }

            // Push Notifications
            $c->add_section('senoobar_push', [
                'title'    => '🔔 پوش نوتیفیکیشن',
                'priority' => 37,
            ]);
            $c->add_setting('senoobar_push_vapid_public', ['default' => '']);
            $c->add_control('senoobar_push_vapid_public', [
                'label'       => 'VAPID Public Key',
                'description' => 'کلید عمومی VAPID برای Web Push (Base64 encoded).',
                'section'     => 'senoobar_push',
                'type'        => 'textarea',
            ]);
            $c->add_setting('senoobar_push_api_url', ['default' => '']);
            $c->add_control('senoobar_push_api_url', [
                'label'       => 'API URL سرویس پوش',
                'description' => 'مثلاً https://fcm.googleapis.com/fcm/send یا endpoint سرویسirds شخص ثالث.',
                'section'     => 'senoobar_push',
                'type'        => 'text',
            ]);
            $c->add_setting('senoobar_push_api_key', ['default' => '']);
            $c->add_control('senoobar_push_api_key', [
                'label'       => 'API Key / Server Key',
                'description' => 'کلید سرور سرویس پوش.',
                'section'     => 'senoobar_push',
                'type'        => 'password',
            ]);
            $c->add_setting('senoobar_push_btn_text', ['default' => 'دریافت نوتیفیکیشن']);
            $c->add_control('senoobar_push_btn_text', [
                'label'   => 'متن دکمه سابسکریپت',
                'section' => 'senoobar_push',
                'type'    => 'text',
            ]);
            $c->add_setting('senoobar_push_subscribed_btn_text', ['default' => 'لغو نوتیفیکیشن']);
            $c->add_control('senoobar_push_subscribed_btn_text', [
                'label'   => 'متن دکمه لغو سابسکریپت',
                'section' => 'senoobar_push',
                'type'    => 'text',
            ]);

            // Cart
            $c->add_section('senoobar_cart', [
                'title'    => '🛒 سبد خرید',
                'priority' => 85,
            ]);
            $cart_settings = [
                'cart_title'       => ['default' => 'سبد خرید شما', 'type' => 'text'],
                'cart_empty_title' => ['default' => 'سبد خرید شما خالی است', 'type' => 'text'],
                'cart_empty_text'  => ['default' => 'محصولات مورد نظر خود را انتخاب کنید.', 'type' => 'textarea'],
                'cart_empty_btn'   => ['default' => 'مشاهده محصولات', 'type' => 'text'],
                'checkout_btn'     => ['default' => 'ادامه جهت تسویه حساب', 'type' => 'text'],
                'continue_btn'     => ['default' => 'ادامه خرید', 'type' => 'text'],
                'support_phone'    => ['default' => '۰۹۱۳۰۲۰۵۸۹۸', 'type' => 'text'],
                'support_title'    => ['default' => 'سوالی دارید؟ ما در کنار شما هستیم', 'type' => 'text'],
                'support_text'     => ['default' => 'برای راهنمایی در خرید یا پیگیری سفارش‌تان با پشتیبانی ما تماس بگیرید.', 'type' => 'textarea'],
                'support_btn'      => ['default' => 'تماس با پشتیبانی', 'type' => 'text'],
            ];
            foreach ($cart_settings as $k => $cfg) {
                $c->add_setting("senoobar_cart_{$k}", ['default' => $cfg['default']]);
                $c->add_control("senoobar_cart_{$k}", [
                    'label'   => $k,
                    'section' => 'senoobar_cart',
                    'type'    => $cfg['type'],
                ]);
            }

            // Footer
            $c->add_section('senoobar_footer', [
                'title'    => 'فوتر',
                'priority' => 90,
            ]);
                        $footer_settings = [
                'about'   => ['default' => 'فروشگاه صنوبر یک فروشگاه تخصصی در زمینه سرویس خواب، تشک و مبلمان در اصفهان است. ما انواع تشک، سرویس خواب، تخت خواب، مبل و مبلمان منزل را با تنوع بالا و کیفیت مناسب ارائه می‌دهیم.', 'type' => 'textarea'],
                'phone1'  => ['default' => '۰۹۱۳۰۲۰۵۸۹۸', 'type' => 'text'],
                'phone2'  => ['default' => '۰۹۱۳۰۲۰۵۸۶۸', 'type' => 'text'],
                'phone3'  => ['default' => '۰۹۱۳۰۲۰۵۳۲۳', 'type' => 'text'],
                'address' => ['default' => 'اصفهان، شهرک صنعتی دولت‌آباد، خیابان شماره ۱۰ (خیام)، فروشگاه صنوبر', 'type' => 'textarea'],
                'hours'   => ['default' => 'شنبه تا پنجشنبه، ۱۰ صبح تا ۹ شب', 'type' => 'text'],
                'telegram'   => ['default' => '', 'type' => 'text'],
                'instagram'  => ['default' => '', 'type' => 'text'],
                'whatsapp'   => ['default' => '', 'type' => 'text'],
            ];
            foreach ($footer_settings as $f => $cfg) {
                $c->add_setting("senoobar_footer_{$f}", isset($cfg['default']) ? ['default' => $cfg['default']] : []);
                $c->add_control("senoobar_footer_{$f}", [
                    'label'   => $f,
                    'section' => 'senoobar_footer',
                    'type'    => $cfg['type'],
                ]);
            }

            // E-Namad / Trust Badges
            $c->add_section('senoobar_trust', [
                'title'    => 'اعتماد و نمادها',
                'priority' => 95,
            ]);
            $c->add_setting('senoobar_enamad_code', [
                'default'           => '',
                'sanitize_callback' => 'senoobar_sanitize_enamad_code',
            ]);
            $c->add_control(new WP_Customize_Control($c, 'senoobar_enamad_code', [
                'label'       => 'کد نماد اعتماد الکترونیکی',
                'description' => 'کد امبد/اسنیپت رسمی نماد اعتماد الکترونیکی را اینجا قرار دهید. خالی بگذارید برای عدم نمایش.',
                'section'     => 'senoobar_trust',
                'type'        => 'textarea',
            ]));

            // Section Titles
            $c->add_section('senoobar_sections', [
                'title'    => 'عناوین بخش‌ها',
                'priority' => 33,
            ]);
            $section_settings = [
                'cats_title'       => 'دسته‌بندی‌ها',
                'featured_title'   => 'محصولات ویژه',
                'featured_desc'    => 'بهترین انتخاب‌های هفته با تخفیف‌های استثنایی',
                'bestsellers_title'=> 'پرفروش‌ترین‌ها',
                'bestsellers_desc' => '',
                'gallery_title'    => 'ایده‌هایی برای خانه شما',
                'blog_title'       => 'آخرین مقالات',
                'blog_desc'        => '',
                'newsletter_title' => 'در خبرنامه صنوبر عضو شوید!',
                'newsletter_desc'  => 'از تخفیف‌ها و جدیدترین محصولات باخبر شوید.',
            ];
            foreach ($section_settings as $k => $v) {
                $c->add_setting("senoobar_section_{$k}", ['default' => $v]);
                $c->add_control("senoobar_section_{$k}", [
                    'label'   => $k,
                    'section' => 'senoobar_sections',
                    'type'    => str_contains($k, 'desc') ? 'textarea' : 'text',
                ]);
            }

            // ─── Categories Grid Settings ─────────────
            $c->add_section('senoobar_cats', [
                'title'       => '💎 دسته‌بندی‌های صفحه اصلی',
                'description' => 'انتخاب کنید کدام دسته‌بندی‌ها نمایش داده شوند و با چه ترتیبی. عدد اولویت کمتر = جلوتر.',
                'priority'    => 28,
            ]);

            // ── Columns per device ──
            $c->add_setting('senoobar_cats_cols_desktop', ['default' => 6]);
            $c->add_control('senoobar_cats_cols_desktop', [
                'label'   => 'تعداد ستون — دسکتاپ',
                'section' => 'senoobar_cats',
                'type'    => 'select',
                'choices' => ['2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶'],
            ]);
            $c->add_setting('senoobar_cats_cols_tablet', ['default' => 3]);
            $c->add_control('senoobar_cats_cols_tablet', [
                'label'   => 'تعداد ستون — تبلت',
                'section' => 'senoobar_cats',
                'type'    => 'select',
                'choices' => ['1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴'],
            ]);
            $c->add_setting('senoobar_cats_cols_mobile', ['default' => 2]);
            $c->add_control('senoobar_cats_cols_mobile', [
                'label'   => 'تعداد ستون — موبایل',
                'section' => 'senoobar_cats',
                'type'    => 'select',
                'choices' => ['1' => '۱', '2' => '۲', '3' => '۳'],
            ]);

            // ── Per-category: enabled + priority ──
            // We add controls for the first 15 WooCommerce product categories
            if (class_exists('WooCommerce')) {
                $all_cats = get_terms([
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                    'number'     => 20,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                ]);
                if (!is_wp_error($all_cats) && !empty($all_cats)) {
                    // Separator label
                    $c->add_control(new WP_Customize_Control($c, 'senoobar_cats_sep', [
                        'label'       => '⬇️ انتخاب و ترتیب دسته‌ها ⬇️',
                        'description' => 'هر دسته را فعال/غیرفعال کنید و با عدد اولویت ترتیبش را مشخص کنید.',
                        'section'     => 'senoobar_cats',
                        'type'        => 'hidden',
                    ]));

                    $idx = 0;
                    foreach ($all_cats as $cat) {
                        $idx++;
                        $cat_id = $cat->term_id;
                        $cat_name = $cat->name;
                        $cat_count = $cat->count;

                        // Enabled checkbox
                        $c->add_setting("senoobar_cat_{$cat_id}_enabled", ['default' => ($idx <= 6) ? '1' : '']);
                        $c->add_control("senoobar_cat_{$cat_id}_enabled", [
                            'label'       => "✅ {$cat_name} ({$cat_count} محصول)",
                            'section'     => 'senoobar_cats',
                            'type'        => 'checkbox',
                        ]);

                        // Priority number
                        $c->add_setting("senoobar_cat_{$cat_id}_priority", ['default' => $idx]);
                        $c->add_control("senoobar_cat_{$cat_id}_priority", [
                            'label'       => "   ↳ اولویت (عدد کمتر = اول بالاتر)",
                            'section'     => 'senoobar_cats',
                            'type'        => 'number',
                            'input_attrs' => ['min' => 1, 'max' => 99, 'step' => 1],
                        ]);
                    }
                }
            } else {
                // No WooCommerce — show a notice
                $c->add_control(new WP_Customize_Control($c, 'senoobar_cats_no_wc', [
                    'label'       => '⚠️ ووکامرس فعال نیست',
                    'description' => 'برای مدیریت دسته‌بندی‌ها، افزونه ووکامرس را نصب و فعال کنید.',
                    'section'     => 'senoobar_cats',
                    'type'        => 'hidden',
                ]));
            }

            // Brand Story
            $c->add_setting('senoobar_story_title', ['default' => 'داستان صنوبر']);
            $c->add_control('senoobar_story_title', ['label' => 'عنوان داستان', 'section' => 'senoobar_hero', 'type' => 'text']);
            $c->add_setting('senoobar_story_text', ['default' => 'همراه شما در ساختن خانه‌ای زیباتر از مبلمان با کیفیت و طراحی مدرن']);
            $c->add_control('senoobar_story_text', ['label' => 'متن داستان', 'section' => 'senoobar_hero', 'type' => 'textarea']);
            $c->add_setting('senoobar_story_btn', ['default' => 'تماشای ویدیو']);
            $c->add_control('senoobar_story_btn', ['label' => 'متن دکمه', 'section' => 'senoobar_hero', 'type' => 'text']);
        });
    }

    // ─── Assets ───────────────────────────────────
    private function assets() {
        add_action('wp_enqueue_scripts', function () {
            // Critical CSS (inline)
            wp_enqueue_style('senoobar-critical', SENOOBAR_URI . '/assets/css/critical.css', [], SENOOBAR_VERSION);
            // Main CSS
            wp_enqueue_style('senoobar-main', SENOOBAR_URI . '/assets/css/main.css', ['senoobar-critical'], SENOOBAR_VERSION);
            // RTL
            if (is_rtl()) {
                wp_enqueue_style('senoobar-rtl', SENOOBAR_URI . '/assets/css/rtl.css', ['senoobar-main'], SENOOBAR_VERSION);
            }
            // Shop CSS
            $is_cart_page = class_exists('WooCommerce') && is_cart();
            $is_checkout_page = class_exists('WooCommerce') && is_checkout();
            $is_account_page = class_exists('WooCommerce') && is_account_page();
            
            if (class_exists('WooCommerce') && (is_shop() || is_product_category() || is_product_tag() || is_search() || $is_cart_page || $is_checkout_page || $is_account_page)) {
                wp_enqueue_style('senoobar-shop', SENOOBAR_URI . '/assets/css/shop.css', ['senoobar-main'], SENOOBAR_VERSION);
            }
            // Cart CSS — always load when WooCommerce is active (cart is reached via
            // many paths; relying only on is_cart() caused it to be missed).
            if (class_exists('WooCommerce')) {
                wp_enqueue_style('senoobar-cart', SENOOBAR_URI . '/assets/css/cart.css', ['senoobar-main'], SENOOBAR_VERSION);
            }

            // Mobile bottom navigation (always loaded; hidden on desktop via CSS).
            wp_enqueue_style('senoobar-bottom-nav', SENOOBAR_URI . '/assets/css/bottom-nav.css', ['senoobar-main'], SENOOBAR_VERSION);
            // Checkout CSS
            if ($is_checkout_page) {
                wp_enqueue_style('senoobar-checkout', SENOOBAR_URI . '/assets/css/checkout.css', ['senoobar-main'], SENOOBAR_VERSION);
            }
            // Account CSS — always load when WooCommerce is active (the account page
            // is reached via template_include to senoobar-account.php, so relying on
            // is_account_page() caused it to be missed and broke mobile layout).
            if (class_exists('WooCommerce')) {
                wp_enqueue_style('senoobar-account', SENOOBAR_URI . '/assets/css/account.css', ['senoobar-main'], SENOOBAR_VERSION);
            }
            // Wishlist CSS + JS (wishlist page + anywhere the heart button appears)
            if (class_exists('WooCommerce')) {
                wp_enqueue_style('senoobar-wishlist', SENOOBAR_URI . '/assets/css/wishlist.css', ['senoobar-main'], SENOOBAR_VERSION);
                wp_enqueue_script('senoobar-wishlist', SENOOBAR_URI . '/assets/js/wishlist.js', ['senoobar-app'], SENOOBAR_VERSION, true);
            }
            // JS
            wp_enqueue_script('senoobar-app', SENOOBAR_URI . '/assets/js/app.js', [], SENOOBAR_VERSION, true);
            wp_localize_script('senoobar-app', 'senoobarData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'cartUrl' => class_exists('WooCommerce') ? wc_get_cart_url() : '',
                'nonce'   => wp_create_nonce('senoobar_cart_nonce'),
                'isRTL'   => is_rtl(),
                'siteUrl'   => home_url(),
                'shopUrl'   => class_exists('WooCommerce') ? get_permalink(wc_get_page_id('shop')) : home_url('/'),
                'loggedIn'  => is_user_logged_in(),
            ]);
            // Cart JS — always load when WooCommerce is active (so the AJAX +/- /
            // remove handlers are present regardless of how is_cart() resolves).
            if (class_exists('WooCommerce')) {
                wp_enqueue_script('senoobar-cart', SENOOBAR_URI . '/assets/js/cart.js', ['senoobar-app'], SENOOBAR_VERSION, true);
                wp_localize_script('senoobar-cart', 'SenoobarCart', [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce'   => wp_create_nonce('senoobar_cart_nonce'),
                ]);
            }
            // Push JS
            wp_enqueue_script('senoobar-push', SENOOBAR_URI . '/assets/js/push.js', ['senoobar-app'], SENOOBAR_VERSION, true);
            wp_localize_script('senoobar-push', 'senoobarPush', [
                'ajaxUrl'        => admin_url('admin-ajax.php'),
                'nonce'          => wp_create_nonce('senoobar_push_nonce'),
                'publicKey'      => get_theme_mod('senoobar_push_vapid_public', ''),
                'btnText'        => get_theme_mod('senoobar_push_btn_text', 'دریافت نوتیفیکیشن'),
                'subscribedText' => get_theme_mod('senoobar_push_subscribed_btn_text', 'لغو نوتیفیکیشن'),
                'isRTL'          => is_rtl(),
                'siteUrl'        => home_url(),
            ]);
            // Checkout JS
            $is_checkout_page = class_exists('WooCommerce') && is_checkout();
            if ($is_checkout_page) {
                wp_enqueue_script('senoobar-checkout', SENOOBAR_URI . '/assets/js/checkout.js', ['senoobar-app'], SENOOBAR_VERSION, true);
            }
            // Shop filter JS (also on search results, so category/price filtering
            // works there too)
            if (class_exists('WooCommerce') && (is_shop() || is_product_category() || is_product_tag() || is_search())) {
                wp_enqueue_script('senoobar-shop-filters', SENOOBAR_URI . '/assets/js/shop-filters.js', ['senoobar-app'], SENOOBAR_VERSION, true);
            }
            // Product buy-box JS (single product: button -> stepper behavior)
            if (class_exists('WooCommerce') && is_product()) {
                wp_enqueue_script('senoobar-product-buy-box', SENOOBAR_URI . '/assets/js/product-buy-box.js', ['senoobar-app'], SENOOBAR_VERSION, true);
            }
            // Newsletter JS
            wp_enqueue_script('senoobar-newsletter', SENOOBAR_URI . '/assets/js/newsletter.js', ['senoobar-app'], SENOOBAR_VERSION, true);
        });

        // Vazirmatn font
        add_action('wp_head', function () {
            echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
            echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
            // Using Google Fonts for Vazirmatn
            echo '<style>@import url("https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap");</style>';
        }, 1);
    }
}

/**
 * Sanitize E-Namad code - only allow safe HTML for trusted badges
 *
 * @param string $input Raw input from Customizer
 * @return string Sanitized output
 */
function senoobar_sanitize_enamad_code(string $input): string {
    if (empty($input)) {
        return '';
    }

    // Allow only specific safe tags and attributes for E-Namad embeds
    $allowed_tags = [
        'a'      => ['href' => [], 'title' => [], 'target' => [], 'rel' => [], 'id' => [], 'class' => []],
        'img'    => ['src' => [], 'alt' => [], 'width' => [], 'height' => [], 'style' => [], 'id' => [], 'class' => []],
        'script' => ['src' => [], 'async' => [], 'defer' => [], 'type' => [], 'id' => [], 'class' => []],
        'div'    => ['id' => [], 'class' => [], 'style' => []],
        'span'   => ['id' => [], 'class' => [], 'style' => []],
        'iframe' => ['src' => [], 'width' => [], 'height' => [], 'frameborder' => [], 'scrolling' => [], 'style' => [], 'id' => [], 'class' => []],
    ];

    $sanitized = wp_kses($input, $allowed_tags);

    // Additional safety: ensure scripts/iframes only from trusted domains
    // This is a basic check - in production, you might want more sophisticated validation
    return $sanitized;
}

/**
 * Normalize Persian/Arabic search query for consistent matching.
 * 
 * Normalizes:
 * - Arabic ي → Persian ی
 * - Arabic ك → Persian ک
 * - Arabic/Persian digits → English digits
 * - Zero-width characters (ZWNJ/ZWJ) normalized
 * - Excessive whitespace trimmed
 * 
 * Does NOT modify original stored data - only normalizes the search query.
 * 
 * @param string $query Raw search query
 * @return string Normalized query
 */
function senoobar_normalize_persian_search(string $query): string {
    if (empty($query)) {
        return $query;
    }
    
    // Arabic to Persian character mapping
    $arabic_to_persian = [
        'ي' => 'ی',  // Arabic yeh -> Persian yeh
        'ك' => 'ک',  // Arabic kaf -> Persian kaf
        '٠' => '0',  // Arabic-Indic digits
        '١' => '1',
        '٢' => '2',
        '٣' => '3',
        '٤' => '4',
        '٥' => '5',
        '٦' => '6',
        '٧' => '7',
        '٨' => '8',
        '٩' => '9',
        '۰' => '0',  // Extended Arabic-Indic digits
        '۱' => '1',
        '۲' => '2',
        '۳' => '3',
        '۴' => '4',
        '۵' => '5',
        '۶' => '6',
        '۷' => '7',
        '۸' => '8',
        '۹' => '9',
    ];
    
    // Replace Arabic chars with Persian equivalents
    $query = strtr($query, $arabic_to_persian);
    
    // Normalize zero-width characters
    // ZWNJ (U+200C) - keep as-is for Persian compound words (نیم‌فاصله)
    // ZWJ (U+200D) - remove
    $query = str_replace("\u{200D}", '', $query); // Remove ZWJ
    
    // Normalize whitespace - collapse multiple spaces, trim
    $query = preg_replace('/\s+/u', ' ', $query);
    $query = trim($query);
    
    return $query;
}
