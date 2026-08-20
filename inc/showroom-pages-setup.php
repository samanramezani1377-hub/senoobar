<?php
/**
 * Senoobar — نمایشگاه‌های صفحه اصلی (اتاق خواب + مبلمان).
 *
 * دو صفحه‌ی «نمایشگاهی» مجزا برای دو بنر صفحه اصلی می‌سازد:
 *   ۱) «اتاق خواب رویایی شما» → template-bedroom.php
 *   ۲) «مبلمان مدرن و راحت»    → template-furniture.php
 *
 * هر صفحه:
 *   - از Customizer (سفارشی‌سازی → نمایشگاه‌ها) کاملاً قابل تنظیم است:
 *     انتخاب دسته‌بندی ووکامرس (dropdown)، عنوان، زیرعنوان، تصویر هدر و متن‌ها.
 *   - محصولات را به‌صورت داینامیک از دسته‌ی انتخابی می‌کشد؛ اگر چیزی انتخاب
 *     نشده یا ووکامرس فعال نباشد، با تصاویر استاتیک قالب (assets/images)
 *     یک نمایشگاه پیش‌فرض نمایش می‌دهد.
 *
 * الگوی ساخت صفحات idempotent است (مانند legal-pages-setup.php) و ساخت آن
 * از طریق `after_switch_theme` فلاش نمی‌شود تا بازنگری‌ها دست نخورده بمانند.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * پیکربندی ثابت دو نمایشگاه.
 * `slug` نام یکتای صفحه/گزینه‌هاست. `template` فایل قالب، `title` عنوان پیش‌فرض
 * صفحه و `cat_fallback` تصویر نمایشگاهی که وقتی دسته‌ای انتخاب نشده استفاده می‌شود.
 */
function senoobar_showroom_configs(): array {
    return [
        'bedroom' => [
            'slug'     => 'bedroom',
            'template' => 'template-bedroom.php',
            'title'    => 'اتاق خواب رویایی شما',
            'cat_fallback' => 'hero-2.jpg',
        ],
        'furniture' => [
            'slug'     => 'furniture',
            'template' => 'template-furniture.php',
            'title'    => 'مبلمان مدرن و راحت',
            'cat_fallback' => 'hero-1.jpg',
        ],
    ];
}

/* ════════════════════════════════════════════════════════════════
   ۱. ثبت قالب‌های صفحه (تا در انتخاب قالبِ هر برگه دیده شوند)
   ════════════════════════════════════════════════════════════════ */
function senoobar_register_showroom_templates( $templates ) {
    foreach ( senoobar_showroom_configs() as $cfg ) {
        $templates[ $cfg['template'] ] = 'نمایشگاه ' . $cfg['title'];
    }
    return $templates;
}
add_filter( 'theme_page_templates', 'senoobar_register_showroom_templates' );

/* ════════════════════════════════════════════════════════════════
   ۲. ساخت خودکار دو صفحه (فقط یک‌بار)
   ════════════════════════════════════════════════════════════════ */
function senoobar_ensure_showroom_pages(): void {
    if ( get_option( 'senoobar_showroom_pages_created' ) ) {
        return;
    }

    foreach ( senoobar_showroom_configs() as $key => $cfg ) {
        $option = 'senoobar_showroom_' . $key . '_page_id';

        // از قبل ساخته شده؟
        if ( (int) get_option( $option ) ) {
            continue;
        }

        // شاید صفحه‌ای با همین قالب از قبل موجود باشد.
        $existing = get_pages( [
            'meta_key'   => '_wp_page_template',
            'meta_value' => $cfg['template'],
            'number'     => 1,
        ] );

        if ( ! empty( $existing ) ) {
            update_option( $option, $existing[0]->ID );
            continue;
        }

        $page_id = wp_insert_post( [
            'post_title'   => $cfg['title'],
            'post_type'    => 'page',
            'post_status'  => 'publish',
            'post_name'    => $cfg['slug'],
            'post_content' => '',
        ], true );

        if ( ! is_wp_error( $page_id ) ) {
            update_post_meta( $page_id, '_wp_page_template', $cfg['template'] );
            update_option( $option, $page_id );
        }
    }

    update_option( 'senoobar_showroom_pages_created', 1 );
}
add_action( 'after_setup_theme', 'senoobar_ensure_showroom_pages', 31 );

/* ════════════════════════════════════════════════════════════════
   ۳. توابع کمکی برای گرفتن آدرس صفحه و دسته‌ی انتخابی
   ════════════════════════════════════════════════════════════════ */
function senoobar_showroom_page_url( string $key ): string {
    $id = (int) get_option( 'senoobar_showroom_' . $key . '_page_id' );
    if ( $id && 'publish' === get_post_status( $id ) ) {
        return get_permalink( $id );
    }
    return '';
}

