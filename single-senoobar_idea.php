<?php
/**
 * صفحه‌ی تکی ایده — تمام‌صفحه، شبیه ریلز/پست اینستاگرام.
 *
 * - بدون هدر و فوتر تم (فقط <head> و <body> لازم).
 * - هر ایده در یک «اسلاید» تمام‌صفحه (100vh) نمایش داده می‌شود.
 * - دکمه‌های قبل/بعد + صفحه‌کلید + اسکرول + سوایپ برای جابه‌جایی.
 * - ویدیو: پخش خودکار با صدا (با دکمه mute/unmute).
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

// لوگوی سایت برای آواتار (شبیه عکس پروفایل)
$logo_id = get_theme_mod( 'custom_logo' );
$avatar_html = '';
if ( $logo_id ) {
	$avatar_html = wp_get_attachment_image( $logo_id, 'thumbnail', false, [ 'class' => 'idea-feed__avatar-img' ] );
}
if ( '' === $avatar_html ) {
	$avatar_html = '<span class="idea-feed__avatar-fallback">' . esc_html( mb_substr( get_bloginfo( 'name' ), 0, 1 ) ) . '</span>';
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
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

	<!-- نوار بالا: بستن + آواتار + عنوان -->
	<header class="idea-feed__topbar">
		<a class="idea-feed__close" href="<?php echo esc_url( $gallery_url ); ?>" title="بستن" aria-label="بستن">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18 18 6M6 6l12 12"/></svg>
		</a>
		<div class="idea-feed__topbar-title">
			<span class="idea-feed__avatar"><?php echo $avatar_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="idea-feed__topbar-name">ایده‌های صنوبر</span>
		</div>
		<span class="idea-feed__count">
			<?php echo esc_html( $nav['prev'] ? '⋯' : '۱' ); ?>
		</span>
	</header>

	<!-- اسلاید -->
	<div class="idea-feed__slide" id="idea-feed-slide">
		<div class="idea-feed__media">
			<?php if ( $video ) : ?>
				<video id="idea-video" class="idea-feed__video" autoplay loop playsinline
					poster="<?php echo esc_url( $cover ); ?>">
					<source src="<?php echo esc_url( $video ); ?>">
				</video>
				<button type="button" class="idea-feed__sound" id="idea-sound-btn" aria-label="صدا" title="صدا">
					<svg class="idea-sound-on" viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3z"/><path d="M16.5 12a4.5 4.5 0 0 0-2.5-4v8a4.5 4.5 0 0 0 2.5-4z"/><path d="M14 3.2v2.1a7 7 0 0 1 0 13.4v2.1a9 9 0 0 0 0-17.6z"/></svg>
					<svg class="idea-sound-off" viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3z"/><path d="M16.4 9.2l4.6 4.6M21 9.2l-4.6 4.6"/></svg>
				</button>
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

		<!-- کپشن -->
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
	var gallery = feed.getAttribute('data-gallery');
	var locked = false;

	function go(url) {
		if (url && !locked) {
			locked = true; // جلوگیری از چندبار تریگر قبل از ناوبری
			window.location.href = url;
		}
	}

	/* ── صدا (ویدیو) ── */
	var video = document.getElementById('idea-video');
	var soundBtn = document.getElementById('idea-sound-btn');
	var iconOn = soundBtn ? soundBtn.querySelector('.idea-sound-on') : null;
	var iconOff = soundBtn ? soundBtn.querySelector('.idea-sound-off') : null;

	function setMuted(muted) {
		if (!video) return;
		video.muted = muted;
		if (soundBtn) soundBtn.classList.toggle('is-muted', muted);
	}

	if (soundBtn) {
		soundBtn.addEventListener('click', function (e) {
			e.stopPropagation();
			if (!video) return;
			if (video.muted) {
				// تلاش برای پخش با صدا (نیاز به تعامل کاربر دارد)
				video.muted = false;
				video.play().catch(function () {});
			} else {
				video.muted = true;
			}
			setMuted(video.muted);
		});
	}
	if (video) {
		// شروع بدون صدا (محدودیت autoplay مرورگر). کاربر با لمس دکمه صدا روشن می‌کند.
		setMuted(true);
		video.addEventListener('volumechange', function () {
			setMuted(video.muted);
		});
	}

	/* ── صفحه‌کلید ── */
	document.addEventListener('keydown', function (e) {
		var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
		if (tag === 'input' || tag === 'textarea') return;
		if (e.key === 'ArrowRight') { e.preventDefault(); go(next); }
		if (e.key === 'ArrowLeft')  { e.preventDefault(); go(prev); }
		if (e.key === 'Escape')     { window.location.href = gallery; }
	});

	/* ── اسکرول چرخ (دسکتاپ) ── */
	var wheelLock = false;
	document.addEventListener('wheel', function (e) {
		if (wheelLock) return;
		if (Math.abs(e.deltaY) < 25) return;
		wheelLock = true;
		setTimeout(function () { wheelLock = false; }, 600);
		if (e.deltaY > 0) go(next);
		else go(prev);
	}, { passive: true });

	/* ── سوایپ لمسی (موبایل) ── */
	var sy = null, sx = null, isSwipe = false;

	function onStart(x, y) { sx = x; sy = y; isSwipe = false; }
	function onMove(x, y) {
		if (sy === null) return;
		var dy = y - sy;
		var dx = x - sx;
		if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 20) {
			isSwipe = true;
		}
	}
	function onEnd(y) {
		if (sy === null) return;
		var dy = y - sy;
		sy = null;
		if (!isSwipe) return;
		isSwipe = false;
		if (dy < -40) go(next);      // کشیدن به بالا
		else if (dy > 40) go(prev);  // کشیدن به پایین
	}

	// Pointer Events (موبایل + دسکتاپ لمسی) — دقیق‌تر از touch
	var slide = document.getElementById('idea-feed-slide');
	if (slide && window.PointerEvent) {
		slide.addEventListener('pointerdown', function (e) {
			if (e.pointerType === 'mouse') return; // موس با wheel مدیریت می‌شود
			onStart(e.clientX, e.clientY);
		});
		slide.addEventListener('pointermove', function (e) {
			if (sy === null) return;
			onMove(e.clientX, e.clientY);
		});
		slide.addEventListener('pointerup', function (e) {
			onEnd(e.clientY);
		});
		slide.addEventListener('pointercancel', function () { sy = null; });
	}

	// Fallback: Touch Events
	document.addEventListener('touchstart', function (e) {
		var t = e.touches[0]; onStart(t.clientX, t.clientY);
	}, { passive: true });
	document.addEventListener('touchmove', function (e) {
		var t = e.touches[0]; onMove(t.clientX, t.clientY);
	}, { passive: true });
	document.addEventListener('touchend', function (e) {
		onEnd(e.changedTouches[0].clientY);
	}, { passive: true });
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
