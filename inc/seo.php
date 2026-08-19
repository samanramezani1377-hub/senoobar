<?php
/**
 * Senoobar — Dynamic SEO Engine (title + meta description + OG tags)
 *
 * به‌صورت خودکار و داینامیک، تایتل و متا دیسکریپشنِ همه‌ی صفحه‌ها را با طول
 * استاندارد تولید می‌کند. شامل صفحه‌های آینده (محصول جدید، دسته، برگه، نوشته و …)
 * هم می‌شود، چون بر اساس «نوع صفحه» ساخته می‌شود نه به‌صورت دستی.
 *
 * استانداردهای طول (برای نمایش کامل در نتایج گوگل):
 *   - Title: حداکثر ۶۰ کاراکتر
 *   - Meta description: بین ۷۰ تا ۱۶۰ کاراکتر (بهینه ۱۵۰~۱۶۰)
 *
 * اولویت خروجی:
 *   ۱) مقدار دستی (فیلد سفارشی) اگر پر شده باشد
 *   ۲) ساخت هوشمند از محتوای صفحه
 *   ۳) پیش‌فرض‌های سایت
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* -------------------------------------------------------------------------
 * ابزارهای کمکی
 * ---------------------------------------------------------------------- */

function senoobar_brand() {
    static $brand = null;
    if ( null === $brand ) {
        $brand = trim( get_bloginfo( 'name' ) );
        if ( empty( $brand ) || $brand === 'senoobar' ) {
            $brand = 'صنوبر';
        }
    }
    return $brand;
}

function senoobar_clip( $text, $max ) {
    $text = trim( $text );
    if ( mb_strlen( $text ) <= $max ) {
        return $text;
    }
    return mb_substr( $text, 0, $max - 1 ) . '…';
}

/**
 * حذف پیشوندهای مزاحم وردپرس مثل «بایگانی‌ها:» یا «Archives:».
 */
function senoobar_clean_archive_title( $title ) {
    $title = wp_strip_all_tags( $title );
    $prefixes = array(
        'بایگانی‌ها:', 'بایگانی‌های', 'بایگانی:', 'آرشیو:', 'دسته:', 'برچسب:',
        'Archives:', 'Archive:', 'Category:', 'Tag:', 'Author:',
        'فروشگاه:', 'محصولات:',
    );
    foreach ( $prefixes as $p ) {
        if ( 0 === mb_strpos( $title, $p ) ) {
            $title = trim( mb_substr( $title, mb_strlen( $p ) ) );
            break;
        }
    }
    return trim( $title );
}

function senoobar_build_description( $text ) {
    $text = wp_strip_all_tags( $text );
    $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
    $text = preg_replace( '/\s+/u', ' ', $text );
    $text = trim( $text, " \t\n\r\0\x0B|—-" );
    $text = trim( $text );

    if ( mb_strlen( $text ) > 160 ) {
        $text = mb_substr( $text, 0, 157 ) . '…';
    }

    if ( mb_strlen( $text ) < 70 ) {
        $text .= ' | ' . senoobar_brand() . ' — فروشگاه تخصصی تشک، سرویس خواب و مبلمان.';
        if ( mb_strlen( $text ) > 160 ) {
            $text = mb_substr( $text, 0, 157 ) . '…';
        }
    }

    return trim( $text, " \t\n\r\0\x0B|—-" );
}

/* -------------------------------------------------------------------------
 * متا دیسکریپشن
 * ---------------------------------------------------------------------- */

function senoobar_get_meta_description() {
    $desc = '';

    // 1) مقدار دستی (بالاترین اولویت).
    if ( is_singular() ) {
        $manual = get_post_meta( get_queried_object_id(), '_senoobar_meta_description', true );
        if ( ! empty( $manual ) ) {
            return senoobar_build_description( $manual );
        }
    }

    // 2) صفحه اصلی.
    if ( is_front_page() ) {
        $desc = get_bloginfo( 'description' );
        if ( empty( $desc ) || $desc === 'senoobar' ) {
            $desc = 'خرید تشک طبی فنری، سرویس خواب و مبلمان با ضمانت و ارسال سریع | فروشگاه صنوبر';
        }
        return senoobar_build_description( $desc );
    }

    // 3) برگه / نوشته / محصول.
    if ( is_singular() ) {
        $post = get_post( get_queried_object_id() );
        $desc = ! empty( $post->post_excerpt ) ? $post->post_excerpt : $post->post_content;
        if ( ! empty( $desc ) ) {
            return senoobar_build_description( $desc );
        }
    }

    // 4) صفحه فروشگاه ووکامرس (shop).
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        $shop_id = wc_get_page_id( 'shop' );
        $desc    = get_post_meta( $shop_id, '_senoobar_meta_description', true );
        if ( empty( $desc ) ) {
            $desc = 'انواع محصولات ' . senoobar_brand() . ' شامل تشک طبی، سرویس خواب، تخت خواب و مبلمان با کیفیت و قیمت مناسب.';
        }
        return senoobar_build_description( $desc );
    }

    // 5) دسته‌بندی / برچسب / اصطلاح.
    if ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            return senoobar_build_description( $term->description );
        }
        if ( $term ) {
            return senoobar_build_description( 'انواع ' . $term->name . ' با کیفیت و قیمت مناسب در فروشگاه ' . senoobar_brand() . '.' );
        }
    }

    // 6) بایگانی نویسنده.
    if ( is_author() ) {
        return senoobar_build_description( 'نوشته‌های این نویسنده را در فروشگاه ' . senoobar_brand() . ' بخوانید.' );
    }

    // 7) نتایج جستجو.
    if ( is_search() ) {
        return senoobar_build_description( 'نتایج جستجو برای عبارت «' . get_search_query() . '» در فروشگاه ' . senoobar_brand() . '.' );
    }

    // 8) پیش‌فرض.
    return senoobar_build_description( get_bloginfo( 'description' ) );
}

