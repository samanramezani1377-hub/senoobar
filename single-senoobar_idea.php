<?php
/**
 * صفحه‌ی تکی ایده — تمام‌صفحه، شبیه ریلز/پست اینستاگرام.
 *
 * - بدون هدر و فوتر تم (فقط <head> و <body> لازم).
 *   سرویس ورکر/فونت/کریتیکال CSS همچنان از طریق wp_head بارگذاری می‌شوند.
 * - هر ایده در یک «اسلاید» تمام‌صفحه (100vh) نمایش داده می‌شود.
 * - دکمه‌های قبل/بعد + صفحه‌کلید + اسکرول (چرخ) + سوایپ برای جابه‌جایی.
 * - در دسکتاپ: ستون مرکزی باریک شبیه پست اینستاگرام با پس‌زمینه تیره.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

while ( have_posts() ) :
	the_post();
	$idea  = senoobar_idea_item( get_post() );
	$nav   = senoobar_idea_nav();
	$video = $idea['video'];
	$cover = $idea['cover'];
endwhile;

$gallery_url = senoobar_ideas_page_url() ?: home_url( '/' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'senoobar-idea-single' ); ?>>
<?php wp_body_open(); ?>

<div id="idea-feed"
	class="idea-feed"
	data-next="<?php echo esc_url( $nav['next'] ? $nav['next']['link'] : '' ); ?>"
	data-prev="<?php echo esc_url( $nav['prev'] ? $nav['prev']['link'] : '' ); ?>"
	data-gallery="<?php echo esc_url( $gallery_url ); ?>">

	<!-- نوار بالا: بستن + عنوان -->
	<header class="idea-feed__topbar">
		<a class="idea-feed__close" href="<?php echo esc_url( $gallery_url ); ?>" title="بستن" aria-label="بستن">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18 18 6M6 6l12 12"/></svg>
		</a>
		<div class="idea-feed__topbar-title">
			<span class="idea-feed__avatar">
				<?php
				$logo_id = get_theme_mod( 'custom_logo' );
				$logo_html = $logo_id ? wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'idea-feed__avatar-img' ] ) : '';
				if ( $logo_html ) {
					echo $logo_html;
				} else {
					// اگر لوگو نبود، حرف اول عنوان سایت
					echo '<span class="idea-feed__avatar-fallback">' . esc_html( mb_substr( get_bloginfo( 'name' ), 0, 1 ) ) . '</span>';
				}
				?>
			</span>
			<span>ایده‌های صنوبر</span>
		</div>
		<span class="idea-feed__count">
			<?php echo esc_html( $nav['prev'] ? '⋯' : '۱' ); ?>
		</span>
	</header>

	<!-- اسلاید -->
	<div class="idea-feed__slide" id="idea-feed-slide">
		<div class="idea-feed__media">
			<?php if ( $video ) : ?>
				<video class="idea-feed__video" controls autoplay muted loop playsinline
					poster="<?php echo esc_url( $cover ); ?>">
					<source src="<?php echo esc_url( $video ); ?>">
				</video>
			<?php elseif ( $cover ) : ?>
				<img class="idea-feed__img" src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $idea['title'] ); ?>">
			<?php else : ?>
				<div class="idea-feed__ph">بدون تصویر</div>
			<?php endif; ?>

			<!-- دکمه‌های قبل/بعد (ثابت وسط چپ/راست) -->
			<?php if ( $nav['prev'] ) : ?>
			<a class="idea-feed__nav idea-feed__nav--prev" href="<?php echo esc_url( $nav['prev']['link'] ); ?>" title="ایده قبلی" aria-label="ایده قبلی">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
			</a>
			<?php endif; ?>
			<?php if ( $nav['next'] ) : ?>
			<a class="idea-feed__nav idea-feed__nav--next" href="<?php echo esc_url( $nav['next']['link'] ); ?>" title="ایده بعدی" aria-label="ایده بعدی">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
			</a>
			<?php endif; ?>
		</div>

		<!-- کپشن روی/زیر رسانه -->
		<div class="idea-feed__caption">
			<h1 class="idea-feed__title"><?php echo esc_html( $idea['title'] ); ?></h1>
			<?php if ( ! empty( trim( $idea['content'] ) ) ) : ?>
				<div class="idea-feed__content"><?php echo wp_kses_post( wpautop( $idea['content'] ) ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
(function () {
	var feed = document.getElementById('idea-feed');
	if (!feed) return;
	var next = feed.getAttribute('data-next');
	var prev = feed.getAttribute('data-prev');

	function go(url) {
		if (url) window.location.href = url;
	}

	// صفحه‌کلید: راست = بعدی، چپ = قبلی
	document.addEventListener('keydown', function (e) {
		var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
		if (tag === 'input' || tag === 'textarea') return;
		if (e.key === 'ArrowRight') { e.preventDefault(); go(next); }
		if (e.key === 'ArrowLeft')  { e.preventDefault(); go(prev); }
		if (e.key === 'Escape')     { window.location.href = feed.getAttribute('data-gallery'); }
	});

	// اسکرول چرخ (دسکتاپ): پایین = بعدی، بالا = قبلی (با throttle)
	var wheelLock = false;
	document.addEventListener('wheel', function (e) {
		if (wheelLock) return;
		if (Math.abs(e.deltaY) < 25) return;
		wheelLock = true;
		setTimeout(function () { wheelLock = false; }, 700);
		if (e.deltaY > 0) go(next);
		else go(prev);
	}, { passive: true });

	// سوایپ لمسی (موبایل): کشیدن بالا = بعدی، پایین = قبلی
	// ردیابی کامل start/move/end تا جهت دقیق مشخص و از تداخل اسکرول جلوگیری شود.
	var sy = null, sx = null, swiping = false;

	document.addEventListener('touchstart', function (e) {
		var t = e.touches[0];
		sy = t.clientY;
		sx = t.clientX;
		swiping = false;
	}, { passive: true });

	document.addEventListener('touchmove', function (e) {
		if (sy === null) return;
		var t = e.touches[0];
		var dy = t.clientY - sy;
		var dx = t.clientX - sx;
		// فقط وقتی عمودی‌تر از افقی باشد، سوایپ فعال شود
		if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 24) {
			swiping = true;
		}
	}, { passive: true });

	document.addEventListener('touchend', function (e) {
		if (sy === null) return;
		var dy = e.changedTouches[0].clientY - sy;
		sy = null;
		if (!swiping) return;
		swiping = false;
		if (dy < -50) go(next);   // کشیدن بالا
		else if (dy > 50) go(prev); // کشیدن پایین
	}, { passive: true });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
