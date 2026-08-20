<?php
/**
 * Senoobar — گالری ایده‌ها (Ideas Gallery).
 *
 * یک نوع محتوای مجزا (ایده) می‌سازد که برای نمایش پست‌های عکس‌محور / ویدیویی
 * در بخش «ایده‌هایی برای خانه شما» صفحه اصلی استفاده می‌شود. برخلاف مقالات،
 * این محتوا بصری (کاور تصویر + ویدیوی اختیاری + تیتر) است.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* 1. ثبت نوع محتوای «ایده» */
function senoobar_register_idea_cpt() {
	$labels = [
		'name'               => 'ایده‌ها',
		'singular_name'      => 'ایده',
		'menu_name'          => 'گالری ایده‌ها',
		'add_new'            => 'افزودن ایده',
		'add_new_item'       => 'افزودن ایده جدید',
		'edit_item'          => 'ویرایش ایده',
		'new_item'           => 'ایده جدید',
		'view_item'          => 'مشاهده ایده',
		'search_items'       => 'جستجوی ایده‌ها',
		'not_found'          => 'ایده‌ای پیدا نشد.',
		'not_found_in_trash' => 'ایده‌ای در زباله‌دان نیست.',
		'featured_image'     => 'تصویر کاور',
		'set_featured_image' => 'انتخاب تصویر کاور',
		'remove_featured_image' => 'حذف تصویر کاور',
		'use_featured_image' => 'استفاده به‌عنوان کاور',
	];

	register_post_type( 'senoobar_idea', [
		'labels'        => $labels,
		'public'        => true,
		'has_archive'   => true,
		'menu_icon'     => 'dashicons-format-gallery',
		'menu_position' => 6,
		'supports'      => [ 'title', 'thumbnail', 'revisions', 'editor' ],
		'show_in_rest'  => true,
		'rewrite'       => [ 'slug' => 'idea', 'with_front' => false ],
	] );
}
add_action( 'init', 'senoobar_register_idea_cpt' );

/* 2. متاباکس «ویدیو» */
function senoobar_idea_add_meta_boxes() {
	add_meta_box(
		'senoobar_idea_video',
		'ویدیو (اختیاری)',
		'senoobar_idea_video_meta_box',
		'senoobar_idea',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'senoobar_idea_add_meta_boxes' );

function senoobar_idea_video_meta_box( $post ) {
	wp_nonce_field( 'senoobar_idea_video_nonce', 'senoobar_idea_video_nonce' );
	$video = get_post_meta( $post->ID, '_senoobar_idea_video', true );
	$video = is_string( $video ) ? $video : '';
	?>
	<div class="senoobar-idea-video-field" style="margin-top:8px;">
		<input type="text"
			id="senoobar_idea_video"
			name="senoobar_idea_video"
			value="<?php echo esc_attr( $video ); ?>"
			placeholder="آدرس ویدیو (mp4 یا لینک) — اختیاری"
			style="width:100%;max-width:560px;direction:ltr;text-align:left;"
		/>
		<p>
			<button type="button" class="button" id="senoobar_idea_video_upload">انتخاب از کتابخانه</button>
			<button type="button" class="button" id="senoobar_idea_video_clear">پاک کردن</button>
		</p>
		<p class="description">اگر ویدیویی انتخاب کنید، روی کارت این ایده نشان «ویدیو» نمایش داده می‌شود. اگر فقط عکس می‌خواهید، این فیلد را خالی بگذارید (تصویر کاور همیشه نمایش داده می‌شود).</p>
	</div>
	<script>
	(function () {
		var field = document.getElementById('senoobar_idea_video');
		if (!field) return;
		var frame;
		document.getElementById('senoobar_idea_video_upload').addEventListener('click', function (e) {
			e.preventDefault();
			if (frame) { frame.open(); return; }
			frame = wp.media({
				title: 'انتخاب ویدیو',
				library: { type: 'video' },
				multiple: false,
				button: { text: 'انتخاب این ویدیو' }
			});
			frame.on('select', function () {
				var att = frame.state().get('selection').first().toJSON();
				field.value = att.url;
			});
			frame.open();
		});
		document.getElementById('senoobar_idea_video_clear').addEventListener('click', function (e) {
			e.preventDefault();
			field.value = '';
		});
	})();
	</script>
	<?php
}

function senoobar_idea_save( $post_id ) {
	if ( ! isset( $_POST['senoobar_idea_video_nonce'] )
		|| ! wp_verify_nonce( $_POST['senoobar_idea_video_nonce'], 'senoobar_idea_video_nonce' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( isset( $_POST['post_type'] ) && 'senoobar_idea' === $_POST['post_type'] ) {
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	$video = isset( $_POST['senoobar_idea_video'] ) ? sanitize_text_field( wp_unslash( $_POST['senoobar_idea_video'] ) ) : '';
	if ( '' === $video ) {
		delete_post_meta( $post_id, '_senoobar_idea_video' );
	} else {
		update_post_meta( $post_id, '_senoobar_idea_video', $video );
	}
}
add_action( 'save_post', 'senoobar_idea_save' );

/* 3. کمکی: ویدیوی یک ایده */
function senoobar_idea_video( $post_id ) {
	$video = get_post_meta( $post_id, '_senoobar_idea_video', true );
	return is_string( $video ) ? trim( $video ) : '';
}

/* 4. کوئری آخرین ایده‌ها */
function senoobar_ideas_query( $count = 4 ) {
	$posts = get_posts( [
		'post_type'      => 'senoobar_idea',
		'post_status'    => 'publish',
		'numberposts'    => (int) $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'suppress_filters' => false,
	] );

	$items = [];
	foreach ( $posts as $p ) {
		$cover_id = get_post_thumbnail_id( $p->ID );
		$cover    = $cover_id ? wp_get_attachment_image_url( $cover_id, 'large' ) : '';
		$items[]  = [
			'id'    => (int) $p->ID,
			'title' => get_the_title( $p ),
			'cover' => $cover,
			'video' => senoobar_idea_video( $p->ID ),
			'link'  => get_permalink( $p ),
		];
	}
	return $items;
}