/* -------------------------------------------------------------------------
 * تایتل صفحه
 * ---------------------------------------------------------------------- */

function senoobar_get_document_title() {
    // مقدار دستی (بالاترین اولویت).
    if ( is_singular() ) {
        $manual = get_post_meta( get_queried_object_id(), '_senoobar_meta_title', true );
        if ( ! empty( $manual ) ) {
            return senoobar_clip( $manual, 60 );
        }
    }

    $brand = senoobar_brand();

    if ( is_front_page() ) {
        $tagline = trim( get_bloginfo( 'description' ) );
        if ( empty( $tagline ) || $tagline === 'senoobar' ) {
            return $brand . ' | فروشگاه تشک طبی و فنری، سرویس خواب و مبلمان';
        }
        return senoobar_clip( $brand . ' | ' . $tagline, 60 );
    }

    if ( is_singular() ) {
        return senoobar_clip( get_the_title( get_queried_object_id() ) . ' | ' . $brand, 60 );
    }

    // صفحه فروشگاه ووکامرس.
    if ( function_exists( 'is_shop' ) && is_shop() ) {
        $shop_id = wc_get_page_id( 'shop' );
        $name    = get_the_title( $shop_id );
        if ( empty( $name ) || strtolower( $name ) === 'shop' ) {
            $name = 'فروشگاه';
        }
        return senoobar_clip( $name . ' | ' . $brand, 60 );
    }

    if ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        $name = $term ? $term->name : '';
        if ( empty( $name ) ) {
            $name = senoobar_clean_archive_title( get_the_archive_title() );
        }
        return senoobar_clip( $name . ' | ' . $brand, 60 );
    }

    if ( is_author() ) {
        return senoobar_clip( get_the_author() . ' | ' . $brand, 60 );
    }

    if ( is_search() ) {
        return senoobar_clip( 'جستجوی «' . get_search_query() . '» | ' . $brand, 60 );
    }

    if ( is_archive() ) {
        $name = senoobar_clean_archive_title( get_the_archive_title() );
        if ( empty( $name ) ) {
            $name = 'بایگانی';
        }
        return senoobar_clip( $name . ' | ' . $brand, 60 );
    }

    if ( is_404() ) {
        return 'صفحه پیدا نشد | ' . $brand;
    }

    return senoobar_clip( get_the_title() . ' | ' . $brand, 60 );
}

/* -------------------------------------------------------------------------
 * خروجی در هدر
 * ---------------------------------------------------------------------- */

function senoobar_render_seo_meta() {
    $desc  = senoobar_get_meta_description();
    $title = senoobar_get_document_title();

    if ( ! empty( $desc ) ) {
        echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
    }

    echo '<title>' . esc_html( $title ) . "</title>\n";

    echo '<meta property="og:title" content="' . esc_attr( $title ) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $desc ) . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( senoobar_brand() ) . '" />' . "\n";

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

/* -------------------------------------------------------------------------
 * مدیریت افزونه‌های سئو (Yoast) — تا title/desc خراب یا تکراری نشود.
 * ---------------------------------------------------------------------- */

if ( function_exists( 'YoastSEO' ) || class_exists( 'WPSEO_Frontend' ) || defined( 'WPSEO_VERSION' ) ) {
    // تایتل و توضیحِ ما جایگزین خروجی Yoast می‌شود.
    add_filter( 'wpseo_title', 'senoobar_get_document_title', 999 );
    add_filter( 'wpseo_metadesc', 'senoobar_get_meta_description', 999 );

    // حذف تگ‌های <title> و <meta description> تکراریِ Yoast.
    add_filter( 'wpseo_frontend_presenter_classes', function ( $classes ) {
        return array_filter( $classes, function ( $c ) {
            if ( false !== strpos( $c, 'Title' ) ) {
                return false;
            }
            if ( false !== strpos( $c, 'Meta_Description' ) ) {
                return false;
            }
            return true;
        } );
    } );
}

// جلوگیری از چاپ تایتل پیش‌فرض وردپرس.
add_filter( 'pre_get_document_title', 'senoobar_get_document_title', 998 );
