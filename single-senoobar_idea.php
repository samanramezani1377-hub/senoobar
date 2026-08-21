<?php
/**
 * صفحه‌ی تکی ایده — تمام‌صفحه، اسکرولی پیوسته با لوپ (شبیه ریلز اینستاگرام).
 *
 * - بدون هدر و فوتر تم.
 * - همه‌ی ایده‌ها لود شده و با اسکرول/سوایپ/صفحه‌کلید جابه‌جا می‌شوند.
 * - در انتها به ابتدا می‌پیچد (لوپ) و بالعکس.
 * - انیمیشن اسلاید عمودی بین ایده‌ها.
 * - نشانگر اسکرول (انیمیشن) که با اولین تعامل محو می‌شود.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_id = get_the_ID();

// فهرست کامل ایده‌ها (همان ترتیب REST)
$all_posts = get_posts( [
	'post_type'       => 'senoobar_idea',
	'post_status'     => 'publish',
	'numberposts'     => -1,
	'orderby'         => 'date',
	'order'           => 'DESC',
	'suppress_filters'=> false,
] );

$ideas = [];
$current_index = 0;
foreach ( $all_posts as $i => $p ) {
	$item = senoobar_idea_item( $p );
	if ( ! $item ) {
		continue;
	}
	if ( (int) $item['id'] === (int) $current_id ) {
		$current_index = count( $ideas );
	}
	$item['content'] = wpautop( $item['content'] );
	$ideas[] = $item;
}

$gallery_url = senoobar_ideas_page_url() ?: home_url( '/' );

// لوگوی سایت برای آواتار
$logo_id      = get_theme_mod( 'custom_logo' );
$avatar_html  = '';
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
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'senoobar-idea-single' ); ?>>
<?php wp_body_open(); ?>

<div id="idea-feed" class="idea-feed"
	data-gallery="<?php echo esc_url( $gallery_url ); ?>"
	data-index="<?php echo (int) $current_index; ?>">

	<header class="idea-feed__topbar">
		<a class="idea-feed__close" href="<?php echo esc_url( $gallery_url ); ?>" title="بستن" aria-label="بستن">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 18 18 6M6 6l12 12"/></svg>
		</a>
		<div class="idea-feed__topbar-title">
			<span class="idea-feed__avatar"><?php echo $avatar_html; // phpcs:ignore ?></span>
			<span class="idea-feed__topbar-name">ایده‌های صنوبر</span>
		</div>
		<span class="idea-feed__count" id="idea-feed-count"></span>
	</header>

	<!-- ظرف اسلایدها (همه‌ی ایده‌ها) -->
	<div class="idea-feed__track" id="idea-track"></div>

	<!-- نشانگر اسکرول -->
	<div class="idea-feed__hint" id="idea-hint" aria-hidden="true">
		<span class="idea-feed__hint-label">برای مشاهده بکشید</span>
		<svg class="idea-feed__hint-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
	</div>
</div>

<script type="application/json" id="idea-data"><?php echo wp_json_encode( $ideas ); ?></script>

<script>
(function () {
	var feed  = document.getElementById('idea-feed');
	var track = document.getElementById('idea-track');
	var count = document.getElementById('idea-feed-count');
	var hint  = document.getElementById('idea-hint');
	if (!feed || !track) return;

	var dataEl = document.getElementById('idea-data');
	var items  = [];
	try { items = JSON.parse(dataEl.textContent) || []; } catch (e) { items = []; }
	if (!items.length) { track.innerHTML = '<div class="idea-feed__empty">ایده‌ای موجود نیست</div>'; return; }

	var index = parseInt(feed.getAttribute('data-index'), 10) || 0;
	if (index < 0) index = 0;
	if (index >= items.length) index = items.length - 1;

	var gallery = feed.getAttribute('data-gallery');
	var animating = false;
	var interacted = false;

	/* ── ساخت اسلایدها ── */
	items.forEach(function (item, i) {
		var slide = document.createElement('div');
		slide.className = 'idea-feed__slide';
		slide.dataset.i = i;
		var d0 = i - index;
		slide.style.setProperty('--dy', d0);

		var media = document.createElement('div');
		media.className = 'idea-feed__media';

		if (item.video || item.video_mp4) {
			var vid = document.createElement('video');
			vid.className = 'idea-feed__video';
			// صفت‌های لازم برای autoplay بی‌صدا در iOS/Safari
			vid.setAttribute('autoplay', '');
			vid.setAttribute('loop', '');
			vid.setAttribute('playsinline', '');
			vid.setAttribute('webkit-playsinline', '');
			vid.setAttribute('muted', '');
			vid.setAttribute('preload', 'metadata');
			vid.defaultMuted = true;
			vid.muted = true;
			if (item.cover) vid.setAttribute('poster', item.cover);
			// نسخه‌ی WebM (سبک) — برای مرورگرهای مدرن (اندروید/دسکتاپ)
			if (item.video) {
				var srcWebm = document.createElement('source');
				srcWebm.src = item.video;
				srcWebm.type = 'video/webm';
				vid.appendChild(srcWebm);
			}
			// نسخه‌ی MP4/H.264 — fallback برای iOS/Safari
			if (item.video_mp4) {
				var srcMp4 = document.createElement('source');
				srcMp4.src = item.video_mp4;
				srcMp4.type = 'video/mp4';
				vid.appendChild(srcMp4);
			}
			media.appendChild(vid);
			slide._video = vid; // برای مدیریت پخش در پایین

			var snd = document.createElement('button');
			snd.type = 'button';
			snd.className = 'idea-feed__sound is-muted';
			snd.setAttribute('aria-label', 'صدا دار کردن');
			snd.innerHTML =
				'<span class="idea-sound-label idea-sound-label-on">بی\u200cصدا کردن</span>' +
				'<span class="idea-sound-label idea-sound-label-off">صدا دار کردن</span>' +
				'<svg class="idea-sound-on" viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3z"/><path d="M16.5 12a4.5 4.5 0 0 0-2.5-4v8a4.5 4.5 0 0 0 2.5-4z"/></svg>' +
				'<svg class="idea-sound-off" viewBox="0 0 24 24" fill="currentColor"><path d="M3 9v6h4l5 5V4L7 9H3z"/><path d="M16.4 9.2l4.6 4.6M21 9.2l-4.6 4.6" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>';
			snd.addEventListener('click', function () {
				if (vid.muted) {
					vid.muted = false; vid.play().catch(function(){});
				} else {
					vid.muted = true;
				}
				snd.classList.toggle('is-muted', vid.muted);
				snd.setAttribute('aria-label', vid.muted ? 'صدا دار کردن' : 'بی\u200cصدا کردن');
			});
			media.appendChild(snd);
		} else if (item.cover) {
			var img = document.createElement('img');
			img.className = 'idea-feed__img';
			img.src = item.cover;
			img.alt = item.title || '';
			media.appendChild(img);
		} else {
			var ph = document.createElement('div');
			ph.className = 'idea-feed__ph';
			ph.textContent = 'بدون تصویر';
			media.appendChild(ph);
		}

		var cap = document.createElement('div');
		cap.className = 'idea-feed__caption';
		var h1 = document.createElement('h1');
		h1.className = 'idea-feed__title';
		h1.textContent = item.title || '';
		cap.appendChild(h1);
		if (item.content && item.content.trim()) {
			var c = document.createElement('div');
			c.className = 'idea-feed__content';
			c.innerHTML = item.content;
			cap.appendChild(c);
		}

		slide.appendChild(media);
		slide.appendChild(cap);
		track.appendChild(slide);
	});

	var slides = track.querySelectorAll('.idea-feed__slide');

	function updateCount() {
		if (!count) return;
		count.textContent = (index + 1) + ' / ' + items.length;
	}

	function showHint() {
		if (!hint || interacted) return;
		hint.classList.add('is-visible');
	}
	function hideHint() {
		interacted = true;
		if (hint) hint.classList.remove('is-visible');
	}

	/* ── به‌روزرسانی وضعیت پس از جابه‌جایی (لوپ) ── */
	function applyTransform() {
		slides.forEach(function (s, i) {
			var d = i - index;
			// لوپ: نزدیک‌ترین فاصله دایره‌ای
			var half = Math.floor(items.length / 2);
			if (d > half) d = d - items.length;
			if (d < -half) d = d + items.length;
			s.style.setProperty('--dy', d);
			s.classList.toggle('is-active', d === 0);
			s.classList.toggle('is-above', d < 0);
			s.classList.toggle('is-below', d > 0);
		});
		updateCount();
	}

	/* ── ناوبری ── */
	function go(dir) { // dir: 1 = بعدی (پایین)، -1 = قبلی (بالا)
		if (animating) return;
		animating = true;
		hideHint();

		// جهت حرکت: بعدی → کل ترک به بالا می‌رود
		index = (index + dir + items.length) % items.length; // لوپ سراسری

		applyTransform();

		setTimeout(function () {
			animating = false;
			// پخش/توقف ویدیوها
			slides.forEach(function (s, i) {
				var v = s.querySelector('video');
				if (!v) return;
				if (i === index) { v.play().catch(function(){}); }
				else { v.pause(); }
			});
		}, 520);
	}

	// ویدیوی فعلی را همان ابتدا پخش کن و بقیه را متوقف
	function pauseAllExcept() {
		slides.forEach(function (s, i) {
			var v = s.querySelector('video');
			if (!v) return;
			if (i === index) v.play().catch(function(){});
			else v.pause();
		});
	}
	pauseAllExcept();

	/* ── ورودی‌ها ── */
	// صفحه‌کلید
	document.addEventListener('keydown', function (e) {
		var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
		if (tag === 'input' || tag === 'textarea') return;
		if (e.key === 'ArrowDown')  { e.preventDefault(); go(1); }
		if (e.key === 'ArrowUp')    { e.preventDefault(); go(-1); }
		if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
		if (e.key === 'ArrowLeft')  { e.preventDefault(); go(-1); }
		if (e.key === 'Escape')     { window.location.href = gallery; }
	});

	// اسکرول چرخ
	var wheelLock = false;
	document.addEventListener('wheel', function (e) {
		if (Math.abs(e.deltaY) < 25) return;
		if (wheelLock) return;
		wheelLock = true;
		setTimeout(function () { wheelLock = false; }, 650);
		if (e.deltaY > 0) go(1);
		else go(-1);
	}, { passive: true });

	// سوایپ (pointer + touch)
	var sy = null, sx = null, isSwipe = false;
	function onStart(x, y) { sx = x; sy = y; isSwipe = false; }
	function onMove(x, y) {
		if (sy === null) return;
		var dy = y - sy, dx = x - sx;
		if (Math.abs(dy) > Math.abs(dx) && Math.abs(dy) > 20) isSwipe = true;
	}
	function onEnd(y) {
		if (sy === null) return;
		var dy = y - sy; sy = null;
		if (!isSwipe) return;
		isSwipe = false;
		if (dy < -40) go(1);       // کشیدن بالا → بعدی
		else if (dy > 40) go(-1);  // کشیدن پایین → قبلی
	}

	if (window.PointerEvent) {
		feed.addEventListener('pointerdown', function (e) {
			if (e.pointerType === 'mouse') return;
			onStart(e.clientX, e.clientY);
		});
		feed.addEventListener('pointermove', function (e) {
			if (sy === null) return;
			onMove(e.clientX, e.clientY);
		});
		feed.addEventListener('pointerup', function (e) { onEnd(e.clientY); });
		feed.addEventListener('pointercancel', function () { sy = null; });
	} else {
		document.addEventListener('touchstart', function (e) {
			var t = e.touches[0]; onStart(t.clientX, t.clientY);
		}, { passive: true });
		document.addEventListener('touchmove', function (e) {
			var t = e.touches[0]; onMove(t.clientX, t.clientY);
		}, { passive: true });
		document.addEventListener('touchend', function (e) {
			onEnd(e.changedTouches[0].clientY);
		}, { passive: true });
	}

	// نشانگر اسکرول: بعد از ۱.۲ ثانیه نمایش، با اولین تعامل محو
	setTimeout(showHint, 1200);

	updateCount();
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