/**
 * دسته‌ی انتخابی برای یک نمایشگاه (term_id یا 0).
 * از گزینه‌ی Customizer خوانده می‌شود؛ اگر ووکامرس نباشد یا دسته حذف شده باشد، 0 برمی‌گردد.
 */
function senoobar_showroom_category( string $key ): int {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return 0;
    }
    $term_id = (int) get_theme_mod( 'senoobar_showroom_' . $key . '_cat', 0 );
    if ( $term_id && term_exists( $term_id, 'product_cat' ) ) {
        return $term_id;
    }
    return 0;
}

/**
 * گالری نمایشگاهی برای یک دسته:
 *  - اگر دسته‌ای انتخاب شده → محصولات همان دسته (با تصویرشان).
 *  - وگرنه → اسلایدهای استاتیک از تصاویر قالب.
 *
 * خروجی: آرایه‌ای از اسلایدها. هر اسلاید:
 *   [ title, image, link, price, caption ]
 */
function senoobar_showroom_slides( string $key ): array {
    $term_id = senoobar_showroom_category( $key );
    $cfg     = senoobar_showroom_configs()['bedroom'];
    if ( 'furniture' === $key ) {
        $cfg = senoobar_showroom_configs()['furniture'];
    }

    if ( $term_id ) {
        $products = wc_get_products( [
            'status'   => 'publish',
            'limit'    => 24,
            'category' => [ $term_id ],
            'orderby'  => 'date',
            'order'    => 'DESC',
        ] );

        $slides = [];
        foreach ( $products as $p ) {
            $image = wp_get_attachment_image_url( $p->get_image_id(), 'large' );
            $slides[] = [
                'title'   => $p->get_name(),
                'image'   => $image ? $image : wc_placeholder_img_src(),
                'link'    => get_permalink( $p->get_id() ),
                'price'   => $p->get_price_html(),
                'caption' => wp_trim_words( wp_strip_all_tags( $p->get_short_description() ?: $p->get_name() ), 18 ),
            ];
        }

        if ( ! empty( $slides ) ) {
            return $slides;
        }
    }

    // ── Fallback: نمایشگاه استاتیک از تصاویر قالب ──
    $titles = [
        'bedroom'  => [ 'سرویس خواب لاکچری', 'تشک طبی فنری', 'تخت خواب مدرن', 'ست کامل اتاق خواب' ],
        'furniture' => [ 'مبل راحتی مدرن', 'سرویس مبلمان کلاسیک', 'میز جلو مبلی', 'مبلمان پذیرایی' ],
    ];
    $imgs = [ 'hero-1.jpg', 'hero-2.jpg', 'featured-sofa.jpg', 'featured-dining.jpg', 'promo-bedroom.jpg', 'cat-bed.jpg', 'cat-mattress.jpg', 'featured-tv-table.jpg' ];

    $list = $titles[ $key ] ?? $titles['bedroom'];
    $slides = [];
    foreach ( $list as $i => $t ) {
        $img = $imgs[ $i % count( $imgs ) ];
        $slides[] = [
            'title'   => $t,
            'image'   => SENOOBAR_URI . '/assets/images/' . $img,
            'link'    => '',
            'price'   => '',
            'caption' => esc_html( $t ) . ' با طراحی خاص صنوبر',
        ];
    }
    return $slides;
}

/* ════════════════════════════════════════════════════════════════
   ۴. تنظیمات Customizer (سفارشی‌سازی → نمایشگاه‌ها)
   ════════════════════════════════════════════════════════════════ */
