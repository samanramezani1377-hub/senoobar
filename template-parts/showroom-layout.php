<?php
/**
 * چیدمان مشترک صفحه‌ی نمایشگاه (اتاق خواب / مبلمان).
 *
 * از طریق get_template_part(..., $data) فراخوانی می‌شود؛ $args شامل:
 *   title, subtitle, kicker, cat_id, cat_link, slides
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$d = is_array( $args ) ? $args : [];

$title    = isset( $d['title'] ) ? $d['title'] : get_the_title();
$subtitle = isset( $d['subtitle'] ) ? $d['subtitle'] : '';
$kicker   = isset( $d['kicker'] ) ? $d['kicker'] : '';
$cat_link = isset( $d['cat_link'] ) ? $d['cat_link'] : '';
$slides   = isset( $d['slides'] ) ? $d['slides'] : [];

// تصویر هیرو: اولین تصویر گالری؛ وگرنه تصویر پیش‌فرض.
$hero_img = '';
if ( ! empty( $slides[0]['image'] ) ) {
    $hero_img = $slides[0]['image'];
}
if ( '' === $hero_img ) {
    $hero_img = SENOOBAR_URI . '/assets/images/' . ( ( $d['key'] ?? '' ) === 'furniture' ? 'hero-1.jpg' : 'hero-2.jpg' );
}

// تعداد اسلایدها برای نوار آماری.
$count = count( $slides );

// اسلایدهای ویژه (۳ مورد اول برای چیدمان ویترین).
$featured = array_slice( $slides, 0, 3 );
?>

<main id="primary" class="site-main showroom-page">

    <!-- ═══ HERO سینمایی ═══ -->
    <section class="showroom-hero">
        <div class="showroom-hero__bg">
            <?php echo senoobar_img( $hero_img, [ 'alt' => $title, 'fetchpriority' => 'high', 'decoding' => 'async' ] ); ?>
        </div>
        <div class="showroom-hero__overlay"></div>

        <div class="container showroom-hero__content">
            <?php if ( $kicker ) : ?>
                <span class="showroom-hero__kicker"><?php echo esc_html( $kicker ); ?></span>
            <?php endif; ?>
            <h1 class="showroom-hero__title"><?php echo esc_html( $title ); ?></h1>
            <?php if ( $subtitle ) : ?>
                <p class="showroom-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p>
            <?php endif; ?>
            <div class="showroom-hero__cta">
                <a href="#showroom-products" class="btn btn--white">مشاهده مجموعه</a>
                <?php if ( $cat_link ) : ?>
                    <a href="<?php echo esc_url( $cat_link ); ?>" class="btn hero__btn-outline">همه محصولات</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="showroom-hero__scroll" aria-hidden="true">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </div>
    </section>

    <!-- ═══ نوار آماری ═══ -->
    <section class="showroom-facts">
        <div class="container">
            <div class="showroom-facts__grid">
                <div class="showroom-fact"><span class="showroom-fact__num"><?php echo esc_html( $count > 0 ? $count . '+' : '۵۰' ); ?></span><span class="showroom-fact__label">طرح متنوع</span></div>
                <div class="showroom-fact"><span class="showroom-fact__num">۱۰۰٪</span><span class="showroom-fact__label">ضمانت اصالت کالا</span></div>
                <div class="showroom-fact"><span class="showroom-fact__num">۷ روز</span><span class="showroom-fact__label">ضمانت بازگشت</span></div>
                <div class="showroom-fact"><span class="showroom-fact__num">سراسر کشور</span><span class="showroom-fact__label">ارسال سریع</span></div>
            </div>
        </div>
    </section>

    <div class="showroom-main">

        <?php if ( empty( $slides ) ) : ?>
            <!-- ═══ حالت خالی ═══ -->
            <section class="showroom-section">
                <div class="container">
                    <div class="showroom-empty">
                        <p>در حال حاضر محصولی برای نمایش در این نمایشگاه موجود نیست.</p>
                        <?php if ( $cat_link ) : ?>
                            <p><a href="<?php echo esc_url( $cat_link ); ?>">مشاهده فروشگاه</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

        <?php else : ?>

            <!-- ═══ ویترین (featured) ═══ -->
            <?php if ( count( $featured ) > 1 ) : ?>
            <section class="showroom-section">
                <div class="container">
                    <div class="showroom-section__head showroom-animate">
                        <div>
                            <h2 class="showroom-section__title">منتخب‌های <?php echo esc_html( $title ); ?></h2>
                            <p class="showroom-section__desc">چند مورد از خاص‌ترین طرح‌ها که برای شروع تماشا انتخاب کرده‌ایم.</p>
                        </div>
                    </div>
                    <div class="showroom-featured showroom-animate">
                        <?php foreach ( $featured as $i => $slide ) : ?>
                            <div class="showroom-featured__item"
                                 data-showroom-lightbox
                                 data-image="<?php echo esc_url( $slide['image'] ); ?>"
                                 data-title="<?php echo esc_attr( $slide['title'] ); ?>"
                                 data-caption="<?php echo esc_attr( $slide['caption'] ?? '' ); ?>"
                                 data-price="<?php echo esc_attr( wp_strip_all_tags( $slide['price'] ?? '' ) ); ?>"
                                 data-link="<?php echo esc_url( $slide['link'] ?? '' ); ?>">
                                <?php echo senoobar_img( $slide['image'], [ 'alt' => $slide['title'], 'loading' => ( 0 === $i ? 'eager' : 'lazy' ) ] ); ?>
                                <div class="showroom-featured__overlay"></div>
                                <div class="showroom-featured__label"><?php echo esc_html( $slide['title'] ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- ═══ گالری پیمایشی (راهروی نمایشگاه) ═══ -->
            <section class="showroom-section showroom-section--dark">
                <div class="container">
                    <div class="showroom-section__head showroom-animate">
                        <div>
                            <h2 class="showroom-section__title">گالری نمایشگاه</h2>
                            <p class="showroom-section__desc">برای دیدن جزئیات، هر قاب را لمس کنید.</p>
                        </div>
                    </div>
                    <div class="showroom-rail-wrap showroom-animate">
                        <button type="button" class="showroom-arrow showroom-arrow--prev" aria-label="قبلی">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                        <div class="showroom-rail">
                            <?php foreach ( $slides as $slide ) : ?>
                                <div class="showroom-panel"
                                     data-showroom-lightbox
                                     data-image="<?php echo esc_url( $slide['image'] ); ?>"
                                     data-title="<?php echo esc_attr( $slide['title'] ); ?>"
                                     data-caption="<?php echo esc_attr( $slide['caption'] ?? '' ); ?>"
                                     data-price="<?php echo esc_attr( wp_strip_all_tags( $slide['price'] ?? '' ) ); ?>"
                                     data-link="<?php echo esc_url( $slide['link'] ?? '' ); ?>">
                                    <?php echo senoobar_img( $slide['image'], [ 'alt' => $slide['title'], 'loading' => 'lazy' ] ); ?>
                                    <div class="showroom-panel__overlay"></div>
                                    <span class="showroom-panel__zoom" aria-hidden="true">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35M11 8v6M8 11h6"/></svg>
                                    </span>
                                    <div class="showroom-panel__content">
                                        <h3 class="showroom-panel__title"><?php echo esc_html( $slide['title'] ); ?></h3>
                                        <?php if ( ! empty( $slide['caption'] ) ) : ?>
                                            <p class="showroom-panel__caption"><?php echo esc_html( $slide['caption'] ); ?></p>
                                        <?php endif; ?>
                                        <?php if ( ! empty( $slide['price'] ) ) : ?>
                                            <div class="showroom-panel__price"><?php echo wp_kses_post( $slide['price'] ); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="showroom-arrow showroom-arrow--next" aria-label="بعدی">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>
                    </div>
                </div>
            </section>

            <!-- ═══ شبکه کامل محصولات ═══ -->
            <section class="showroom-section" id="showroom-products">
                <div class="container">
                    <div class="showroom-section__head showroom-animate">
                        <div>
                            <h2 class="showroom-section__title">همه محصولات</h2>
                            <p class="showroom-section__desc">مجموعه کامل <?php echo esc_html( $title ); ?> را اینجا ببینید.</p>
                        </div>
                        <?php if ( $cat_link ) : ?>
                            <a href="<?php echo esc_url( $cat_link ); ?>" class="showroom-grid__link">مشاهده همه ←</a>
                        <?php endif; ?>
                    </div>
                    <div class="showroom-grid showroom-animate">
                        <?php foreach ( $slides as $slide ) : ?>
                            <article class="showroom-grid__item">
                                <div class="showroom-grid__media"
                                     data-showroom-lightbox
                                     data-image="<?php echo esc_url( $slide['image'] ); ?>"
                                     data-title="<?php echo esc_attr( $slide['title'] ); ?>"
                                     data-caption="<?php echo esc_attr( $slide['caption'] ?? '' ); ?>"
                                     data-price="<?php echo esc_attr( wp_strip_all_tags( $slide['price'] ?? '' ) ); ?>"
                                     data-link="<?php echo esc_url( $slide['link'] ?? '' ); ?>">
                                    <?php echo senoobar_img( $slide['image'], [ 'alt' => $slide['title'], 'loading' => 'lazy' ] ); ?>
                                </div>
                                <div class="showroom-grid__body">
                                    <h3 class="showroom-grid__title">
                                        <?php if ( ! empty( $slide['link'] ) ) : ?>
                                            <a href="<?php echo esc_url( $slide['link'] ); ?>"><?php echo esc_html( $slide['title'] ); ?></a>
                                        <?php else : ?>
                                            <?php echo esc_html( $slide['title'] ); ?>
                                        <?php endif; ?>
                                    </h3>
                                    <?php if ( ! empty( $slide['price'] ) ) : ?>
                                        <div class="showroom-grid__price"><?php echo wp_kses_post( $slide['price'] ); ?></div>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

        <?php endif; ?>

        <!-- ═══ CTA پایانی ═══ -->
        <section class="showroom-section">
            <div class="container">
                <div class="showroom-cta showroom-animate">
                    <h2><?php echo esc_html( $title ); ?> را از نزدیک تجربه کنید</h2>
                    <p>برای مشاوره رایگان و انتخاب مناسب‌ترین گزینه، با کارشناسان صنوبر در تماس باشید.</p>
                    <a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn--white">تماس با ما</a>
                </div>
            </div>
        </section>

    </div><!-- /.showroom-main -->

    <!-- ═══ لایت‌باکس ═══ -->
    <div class="showroom-lightbox" id="showroomLightbox" role="dialog" aria-modal="true" aria-label="نمایش تصویر">
        <button type="button" class="showroom-lightbox__close" aria-label="بستن">✕</button>
        <div class="showroom-lightbox__inner">
            <div class="showroom-lightbox__media"></div>
            <div class="showroom-lightbox__title"></div>
            <div class="showroom-lightbox__caption"></div>
            <div class="showroom-lightbox__price"></div>
            <div class="showroom-lightbox__actions"></div>
        </div>
    </div>

</main>
