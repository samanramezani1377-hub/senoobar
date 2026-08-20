<?php
/**
 * صفحه‌ی تکی ایده — شبیه اینستاگرام.
 *
 * - در موبایل: نمای تمام‌صفحه و با اسکرول/سوایپ به ایده‌ی بعدی می‌رود.
 * - عنوان، رسانه (ویدیو یا کاور) و محتوا.
 */
get_header();

while ( have_posts() ) : the_post();
	$idea  = senoobar_idea_item( get_post() );
	$nav   = senoobar_idea_nav();
	$video = $idea['video'];
	$cover = $idea['cover'];
endwhile;
?>
<div id="idea-feed"
	class="idea-feed"
	data-next="<?php echo esc_url( $nav['next'] ? $nav['next']['link'] : '' ); ?>"
	data-prev="<?php echo esc_url( $nav['prev'] ? $nav['prev']['link'] : '' ); ?>"
	data-video="<?php echo $video ? '1' : '0'; ?>"
>

	<div class="idea-feed__stage">
		<div class="idea-feed__media">
			<?php if ( $video ) : ?>
				<video class="idea-feed__video" controls autoplay muted loop playsinline
					poster="<?php echo esc_url( $cover ); ?>">
					<source src="<?php echo esc_url( $video ); ?>">
					مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
				</video>
			<?php elseif ( $cover ) : ?>
				<img class="idea-feed__img" src="<?php echo esc_url( $cover ); ?>" alt="<?php echo esc_attr( $idea['title'] ); ?>">
			<?php else : ?>
				<div class="idea-feed__ph">بدون تصویر</div>
			<?php endif; ?>

			<div class="idea-feed__overlay">
				<div class="idea-feed__nav idea-feed__nav--prev">
					<?php if ( $nav['prev'] ) : ?><a href="<?php echo esc_url( $nav['prev']['link'] ); ?>" title="ایده قبلی"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a><?php endif; ?>
				</div>
				<div class="idea-feed__nav idea-feed__nav--next">
					<?php if ( $nav['next'] ) : ?><a href="<?php echo esc_url( $nav['next']['link'] ); ?>" title="ایده بعدی"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg></a><?php endif; ?>
				</div>
			</div>
		</div>

		<div class="idea-feed__body">
			<div class="idea-feed__meta">
				<span class="idea-feed__dot"></span>
				<span>ایده دکوراسیون</span>
			</div>
			<h1 class="idea-feed__title"><?php echo esc_html( $idea['title'] ); ?></h1>
			<?php if ( ! empty( $idea['content'] ) ) : ?>
				<div class="idea-feed__content"><?php echo wp_kses_post( wpautop( $idea['content'] ) ); ?></div>
			<?php endif; ?>

			<a class="idea-feed__back" href="<?php echo esc_url( senoobar_ideas_page_url() ?: home_url( '/' ) ); ?>">
				بازگشت به گالری
			</a>
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

	// ناوبری صفحه‌کلید (راست/چپ)
	document.addEventListener('keydown', function (e) {
		if (e.key === 'ArrowLeft')  go(next);  // در RTL راست = بعدی
		if (e.key === 'ArrowRight') go(prev);
	});

	// سوایپ لمسی (موبایل) — کشیدن به بالا = ایده بعدی
	var y0 = null;
	document.addEventListener('touchstart', function (e) { y0 = e.touches[0].clientY; }, { passive: true });
	document.addEventListener('touchend', function (e) {
		if (y0 === null) return;
		var dy = e.changedTouches[0].clientY - y0;
		y0 = null;
		if (Math.abs(dy) > 80) {
			if (dy < 0) go(next);      // کشیدن بالا
			else go(prev);             // کشیدن پایین
		}
	}, { passive: true });
})();
</script>
<?php get_footer();
