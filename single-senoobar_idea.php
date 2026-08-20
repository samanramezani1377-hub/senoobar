<?php
get_header();
$video = senoobar_idea_video( get_the_ID() );
?>
<main id="primary" class="site-main">
	<div class="container page-content" style="max-width:960px;">
		<?php while ( have_posts() ) : the_post(); ?>
		<article id="idea-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="mb-4">
				<?php the_title( '<h1 class="section__title" style="text-align:right;font-size:1.8rem;">', '</h1>' ); ?>
			</header>

			<div class="idea-single__media">
				<?php if ( $video ) : ?>
					<video class="idea-single__video" controls playsinline
						poster="<?php echo esc_url( get_the_post_thumbnail_url( get_the_ID(), 'large' ) ?: '' ); ?>">
						<source src="<?php echo esc_url( $video ); ?>">
						مرورگر شما از پخش ویدیو پشتیبانی نمی‌کند.
					</video>
				<?php elseif ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'large', [ 'class' => 'w-100' ] ); ?>
				<?php endif; ?>
			</div>

			<?php if ( $video && has_post_thumbnail() ) : ?>
				<div class="idea-single__cover">
					<?php the_post_thumbnail( 'large', [ 'class' => 'w-100' ] ); ?>
				</div>
			<?php endif; ?>

			<div class="entry-content" style="line-height:2.2;font-size:0.95rem;margin-top:24px;">
				<?php the_content(); ?>
			</div>
		</article>
		<?php endwhile; ?>
	</div>
</main>
<?php get_footer();
