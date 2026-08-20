<?php
/**
 * Senoobar — گالری ایده‌ها (Ideas Gallery).
 *
 * یک «نوع محتوای مجزا» می‌سازد که رفتارش دقیقاً مثل یک برگه/صفحه عادی است:
 *   - تیتر (عنوان)
 *   - تصویر کاور (تصویر شاخص)
 *   - محتوای آزاد (ادیتور بلاکی، همان‌طور که یک صفحه می‌سازید)
 *   - ویدیوی اختیاری (یک فیلد ساده در ستون کناری)
 *
 * نمایش:
 *   - صفحه‌ی تکی ایده → مثل اینستاگرام (پیوسته با اسکرول به ایده بعدی)
 *   - صفحه‌ی «گالری ایده‌ها» → مثل یک پیج با گرید عکس/ویدیو (کاور + تیتر)
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ════════════════════════════════════════════════════════════════
   ۱. ثبت نوع محتوای «ایده» (درست مثل ثبت برگه، ولی نوع جدا)
   ════════════════════════════════════════════════════════════════ */
function senoobar_register_idea_cpt() {
	$labels = [
		'name'                  => 'ایده‌ها',
		'singular_name'         => 'ایده',
		'menu_name'             => 'گالری ایده‌ها',
		'add_new'               => 'افزودن ایده',
		'add_new_item'          => 'افزودن ایده جدید',
		'edit_item'             => 'ویرایش ایده',
		'new_item'              => 'ایده جدید',
		'view_item'             => 'مشاهده ایده',
		'view_items'            => 'مشاهده ایده‌ها',
		'search_items'          => 'جستجوی ایده‌ها',
		'not_found'             => 'ایده‌ای پیدا نشد.',
		'not_found_in_trash'    => 'ایده‌ای در زباله‌دان نیست.',
		'all_items'             => 'همه ایده‌ها',
		'archives'              => 'گالری ایده‌ها',
		'featured_image'        => 'تصویر کاور',
		'set_featured_image'    => 'انتخاب تصویر کاور',
		'remove_featured_image' => 'حذف تصویر کاور',
		'use_featured_image'    => 'استفاده به‌عنوان کاور',
	];

	register_post_type( 'senoobar_idea', [
		'labels'       => $labels,
		'description'  => 'ایده‌های بصری (عکس/ویدیو) برای الهام‌بخشی دکوراسیون.',
		'public'       => true,
		'show_in_rest' => true,          // ادیتور بلاکی (مثل برگه)
		'menu_icon'    => 'dashicons-format-gallery',
		'menu_position'=> 6,
		// نکته: cpt بدون آرشیو خودکار؛ آرشیو از طریق «صفحه‌ی قالب» ساخته می‌شود
		// تا لینک «گالری ایده‌ها» همیشه درست باشد.
		'has_archive'  => false,
		'rewrite'      => [ 'slug' => 'idea', 'with_front' => false ],
		'supports'     => [ 'title', 'editor', 'thumbnail', 'revisions', 'excerpt' ],
	] );
}
add_action( 'init', 'senoobar_register_idea_cpt' );

/**
 * بازسازی ساختار پیوندها (یک‌بار بعد از نصب/فعال‌سازی تم).
 * بدون این، صفحه‌ی تکی ایده 404 می‌شود.
 */
function senoobar_idea_maybe_flush_rewrite() {
	if ( get_option( 'senoobar_idea_rewrite_flushed_v2' ) ) {
		return;
	}
	senoobar_register_idea_cpt();
	flush_rewrite_rules();
	update_option( 'senoobar_idea_rewrite_flushed_v2', 1 );
}
add_action( 'init', 'senoobar_idea_maybe_flush_rewrite', 20 );

/* ════════════════════════════════════════════════════════════════
   ۲. فیلد «ویدیو» — در ستون کناری (تا ادیتور مثل برگه، تمام‌عرض بماند)
   ════════════════════════════════════════════════════════════════ */
