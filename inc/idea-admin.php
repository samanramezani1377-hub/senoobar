<?php
/**
 * Senoobar — فرم اختصاصی «افزودن / ویرایش ایده».
 *
 * به‌جای ادیتور بلاکی پیش‌فرض وردپرس، یک فرم مستقل با کادرهای مشخص می‌سازد:
 *   - عنوان
 *   - تصویر کاور (آپلود / انتخاب از کتابخانه)
 *   - ویدیو (آپلود / انتخاب) — اختیاری
 *   - توضیحات (متن آزاد)
 * و یک دکمه‌ی «انتشار / به‌روزرسانی».
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────────────────────────────────────────────
   ۱. یکی‌کردن «افزودن ایده» پیش‌فرض CPT با فرم اختصاصی
   ───────────────────────────────────────────────
   به‌جای ساخت یک ساب‌منوی جدا (که باعث دیده‌شدن دو آیتم «افزودن ایده»
   می‌شد)، آیتم پیش‌فرضِ نوع محتوا را همان فرم اختصاصی می‌کنیم تا
   فقط یک گزینه‌ی «افزودن ایده» وجود داشته باشد:
      ۱) با filter روی admin_url، لینک post-new.php?post_type=senoobar_idea
         به فرم اختصاصی اشاره می‌کند.
      ۲) با admin_init، باز کردن مستقیم post-new.php هم به فرم redirect می‌شود.
   باید load-{hook} مربوط به صفحه‌ی فرم هم استایل/اسکریپت را بارگذاری کند.
   ─────────────────────────────────────────────── */
function senoobar_idea_admin_menu() {
	$hook = add_submenu_page(
		'edit.php?post_type=senoobar_idea',
		'افزودن ایده جدید',
		'افزودن ایده',
		'edit_posts',
		'senoobar-add-idea',
		'senoobar_idea_admin_page'
	);
	add_action( 'load-' . $hook, 'senoobar_idea_admin_assets' );
	// حذف آیتم «افرودن ایده» پیش‌فرض وردپرس تا دوتایی نشود
	remove_submenu_page( 'edit.php?post_type=senoobar_idea', 'post-new.php?post_type=senoobar_idea' );
}
add_action( 'admin_menu', 'senoobar_idea_admin_menu' );

/* ───────────────────────────────────────────────
   ۲. هدایت «افزودن ایده» پیش‌فرض به فرم اختصاصی ما
   ─────────────────────────────────────────────── */
add_filter( 'admin_url', 'senoobar_idea_admin_url_filter', 10, 3 );

function senoobar_idea_admin_url_filter( $url, $path, $blog_id ) {
	// وقتی لینک post-new.php?post_type=senoobar_idea ساخته می‌شود، به فرم ما اشاره کند.
	if ( false !== strpos( $path, 'post-new.php' ) && false !== strpos( $path, 'senoobar_idea' ) ) {
		return admin_url( 'edit.php?post_type=senoobar_idea&page=senoobar-add-idea' );
	}
	return $url;
}

/* باز کردن مستقیم post-new.php?post_type=senoobar_idea → فرم اختصاصی */
function senoobar_idea_redirect_new_to_form() {
	global $pagenow;
	if ( 'post-new.php' === $pagenow && isset( $_GET['post_type'] ) && 'senoobar_idea' === $_GET['post_type'] ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=senoobar_idea&page=senoobar-add-idea' ) );
		exit;
	}
}
add_action( 'admin_init', 'senoobar_idea_redirect_new_to_form' );

/* ───────────────────────────────────────────────
   ۳. بارگذاری استایل/اسکریپت مخصوص فرم
   ─────────────────────────────────────────────── */
function senoobar_idea_admin_assets() {
	wp_enqueue_media(); // برای باز کردن کتابخانه رسانه
	wp_enqueue_script(
		'senoobar-idea-admin',
		SENOOBAR_URI . '/assets/js/idea-admin.js',
		[ 'jquery', 'media-editor' ],
		SENOOBAR_VERSION,
		true
	);
	wp_enqueue_style(
		'senoobar-idea-admin',
		SENOOBAR_URI . '/assets/css/idea-admin.css',
		[],
		SENOOBAR_VERSION
	);
}

