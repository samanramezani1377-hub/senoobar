<?php
/**
 * Template Name: تماس با ما
 *
 * @package Senoobar
 */

defined( 'ABSPATH' ) || exit;

get_header();

$phone1 = get_theme_mod('senoobar_footer_phone1', '۰۹۱۳۰۲۰۵۸۹۸');
$phone2 = get_theme_mod('senoobar_footer_phone2', '۰۹۱۳۰۲۰۵۸۶۸');
$phone3 = get_theme_mod('senoobar_footer_phone3', '۰۹۱۳۰۲۰۵۳۲۳');

if ( ! function_exists( 'senoobar_tel_href' ) ) {
	function senoobar_tel_href( $num ) {
		$en = str_replace( ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'], ['0','1','2','3','4','5','6','7','8','9'], $num );
		return preg_replace( '/[^0-9+]/', '', $en );
	}
}
$tel1 = senoobar_tel_href( $phone1 );
$tel2 = senoobar_tel_href( $phone2 );
$tel3 = senoobar_tel_href( $phone3 );
$address = get_theme_mod('senoobar_footer_address', 'اصفهان، شهرک صنعتی دولت‌آباد، خیابان شماره ۱۰ (خیام)، فروشگاه صنوبر');
$hours   = get_theme_mod('senoobar_footer_hours', 'شنبه تا پنجشنبه، ۱۰ صبح تا ۹ شب');
?>

<main class="legal-page" dir="rtl">
    <div class="container">

        <nav class="legal-page__crumb" aria-label="مسیر">
            <a href="<?php echo esc_url( home_url('/') ); ?>">خانه</a>
            <span>/</span>
            <span>تماس با ما</span>
        </nav>

        <header class="legal-page__head">
            <h1 class="section__title">تماس با ما</h1>
            <p class="section__desc">همیشه خوشحال می‌شویم صدای شما را بشنویم و راهنمایی‌تان کنیم.</p>
        </header>

        <div class="legal-page__body">
            <div class="legal-page__note">
                برای راهنمایی در خرید، پیگیری سفارش یا مشاوره انتخاب محصول، تیم پشتیبانی صنوبر در کنار شماست.
                از طریق شماره تلفن‌های زیر یا مراجعه حضوری به فروشگاه می‌توانید با ما در ارتباط باشید.
            </div>

            <div class="contact-grid">
                <div class="contact-card">
                    <div class="contact-card__icon">📞</div>
                    <div class="contact-card__title">خط تماس اصلی</div>
                    <div class="contact-card__value"><a href="tel:<?php echo esc_attr( $tel1 ); ?>"><?php echo esc_html( $phone1 ); ?></a></div>
                </div>
                <div class="contact-card">
                    <div class="contact-card__icon">📲</div>
                    <div class="contact-card__title">پشتیبانی فروش</div>
                    <div class="contact-card__value"><a href="tel:<?php echo esc_attr( $tel2 ); ?>"><?php echo esc_html( $phone2 ); ?></a></div>
                </div>
                <div class="contact-card">
                    <div class="contact-card__icon">☎️</div>
                    <div class="contact-card__title">مدیریت فروشگاه</div>
                    <div class="contact-card__value"><a href="tel:<?php echo esc_attr( $tel3 ); ?>"><?php echo esc_html( $phone3 ); ?></a></div>
                </div>
                <div class="contact-card">
                    <div class="contact-card__icon">🕐</div>
                    <div class="contact-card__title">ساعات کاری</div>
                    <div class="contact-card__value"><?php echo esc_html( $hours ); ?></div>
                </div>
            </div>

            <h2>آدرس فروشگاه</h2>
            <div class="contact-card" style="text-align:right;">
                <div class="contact-card__title">📍 فروشگاه صنوبر</div>
                <div class="contact-card__value"><?php echo esc_html( $address ); ?></div>
            </div>

            <div class="legal-page__cta">
                <a href="tel:<?php echo esc_attr( $tel1 ); ?>" class="btn btn--primary">تماس با پشتیبانی</a>
            </div>
        </div>

    </div>
</main>

<?php get_footer();
