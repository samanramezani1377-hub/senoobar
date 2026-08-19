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
 * ساخت یک توضیح متا تمیز (بین ۷۰ تا ۱۶۰ کاراکتر).
 */
function senoobar_build_description( $text ) {
    $text = wp_strip_all_tags( $text );
    $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
    $text = preg_replace( '/\s+/u', ' ', $text );
    $text = trim( $text );

    if ( mb_strlen( $text ) > 160 ) {
        $text = mb_substr( $text, 0, 157 ) . '…';
    }

    // حداقل طول: اگر خیلی کوتاه بود با برند کاملش کن.
    if ( mb_strlen( $text ) < 70 ) {
        $text .= ' | ' . senoobar_brand() . ' — فروشگاه تخصصی تشک، سرویس خواب و مبلمان.';
        if ( mb_strlen( $text ) > 160 ) {
            $text = mb_substr( $text, 0, 157 ) . '…';
        }
    }

    return $text;
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

    // 4) دسته‌بندی / برچسب / اصطلاح.
    if ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        if ( $term && ! empty( $term->description ) ) {
            return senoobar_build_description( $term->description );
        }
        if ( $term ) {
            return senoobar_build_description( 'انواع ' . $term->name . ' با کیفیت و قیمت مناسب در فروشگاه ' . senoobar_brand() . '.' );
        }
    }

    // 5) بایگانی نویسنده.
    if ( is_author() ) {
        return senoobar_build_description( 'نوشته‌های این نویسنده را در فروشگاه ' . senoobar_brand() . ' بخوانید.' );
    }

    // 6) نتایج جستجو.
    if ( is_search() ) {
        return senoobar_build_description( 'نتایج جستجو برای عبارت «' . get_search_query() . '» در فروشگاه ' . senoobar_brand() . '.' );
    }

    // 7) پیش‌فرض.
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
            return $brand . ' | فروشگاه تشک طبی، سرویس خواب و مبلمان';
        }
        return senoobar_clip( $brand . ' | ' . $tagline, 60 );
    }

    if ( is_singular() ) {
        return senoobar_clip( get_the_title( get_queried_object_id() ) . ' | ' . $brand, 60 );
    }

    if ( is_tax() || is_category() || is_tag() ) {
        $term = get_queried_object();
        $name = $term ? $term->name : wp_strip_all_tags( get_the_archive_title() );
        return senoobar_clip( $name . ' | ' . $brand, 60 );
    }

    if ( is_author() ) {
        return senoobar_clip( get_the_author() . ' | ' . $brand, 60 );
    }

    if ( is_search() ) {
        return senoobar_clip( 'جستجوی «' . get_search_query() . '» | ' . $brand, 60 );
    }

    if ( is_archive() ) {
        return senoobar_clip( wp_strip_all_tags( get_the_archive_title() ) . ' | ' . $brand, 60 );
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
 * مدیریت افزونه‌های سئو (Yoast) — تا title/desc خراب نشود.
 * ---------------------------------------------------------------------- */

if ( function_exists( 'YoastSEO' ) || class_exists( 'WPSEO_Frontend' ) || defined( 'WPSEO_VERSION' ) ) {
    // تایتل و توضیحِ ما جایگزین خروجی Yoast می‌شود.
    add_filter( 'wpseo_title', 'senoobar_get_document_title', 999 );
    add_filter( 'wpseo_metadesc', 'senoobar_get_meta_description', 999 );

    // حذف تگ <title> تکراریِ Yoast.
    add_filter( 'wpseo_frontend_presenter_classes', function ( $classes ) {
        return array_filter( $classes, function ( $c ) {
            return false === strpos( $c, 'Title' );
        } );
    } );
}

// جلوگیری از چاپ تایتل پیش‌فرض وردپرس.
add_filter( 'pre_get_document_title', 'senoobar_get_document_title', 998 );
