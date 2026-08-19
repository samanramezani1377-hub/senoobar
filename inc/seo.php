<?php
/**
 * Senoobar — Dynamic SEO (title + meta description + OG tags)
 *
 * به‌جای وابستگی به افزونه، متا‌تگ‌ها را برای همه‌ی صفحه‌ها به‌صورت خودکار تولید
 * می‌کند. شامل صفحه‌های آینده (محصول جدید، دسته، برگه، نوشته و …) هم می‌شود،
 * چون بر اساس «نوع صفحه» ساخته می‌شود نه به‌صورت دستی.
 *
 * طرز خروجی:
 *   - اگر افزونه‌ی سئو (Yoast / RankMath) مقدار پر کرده باشد، اولویت با آن است.
 *   - اگر صفحه تنظیم دستی داشته باشد (SEO متاباکس)، همان استفاده می‌شود.
 *   - در غیر این صورت، تیتر و توضیح به‌صورت هوشمند از محتوا ساخته می‌شود.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * یک توضیح متا (meta description) هوشمند بساز.
 */
function senoobar_build_description( $text ) {
    $text = wp_strip_all_tags( $text );
    $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
    $text = preg_replace( '/\s+/u', ' ', $text );
    $text = trim( $text );

    if ( mb_strlen( $text ) > 160 ) {
        $text = mb_substr( $text, 0, 157 ) . '…';
    }

    return $text;
}

/**
 * دریافت متا دیسکریپشن نهاییِ صفحه‌ی فعلی.
 */
function senoobar_get_meta_description() {
    $desc = '';

    // 1) اگر افزونه سئو مقدار داده، همان را برگردان.
    $plugin_desc = apply_filters( 'senoobar_plugin_description', false );
    if ( ! empty( $plugin_desc ) ) {
        return $plugin_desc;
    }

    // 2) صفحه اصلی (فروشگاه / بلاگ).
    if ( is_front_page() ) {
        $desc = get_bloginfo( 'description' );
        if ( empty( $desc ) ) {
            $desc = 'خرید تشک طبی فنری، سرویس خواب و مبلمان با ضمانت و ارسال سریع | فروشگاه سیانوبر';
        }
        return senoobar_build_description( $desc );
    }

    // 3) برگه / نوشته / محصول با مقدار دستی.
    if ( is_singular() ) {
        $id   = get_queried_object_id();
        $desc = get_post_meta( $id, '_senoobar_meta_description', true );

        if ( empty( $desc ) ) {
            $post = get_post( $id );
            $desc = ! empty( $post->post_excerpt )
                ? $post->post_excerpt
                : $post->post_content;
        }

        if ( ! empty( $desc ) ) {
            return senoobar_build_description( $desc );
        }
    }

    // 4) دسته‌بندی / برچسب / اصطلاح (محصول، نوشته و …).
    if ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            return senoobar_build_description( $term->description );
        }
        if ( $term ) {
            return 'آرشیو ' . esc_html( $term->name ) . ' در فروشگاه سیانوبر.';
        }
    }

    // 5) بایگانی نویسنده.
    if ( is_author() ) {
        return 'نوشته‌های این نویسنده را در فروشگاه سیانوبر بخوانید.';
    }

    // 6) نتایج جستجو.
    if ( is_search() ) {
        return 'نتایج جستجو برای عبارت «' . esc_html( get_search_query() ) . '» در فروشگاه سیانوبر.';
    }

    // 7) پیش‌فرض.
    return senoobar_build_description( get_bloginfo( 'description' ) );
}

/**
 * خروجی متاتگ‌ها در هدر.
 */
function senoobar_render_seo_meta() {
    $desc = senoobar_get_meta_description();
    if ( ! empty( $desc ) ) {
        echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
    }

    // Open Graph (برای اشتراک‌گذاری در تلگرام/واتساپ/…).
    if ( is_singular() ) {
        $id    = get_queried_object_id();
        $og_ok = (bool) post_type_supports( get_post_type( $id ), 'thumbnail' ) && has_post_thumbnail( $id );
        if ( $og_ok ) {
            $img = wp_get_attachment_image_url( get_post_thumbnail_id( $id ), 'large' );
            if ( $img ) {
                echo '<meta property="og:image" content="' . esc_url( $img ) . '" />' . "\n";
            }
        }
    }
}
add_action( 'wp_head', 'senoobar_render_seo_meta', 1 );

/**
 * افزودن عنوان سایت (title) به‌صورت داینامیک برای همه‌ی صفحه‌ها.
 * اگر افزونه سئو فعال باشد، به آن اجازه مدیریت تیتر را می‌دهیم.
 */
function senoobar_document_title_parts( $title ) {
    // اگر افزونه سئو خروجی title را مدیریت می‌کند، دست نزن.
    if ( apply_filters( 'senoobar_seo_plugin_active', false ) ) {
        return $title;
    }

    if ( is_front_page() ) {
        $tagline = get_bloginfo( 'description' );
        if ( ! empty( $tagline ) ) {
            $title['tagline'] = $tagline;
        }
    }
    return $title;
}
add_filter( 'document_title_parts', 'senoobar_document_title_parts' );

/**
 * اتصال به افزونه‌های سئو:
 * اگر Yoast یا RankMath نصب باشد، مقدار آنها را اولویت می‌دهیم تا تداخل نشود.
 */
if ( function_exists( 'YoastSEO' ) || class_exists( 'WPSEO_Frontend' ) ) {
    add_filter( 'senoobar_seo_plugin_active', '__return_true' );
    add_filter( 'senoobar_plugin_description', function () {
        if ( class_exists( 'WPSEO_Frontend' ) ) {
            $front = WPSEO_Frontend::get_instance();
            if ( method_exists( $front, 'get_meta_description' ) ) {
                return $front->get_meta_description( get_queried_object_id() );
            }
        }
        return false;
    } );
}