/* ───────────────────────────────────────────────
   ۴. پردازش ارسال فرم
   ─────────────────────────────────────────────── */
function senoobar_idea_handle_submit() {
	if ( ! isset( $_POST['senoobar_idea_form'] ) ) {
		return;
	}
	if ( ! check_admin_referer( 'senoobar_idea_form_nonce', 'senoobar_idea_nonce' ) ) {
		wp_die( 'امنیت فرم تأیید نشد.' );
	}
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'دسترسی لازم را ندارید.' );
	}

	$edit_id    = isset( $_POST['senoobar_idea_id'] ) ? absint( $_POST['senoobar_idea_id'] ) : 0;
	$title      = isset( $_POST['senoobar_idea_title'] ) ? sanitize_text_field( wp_unslash( $_POST['senoobar_idea_title'] ) ) : '';
	$desc       = isset( $_POST['senoobar_idea_desc'] ) ? wp_kses_post( wp_unslash( $_POST['senoobar_idea_desc'] ) ) : '';
	$cover_id   = isset( $_POST['senoobar_idea_cover'] ) ? absint( $_POST['senoobar_idea_cover'] ) : 0;
	$video      = isset( $_POST['senoobar_idea_video'] ) ? esc_url_raw( wp_unslash( $_POST['senoobar_idea_video'] ) ) : '';

	if ( '' === $title ) {
		wp_die( 'عنوان ایده الزامی است.' );
	}

	$post_data = [
		'post_type'    => 'senoobar_idea',
		'post_title'   => $title,
		'post_content' => $desc,
		'post_status'  => 'publish',
	];

	if ( $edit_id && get_post( $edit_id ) ) {
		$post_data['ID'] = $edit_id;
		$new_id = wp_update_post( $post_data, true );
	} else {
		$new_id = wp_insert_post( $post_data, true );
	}

	if ( is_wp_error( $new_id ) ) {
		wp_die( 'خطا در ذخیره: ' . esc_html( $new_id->get_error_message() ) );
	}

	// تصویر کاور
	if ( $cover_id ) {
		set_post_thumbnail( $new_id, $cover_id );
	} elseif ( $edit_id ) {
		delete_post_thumbnail( $new_id );
	}

	// ویدیو
	if ( '' === $video ) {
		delete_post_meta( $new_id, '_senoobar_idea_video' );
	} else {
		update_post_meta( $new_id, '_senoobar_idea_video', $video );
	}

	$redirect = add_query_arg(
		[ 'senoobar_idea_saved' => $new_id ],
		admin_url( 'edit.php?post_type=senoobar_idea&page=senoobar-add-idea' )
	);
	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'admin_init', 'senoobar_idea_handle_submit' );

/* ───────────────────────────────────────────────
   ۵. نمایش صفحه‌ی فرم
   ─────────────────────────────────────────────── */