function senoobar_idea_add_meta_boxes() {
	add_meta_box(
		'senoobar_idea_video',
		'ویدیو (اختیاری)',
		'senoobar_idea_video_meta_box',
		'senoobar_idea',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'senoobar_idea_add_meta_boxes' );

function senoobar_idea_video_meta_box( $post ) {
	wp_nonce_field( 'senoobar_idea_video_nonce', 'senoobar_idea_video_nonce' );
	$video = senoobar_idea_video( $post->ID );
	?>
	<p style="margin:0 0 8px;">
		<input type="text" id="senoobar_idea_video" name="senoobar_idea_video"
			value="<?php echo esc_attr( $video ); ?>"
			placeholder="آدرس فایل ویدیو (mp4)"
			style="width:100%;direction:ltr;text-align:left;"
		/>
	</p>
	<p style="margin:0 0 8px;">
		<button type="button" class="button" id="senoobar_idea_video_upload">انتخاب از کتابخانه</button>
		<button type="button" class="button" id="senoobar_idea_video_clear">پاک کردن</button>
	</p>
	<p class="description" style="margin:0;">اگر ویدیو بگذارید، روی کارت نشان «ویدیو» می‌آید. اگر خالی باشد فقط کاور نمایش داده می‌شود.</p>
	<script>
	(function () {
		var field = document.getElementById('senoobar_idea_video');
		if (!field) return;
		var frame;
		var btnUp = document.getElementById('senoobar_idea_video_upload');
		var btnCl = document.getElementById('senoobar_idea_video_clear');
		if (btnUp) btnUp.addEventListener('click', function (e) {
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
		if (btnCl) btnCl.addEventListener('click', function (e) {
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

/* ════════════════════════════════════════════════════════════════
   ۳. کمکی‌ها
   ════════════════════════════════════════════════════════════════ */
function senoobar_idea_video( $post_id ) {
	$video = get_post_meta( $post_id, '_senoobar_idea_video', true );
	return is_string( $video ) ? trim( $video ) : '';
}

function senoobar_idea_cover( $post_id, $size = 'large' ) {
	$cover_id = get_post_thumbnail_id( $post_id );
	if ( $cover_id ) {
		$url = wp_get_attachment_image_url( $cover_id, $size );
		if ( $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * آرایه‌ی یک ایده (برای استفاده در قالب‌ها).
 */
function senoobar_idea_item( $post ) {
	if ( is_int( $post ) ) {
		$post = get_post( $post );
	}
	if ( ! $post ) {
		return null;
	}
	return [
		'id'     => (int) $post->ID,
		'title'  => get_the_title( $post ),
		'cover'  => senoobar_idea_cover( $post->ID ),
		'video'  => senoobar_idea_video( $post->ID ),
		'link'   => get_permalink( $post ),
		'content'=> get_the_content( null, false, $post ),
	];
}

/**
 * آخرین ایده‌ها.
 */
function senoobar_ideas_query( $count = 4 ) {
	$posts = get_posts( [
		'post_type'      => 'senoobar_idea',
		'post_status'    => 'publish',
		'numberposts'    => (int) $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'suppress_filters'=> false,
	] );

	$items = [];
	foreach ( $posts as $p ) {
		$items[] = senoobar_idea_item( $p );
	}
	return array_filter( $items );
}

/**
 * آدرس صفحه‌ی «گالری ایده‌ها» (صفحه‌ی قالب). اگر نبود، خالی.
 */
function senoobar_ideas_page_url() {
	$id = (int) get_option( 'senoobar_ideas_page_id' );
	if ( $id && 'publish' === get_post_status( $id ) ) {
		return get_permalink( $id );
	}
	return '';
}

/* ════════════════════════════════════════════════════════════════
   ۴. ثبت قالب صفحه و ساخت خودکار صفحه‌ی «گالری ایده‌ها»
   ════════════════════════════════════════════════════════════════ */
function senoobar_register_ideas_template( $templates ) {
	$templates['template-ideas.php'] = 'گالری ایده‌ها';
	return $templates;
}
add_filter( 'theme_page_templates', 'senoobar_register_ideas_template' );

function senoobar_ensure_ideas_page() {
	if ( get_option( 'senoobar_ideas_page_created' ) ) {
		return;
	}

	if ( ! (int) get_option( 'senoobar_ideas_page_id' ) ) {
		$existing = get_pages( [
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'template-ideas.php',
			'number'     => 1,
		] );

		if ( ! empty( $existing ) ) {
			update_option( 'senoobar_ideas_page_id', $existing[0]->ID );
		} else {
			$page_id = wp_insert_post( [
				'post_title'  => 'گالری ایده‌ها',
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => 'gallery-ideas',
				'post_content'=> '',
			], true );

			if ( ! is_wp_error( $page_id ) ) {
				update_post_meta( $page_id, '_wp_page_template', 'template-ideas.php' );
				update_option( 'senoobar_ideas_page_id', $page_id );
			}
		}
	}

	update_option( 'senoobar_ideas_page_created', 1 );
}
add_action( 'after_setup_theme', 'senoobar_ensure_ideas_page', 32 );

/* ════════════════════════════════════════════════════════════════
   ۵. موارد مرتبط (ایده‌های بعد/قبل برای ناوبری اینستاگرامی)
   ════════════════════════════════════════════════════════════════ */
function senoobar_idea_nav() {
	$prev = get_previous_post();
	$next = get_next_post();
	return [
		'prev' => $prev ? senoobar_idea_item( $prev ) : null,
		'next' => $next ? senoobar_idea_item( $next ) : null,
	];
}
