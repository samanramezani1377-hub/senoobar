<?php
get_header();
?>
<main id="primary" class="site-main">
	<div class="container page-content">
		<header class="mb-4" style="text-align:right;">
			<h1 class="section__title" style="font-size:1.8rem;">گالری ایده‌ها</h1>
			<p style="color:var(--color-gray-500);margin-top:8px;">ایده‌هایی برای خانه شما</p>
		</header>

		<?php if ( have_posts() ) : ?>
		<div class="idea-grid">
			<?php while ( have_posts() ) : the_post();
				$video = senoobar_idea_video( get_the_ID() );
			?>
			<a href="<?php the_permalink(); ?>" class="idea-card">
				<div class="idea-card__media">
					<?php if ( has_post_thumbnail() ) : the_post_thumbnail( 'large', [ 'loading' => 'lazy' ] ); else : ?>
						<img src="<?php echo esc_url( SENOOBAR_URI . '/assets/images/hero-1.jpg' ); ?>" alt="" loading="lazy">
					<?php endif; ?>
					<?php if ( $video ) : ?>
						<span class="idea-card__badge">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
							ویدیو
						</span>
					<?php endif; ?>
				</div>
				<div class="idea-card__body">
					<h3 class="idea-card__title"><?php the_title(); ?></h3>
				</div>
			</a>
			<?php endwhile; ?>
		</div>
		<?php else : ?>
			<p style="text-align:right;">هنوز ایده‌ای ثبت نشده است.</p>
		<?php endif; ?>
	</div>
</main>
<?php get_footer();