function senoobar_idea_admin_page() {
	$edit_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
	$post    = $edit_id ? get_post( $edit_id ) : null;

	$title    = $post ? get_the_title( $post ) : '';
	$desc     = $post ? $post->post_content : '';
	$cover_id = $post ? get_post_thumbnail_id( $post ) : 0;
	$video    = $post ? senoobar_idea_video( $post->ID ) : '';

	$saved = isset( $_GET['senoobar_idea_saved'] ) ? absint( $_GET['senoobar_idea_saved'] ) : 0;
	?>
	<div class="wrap senoobar-idea-admin">
		<h1 class="senoobar-idea-admin__title">
			<?php echo $post ? 'ویرایش ایده' : 'افزودن ایده جدید'; ?>
		</h1>

		<?php if ( $saved ) : ?>
			<div class="notice notice-success is-dismissible"><p>ایده با موفقیت ذخیره و منتشر شد. <a href="<?php echo esc_url( get_permalink( $saved ) ); ?>" target="_blank">مشاهده ایده ←</a></p></div>
		<?php endif; ?>

		<form method="post" class="senoobar-idea-form">
			<?php wp_nonce_field( 'senoobar_idea_form_nonce', 'senoobar_idea_nonce' ); ?>
			<input type="hidden" name="senoobar_idea_form" value="1">
			<input type="hidden" name="senoobar_idea_id" value="<?php echo esc_attr( $edit_id ); ?>">

			<div class="senoobar-idea-form__grid">

				<!-- عنوان -->
				<div class="senoobar-idea-field">
					<label for="senoobar_idea_title">عنوان ایده <span class="req">*</span></label>
					<input type="text" id="senoobar_idea_title" name="senoobar_idea_title"
						value="<?php echo esc_attr( $title ); ?>"
						placeholder="مثلاً: اتاق خواب آرامش‌بخش"
						required>
				</div>

				<!-- تصویر کاور -->
				<div class="senoobar-idea-field">
					<label>تصویر کاور (تصویر شاخص)</label>
					<div class="senoobar-idea-media" data-type="image">
						<div class="senoobar-idea-media__preview" id="senoobar_cover_preview">
							<?php if ( $cover_id ) : ?>
								<?php echo wp_get_attachment_image( $cover_id, 'medium' ); ?>
							<?php else : ?>
								<span class="senoobar-idea-media__empty">تصویری انتخاب نشده</span>
							<?php endif; ?>
						</div>
						<input type="hidden" name="senoobar_idea_cover" id="senoobar_idea_cover" value="<?php echo esc_attr( $cover_id ); ?>">
						<p class="senoobar-idea-media__actions">
							<button type="button" class="button senoobar-media-btn" data-field="senoobar_idea_cover" data-type="image">انتخاب / آپلود تصویر</button>
							<button type="button" class="button senoobar-media-clear" data-field="senoobar_idea_cover" data-preview="senoobar_cover_preview">حذف تصویر</button>
						</p>
					</div>
				</div>

				<!-- ویدیو -->
				<div class="senoobar-idea-field">
					<label>ویدیو (اختیاری)</label>
					<div class="senoobar-idea-media" data-type="video">
						<div class="senoobar-idea-media__preview" id="senoobar_video_preview">
							<?php if ( $video ) : ?>
								<video src="<?php echo esc_url( $video ); ?>" controls muted></video>
							<?php else : ?>
								<span class="senoobar-idea-media__empty">ویدیویی انتخاب نشده</span>
							<?php endif; ?>
						</div>
						<input type="hidden" name="senoobar_idea_video" id="senoobar_idea_video" value="<?php echo esc_attr( $video ); ?>">
						<p class="senoobar-idea-media__actions">
							<button type="button" class="button senoobar-media-btn" data-field="senoobar_idea_video" data-type="video">انتخاب / آپلود ویدیو</button>
							<button type="button" class="button senoobar-media-clear" data-field="senoobar_idea_video" data-preview="senoobar_video_preview">حذف ویدیو</button>
						</p>
					</div>
				</div>

			</div>

			<!-- توضیحات -->
			<div class="senoobar-idea-field">
				<label for="senoobar_idea_desc">توضیحات ایده</label>
				<textarea id="senoobar_idea_desc" name="senoobar_idea_desc" rows="6"
					placeholder="درباره این ایده توضیح دهید..."><?php echo esc_textarea( $desc ); ?></textarea>
			</div>

			<p class="senoobar-idea-form__submit">
				<button type="submit" class="button button-primary button-hero">
					<?php echo $post ? 'به‌روزرسانی ایده' : 'انتشار ایده'; ?>
				</button>
				<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=senoobar_idea' ) ); ?>">بازگشت به لیست</a>
			</p>
		</form>
	</div>
	<?php
}


/* ───────────────────────────────────────────────
   ۶. هدایت «ویرایش» در لیست ایده‌ها به فرم اختصاصی
   ─────────────────────────────────────────────── */
function senoobar_idea_row_actions_redirect( $actions, $post ) {
	if ( 'senoobar_idea' !== $post->post_type ) {
		return $actions;
	}
	if ( isset( $actions['edit'] ) ) {
		$url = admin_url( 'edit.php?post_type=senoobar_idea&page=senoobar-add-idea&id=' . $post->ID );
		$actions['edit'] = '<a href="' . esc_url( $url ) . '" aria-label="ویرایش این ایده">ویرایش</a>';
	}
	return $actions;
}
add_filter( 'post_row_actions', 'senoobar_idea_row_actions_redirect', 10, 2 );