function senoobar_showroom_customizer( $wp_customize ) {
    $wp_customize->add_section( 'senoobar_showroom', [
        'title'       => '🖼️ نمایشگاه‌ها',
        'description' => 'تنظیم دو صفحه‌ی نمایشگاهی «اتاق خواب» و «مبلمان» که از بنرهای صفحه اصلی لینک می‌شوند.',
        'priority'    => 29,
    ] );

    // برای هر دو نمایشگاه یک «انتخاب دسته» + فیلدهای متنی.
    foreach ( senoobar_showroom_configs() as $key => $cfg ) {
        $label = ( 'bedroom' === $key ) ? 'اتاق خواب رویایی' : 'مبلمان مدرن';

        // انتخاب دسته‌بندی ووکامرس (dropdown).
        $wp_customize->add_setting( 'senoobar_showroom_' . $key . '_cat', [ 'default' => 0 ] );
        $wp_customize->add_control( 'senoobar_showroom_' . $key . '_cat', [
            'label'       => 'دسته‌بندی: ' . $label,
            'description' => 'دسته‌ای را انتخاب کنید که محصولاتش در این نمایشگاه چیده شود. اگر خالی بماند، نمایشگاه پیش‌فرض با تصاویر قالب نمایش داده می‌شود.',
            'section'     => 'senoobar_showroom',
            'type'        => 'select',
            'choices'     => senoobar_showroom_cat_choices(),
            'priority'    => 10,
        ] );

        // عنوان اصلی (بالای صفحه).
        $wp_customize->add_setting( 'senoobar_showroom_' . $key . '_title', [ 'default' => $cfg['title'] ] );
        $wp_customize->add_control( 'senoobar_showroom_' . $key . '_title', [
            'label'   => 'عنوان: ' . $label,
            'section' => 'senoobar_showroom',
            'type'    => 'text',
        ] );

        // زیرعنوان.
        $default_sub = ( 'bedroom' === $key )
            ? 'آرامش و خوابی باکیفیت را با سرویس خواب‌های خاص صنوبر تجربه کنید.'
            : 'مبلمانی که خانه شما را مدرن‌تر و گرم‌تر می‌کند.';
        $wp_customize->add_setting( 'senoobar_showroom_' . $key . '_subtitle', [ 'default' => $default_sub ] );
        $wp_customize->add_control( 'senoobar_showroom_' . $key . '_subtitle', [
            'label'   => 'زیرعنوان: ' . $label,
            'section' => 'senoobar_showroom',
            'type'    => 'textarea',
        ] );

        // متن کیکر (بالای عنوان).
        $default_kicker = ( 'bedroom' === $key ) ? 'اتاق خواب رویایی شما' : 'مبلمان مدرن و راحت';
        $wp_customize->add_setting( 'senoobar_showroom_' . $key . '_kicker', [ 'default' => $default_kicker ] );
        $wp_customize->add_control( 'senoobar_showroom_' . $key . '_kicker', [
            'label'   => 'متن کوتاه روی تصویر: ' . $label,
            'section' => 'senoobar_showroom',
            'type'    => 'text',
        ] );
    }
}
add_action( 'customize_register', 'senoobar_showroom_customizer' );

/**
 * ساخت آرایه‌ی انتخاب‌ها برای dropdown دسته‌بندی.
 * همیشه یک گزینه‌ی «— بدون دسته (نمایشگاه پیش‌فرض) —» با مقدار 0 دارد.
 */
function senoobar_showroom_cat_choices(): array {
    $choices = [ '0' => '— بدون دسته (نمایشگاه پیش‌فرض) —' ];

    if ( ! class_exists( 'WooCommerce' ) ) {
        return $choices;
    }

    $terms = get_terms( [
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'number'     => 100,
    ] );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return $choices;
    }

    foreach ( $terms as $t ) {
        $choices[ (string) $t->term_id ] = $t->name . ' (' . $t->count . ')';
    }
    return $choices;
}

/* ════════════════════════════════════════════════════════════════
   ۵. بارگذاری استایل نمایشگاه + JS (فقط در این دو صفحه)
   ════════════════════════════════════════════════════════════════ */
function senoobar_showroom_assets(): void {
    if ( ! is_page_template( [ 'template-bedroom.php', 'template-furniture.php' ] ) ) {
        return;
    }

    wp_enqueue_style(
        'senoobar-showroom',
        SENOOBAR_URI . '/assets/css/showroom.css',
        [ 'senoobar-main' ],
        SENOOBAR_VERSION
    );

    // تعامل سبک نمایشگاه (پیمایش گالری، لایت‌باکس و …).
    wp_enqueue_script(
        'senoobar-showroom',
        SENOOBAR_URI . '/assets/js/showroom.js',
        [ 'senoobar-app' ],
        SENOOBAR_VERSION,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'senoobar_showroom_assets', 30 );

/* ════════════════════════════════════════════════════════════════
   ۶. داده‌ی آماده برای قالب‌ها
   ════════════════════════════════════════════════════════════════ */
function senoobar_showroom_data( string $key ): array {
    $cfg = senoobar_showroom_configs()['bedroom'];
    if ( 'furniture' === $key ) {
        $cfg = senoobar_showroom_configs()['furniture'];
    }

    $cat_id = senoobar_showroom_category( $key );

    return [
        'key'      => $key,
        'title'    => get_theme_mod( 'senoobar_showroom_' . $key . '_title', $cfg['title'] ),
        'subtitle' => get_theme_mod( 'senoobar_showroom_' . $key . '_subtitle', '' ),
        'kicker'   => get_theme_mod( 'senoobar_showroom_' . $key . '_kicker', $cfg['title'] ),
        'cat_id'   => $cat_id,
        // لینک به آرشیو همان دسته برای دیدن «همه محصولات».
        'cat_link' => $cat_id ? get_term_link( $cat_id, 'product_cat' ) : ( class_exists( 'WooCommerce' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : home_url( '/' ) ),
        'slides'   => senoobar_showroom_slides( $key ),
    ];
}
