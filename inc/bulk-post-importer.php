<?php
/**
 * Senoobar — افزودن نوشته به‌صورت فایل (ZIP / تکی).
 *
 * یک صفحه‌ی اختصاصی زیر منوی «نوشته‌ها» می‌سازد که از آن می‌توان:
 *   ۱) یک فایل ZIP شامل چند فایل متنی (txt/md/html) آپلود کرد → هر فایل یک نوشته.
 *   ۲) یک نوشته‌ی تکی با فرم کامل (فایل + عنوان/دسته/برچسب/خلاصه/تصویر شاخص) ساخت.
 *
 * قواعد استخراج از فایل:
 *   - «اولین خطِ غیرخالی» فایل = عنوان نوشته (اگر عنوانِ فرم جداگانه وارد نشده باشد،
 *     یا در حالت ZIP که عنوان فقط از فایل گرفته می‌شود).
 *   - باقی‌مانده‌ی فایل = متن نوشته.
 *   - برای فایل‌های Markdown، در حد امکان به HTML تبدیل می‌شود.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ───────────────────────────────────────────────
   ۰. منو و صفحه
   ─────────────────────────────────────────────── */
add_action( 'admin_menu', 'senoobar_bulk_import_menu' );

function senoobar_bulk_import_menu() {
	add_posts_page(
		'افزودن نوشته از فایل',
		'افزودن نوشته از فایل',
		'edit_posts',
		'senoobar-bulk-import',
		'senoobar_bulk_import_page'
	);
}

function senoobar_bulk_import_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$notice = get_transient( 'senoobar_bulk_import_notice' );
	if ( $notice ) {
		delete_transient( 'senoobar_bulk_import_notice' );
	}
	?>
	<div class="wrap" dir="rtl">
		<h1 style="color:#1e3a2f;">📝 افزودن نوشته از فایل</h1>
		<p style="color:#666;">یک فایل ZIP (چند نوشته یک‌جا) یا یک فایل متنی را آپلود کنید. «اولین خطِ غیرخالیِ» هر فایل به‌عنوان عنوان نوشته استفاده می‌شود.</p>

		<?php if ( $notice ) : ?>
			<div class="notice <?php echo esc_attr( $notice['type'] ); ?> is-dismissible"><p><?php echo wp_kses_post( $notice['msg'] ); ?></p></div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:16px;">
			<!-- حالت ZIP -->
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
				<h2 style="margin-top:0;">📦 آپلود چندتایی (ZIP)</h2>
				<p style="color:#555;font-size:.9rem;">هر فایل داخل ZIP (txt / md / html) به یک نوشته تبدیل می‌شود. نام یا نخستین خط، عنوان نوشته است.</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'senoobar_zip_import', 'senoobar_zip_nonce' ); ?>
					<input type="hidden" name="action" value="senoobar_zip_import">
					<p>
						<label><strong>فایل ZIP:</strong><br>
						<input type="file" name="zip_file" accept=".zip" required style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>دسته (اختیاری):</strong><br>
						<?php wp_dropdown_categories( array( 'show_option_all' => '— بدون دسته —', 'hide_empty' => 0, 'name' => 'zip_cat', 'taxonomy' => 'category' ) ); ?></label>
					</p>
					<p>
						<label><strong>وضعیت انتشار:</strong><br>
						<select name="zip_status" style="margin-top:6px;">
							<option value="publish">منتشر شده</option>
							<option value="draft">پیش‌نویس</option>
						</select></label>
					</p>
					<button type="submit" class="button button-primary button-large">افزودن نوشته‌ها</button>
				</form>
			</div>

			<!-- حالت تکی -->
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
				<h2 style="margin-top:0;">📄 افزودن یک نوشته</h2>
				<p style="color:#555;font-size:.9rem;">فایل متنی (txt / md / html) را آپلود کنید. در صورت خالی بودن عنوان، نخستین خط فایل عنوان می‌شود.</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'senoobar_single_import', 'senoobar_single_nonce' ); ?>
					<input type="hidden" name="action" value="senoobar_single_import">
					<p>
						<label><strong>فایل نوشته:</strong><br>
						<input type="file" name="post_file" accept=".txt,.md,.html,.htm" required style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>عنوان (اختیاری — خالی باشد از فایل گرفته می‌شود):</strong><br>
						<input type="text" name="post_title" class="regular-text" style="margin-top:6px;" dir="rtl"></label>
					</p>
					<p>
						<label><strong>دسته (اختیاری):</strong><br>
						<?php wp_dropdown_categories( array( 'show_option_all' => '— بدون دسته —', 'hide_empty' => 0, 'name' => 'single_cat', 'taxonomy' => 'category' ) ); ?></label>
					</p>
					<p>
						<label><strong>خلاصه (اختیاری):</strong><br>
						<textarea name="post_excerpt" rows="2" class="large-text" style="margin-top:6px;" dir="rtl"></textarea></label>
					</p>
					<p>
						<label><strong>تصویر شاخص (اختیاری):</strong><br>
						<input type="file" name="post_image" accept="image/*" style="margin-top:6px;"></label>
					</p>
					<p>
						<label><strong>وضعیت انتشار:</strong><br>
						<select name="single_status" style="margin-top:6px;">
							<option value="publish">منتشر شده</option>
							<option value="draft">پیش‌نویس</option>
						</select></label>
					</p>
					<button type="submit" class="button button-primary button-large">افزودن نوشته</button>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/* ───────────────────────────────────────────────
   ۱. ابزارهای کمکی
   ─────────────────────────────────────────────── */

/** پشته‌ی نام‌های مجاز برای استخراج از ZIP (بدون پیمایش دایرکتوری‌های خطرناک) */
function senoobar_zip_allowed_ext() {
	return array( 'txt', 'md', 'markdown', 'html', 'htm' );
}

/** تبدیل محتوای متنی به HTML ایمن. برای markdown، تبدیل سبک و ساده. */
function senoobar_file_to_html( $content, $ext ) {
	$content = trim( (string) $content );

	if ( 'md' === $ext || 'markdown' === $ext ) {
		$content = senoobar_simple_markdown( $content );
	} elseif ( 'html' === $ext || 'htm' === $ext ) {
		// html واقعی: بخش body را در حد امکان جدا می‌کنیم؛ در غیر این صورت همان را می‌پذیریم.
		if ( preg_match( '/<body[^>]*>(.*?)<\/body>/is', $content, $m ) ) {
			$content = $m[1];
		}
		return wp_kses_post( $content );
	} else {
		// txt ساده → هر خط به یک پاراگراف، با حفظ خط خالی
		$content = wp_strip_all_tags( $content );
		$content = esc_html( $content );
		$content = preg_replace( "/\r?\n/", "\n", $content );
		$paras   = preg_split( "/\n\s*\n/", $content );
		$paras   = array_map( function ( $p ) {
			return '<p>' . nl2br( trim( $p ) ) . '</p>';
		}, array_filter( $paras, 'trim' ) );
		$content = implode( "\n", $paras );
	}

	return wp_kses_post( $content );
}

/** تبدیل markdown بسیار سبک (تیتر، لیست، بولد/ایتالیک، لینک، پاراگراف) */
function senoobar_simple_markdown( $text ) {
	$text = str_replace( "\r\n", "\n", $text );
	$lines = explode( "\n", $text );

	$html = array();
	$list = null; // 'ul' یا 'ol'
	$list_buf = array();

	$flush_list = function () use ( &$list, &$list_buf, &$html ) {
		if ( $list !== null ) {
			$html[] = '<' . $list . '>' . implode( '', $list_buf ) . '</' . $list . '>';
			$list = null;
			$list_buf = array();
		}
	};

	foreach ( $lines as $line ) {
		$trim = trim( $line );
		if ( '' === $trim ) {
			$flush_list();
			$html[] = '';
			continue;
		}
		// تیتر
		if ( preg_match( '/^(#{1,6})\s+(.*)$/', $trim, $m ) ) {
			$flush_list();
			$lvl     = min( 6, strlen( $m[1] ) );
			$html[]  = '<h' . $lvl . ' style="color:#1e3a2f;">' . esc_html( $m[2] ) . '</h' . $lvl . '>';
			continue;
		}
		// لیست نامرتب
		if ( preg_match( '/^[-*+]\s+(.*)$/', $trim, $m ) ) {
			if ( $list !== 'ul' ) { $flush_list(); $list = 'ul'; }
			$list_buf[] = '<li>' . senoobar_md_inline( $m[1] ) . '</li>';
			continue;
		}
		// لیست مرتب
		if ( preg_match( '/^\d+[.)]\s+(.*)$/', $trim, $m ) ) {
			if ( $list !== 'ol' ) { $flush_list(); $list = 'ol'; }
			$list_buf[] = '<li>' . senoobar_md_inline( $m[1] ) . '</li>';
			continue;
		}
		$flush_list();
		$html[] = '<p>' . senoobar_md_inline( $trim ) . '</p>';
	}
	$flush_list();
	return implode( "\n", $html );
}

/** اینلاین markdown: **bold**، *italic*، [link](url) */
function senoobar_md_inline( $text ) {
	$text = esc_html( $text );
	$text = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text );
	$text = preg_replace( '/\*(.+?)\*/s', '<em>$1</em>', $text );
	$text = preg_replace_callback( '/\[([^\]]+)\]\(([^)]+)\)/', function ( $m ) {
		return '<a href="' . esc_url( $m[2] ) . '" target="_blank" rel="noopener">' . esc_html( $m[1] ) . '</a>';
	}, $text );
	return $text;
}

/** استخراج: نخستین خطِ غیرخالی = عنوان، باقی = بدنه */
function senoobar_split_title_body( $raw ) {
	$raw  = str_replace( "\r\n", "\n", (string) $raw );
	$raw  = str_replace( "\r", "\n", $raw );
	$lines = preg_split( '/\n/', $raw );
	$title = '';
	$body_start = 0;

	foreach ( $lines as $i => $line ) {
		$t = trim( $line );
		if ( '' !== $t ) {
			// اگر خط اول یک تیتر markdown بود (# ...) آن را عنوان می‌کنیم
			if ( preg_match( '/^#{1,6}\s+(.*)$/', $t, $m ) ) {
				$title = trim( $m[1] );
			} else {
				$title = $t;
			}
			$body_start = $i + 1;
			break;
		}
	}
	$body = implode( "\n", array_slice( $lines, $body_start ) );

	// اگر عنوان طولانی‌تر از حد معقول بود، کوتاه کن
	if ( function_exists( 'mb_strlen' ) && mb_strlen( $title ) > 200 ) {
		$title = mb_substr( $title, 0, 200 );
	}

	return array( $title, $body );
}

/** ساخت نوشته از متن + تنظیمات */
function senoobar_create_post_from_file( $content, $ext, $args = array() ) {
	list( $auto_title, $body ) = senoobar_split_title_body( $content );

	$title = isset( $args['title'] ) && trim( $args['title'] ) !== '' ? trim( $args['title'] ) : $auto_title;
	if ( '' === $title ) {
		$title = 'نوشته بدون عنوان ' . gmdate( 'Y-m-d H:i:s' );
	}

	$postarr = array(
		'post_type'    => 'post',
		'post_status'  => isset( $args['status'] ) ? $args['status'] : 'publish',
		'post_title'   => wp_strip_all_tags( $title ),
		'post_content' => senoobar_file_to_html( $body, $ext ),
		'post_excerpt' => isset( $args['excerpt'] ) ? $args['excerpt'] : '',
	);

	$post_id = wp_insert_post( $postarr, true );

	if ( is_wp_error( $post_id ) ) {
		return array( false, $post_id->get_error_message() );
	}

	// دسته
	if ( ! empty( $args['category'] ) ) {
		wp_set_post_categories( $post_id, array( (int) $args['category'] ) );
	}

	// تصویر شاخص
	if ( ! empty( $args['image_id'] ) ) {
		set_post_thumbnail( $post_id, (int) $args['image_id'] );
	}

	return array( true, $post_id );
}

/** بارگذاری تصویر از $_FILES موقت و برگرداندن attachment id */
function senoobar_import_image( $file ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$id = media_handle_sideload( $file, 0 );
	return is_wp_error( $id ) ? false : (int) $id;
}

/* ───────────────────────────────────────────────
   ۲. پردازش ZIP
   ─────────────────────────────────────────────── */
add_action( 'admin_post_senoobar_zip_import', 'senoobar_zip_import_handler' );

function senoobar_zip_import_handler() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'دسترسی غیرمجاز.' );
	}
	check_admin_referer( 'senoobar_zip_import', 'senoobar_zip_nonce' );

	if ( empty( $_FILES['zip_file'] ) || UPLOAD_ERR_OK !== $_FILES['zip_file']['error'] ) {
		senoobar_import_redirect( 'error', 'لطفاً یک فایل ZIP انتخاب کنید.' );
	}

	$tmp = $_FILES['zip_file']['tmp_name'];
	$zip = new ZipArchive();

	if ( true !== $zip->open( $tmp ) ) {
		senoobar_import_redirect( 'error', 'فایل ZIP قابل باز شدن نبود.' );
	}

	$allowed = senoobar_zip_allowed_ext();
	$status  = isset( $_POST['zip_status'] ) ? sanitize_key( $_POST['zip_status'] ) : 'publish';
	$cat     = isset( $_POST['zip_cat'] ) && $_POST['zip_cat'] ? (int) $_POST['zip_cat'] : 0;

	$created = 0;
	$errors  = array();

	for ( $i = 0; $i < $zip->numFiles; $i++ ) {
		$name = $zip->getNameIndex( $i );
		// رد کردن پوشه‌ها و فایل‌های مخفی / مسیرهای خطرناک
		if ( substr( $name, -1 ) === '/' || strpos( $name, '..' ) !== false ) {
			continue;
		}
		$ext = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, $allowed, true ) ) {
			continue;
		}

		$content = $zip->getFromIndex( $i );
		if ( false === $content ) {
			continue;
		}

		$r = senoobar_create_post_from_file( $content, $ext, array( 'status' => $status, 'category' => $cat ) );
		if ( $r[0] ) {
			$created++;
		} else {
			$errors[] = $name . ': ' . $r[1];
		}
	}
	$zip->close();

	if ( $created > 0 ) {
		senoobar_import_redirect( 'success', sprintf( '✅ %d نوشته با موفقیت اضافه شد.', $created ) . ( $errors ? ' (خطا در ' . count( $errors ) . ' فایل)' : '' ) );
	} else {
		senoobar_import_redirect( 'error', 'هیچ فایل معتبری (txt/md/html) در ZIP یافت نشد.' );
	}
}

/* ───────────────────────────────────────────────
   ۳. پردازش نوشته‌ی تکی
   ─────────────────────────────────────────────── */
add_action( 'admin_post_senoobar_single_import', 'senoobar_single_import_handler' );

function senoobar_single_import_handler() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'دسترسی غیرمجاز.' );
	}
	check_admin_referer( 'senoobar_single_import', 'senoobar_single_nonce' );

	if ( empty( $_FILES['post_file'] ) || UPLOAD_ERR_OK !== $_FILES['post_file']['error'] ) {
		senoobar_import_redirect( 'error', 'لطفاً فایل نوشته را انتخاب کنید.' );
	}

	$name = isset( $_FILES['post_file']['name'] ) ? $_FILES['post_file']['name'] : '';
	$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, senoobar_zip_allowed_ext(), true ) ) {
		senoobar_import_redirect( 'error', 'فرمت فایل پشتیبانی نمی‌شود (فقط txt / md / html).' );
	}

	$content = (string) file_get_contents( $_FILES['post_file']['tmp_name'] );

	$args = array(
		'title'    => isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '',
		'status'   => isset( $_POST['single_status'] ) ? sanitize_key( $_POST['single_status'] ) : 'publish',
		'excerpt'  => isset( $_POST['post_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['post_excerpt'] ) ) : '',
		'category' => isset( $_POST['single_cat'] ) && $_POST['single_cat'] ? (int) $_POST['single_cat'] : 0,
	);

	// تصویر شاخص
	if ( ! empty( $_FILES['post_image'] ) && UPLOAD_ERR_OK === $_FILES['post_image']['error'] ) {
		$image_id = senoobar_import_image( $_FILES['post_image'] );
		if ( $image_id ) {
			$args['image_id'] = $image_id;
		}
	}

	$r = senoobar_create_post_from_file( $content, $ext, $args );

	if ( $r[0] ) {
		$link = get_edit_post_link( $r[1], 'raw' );
		senoobar_import_redirect( 'success', '✅ نوشته ساخته شد. ' . ( $link ? '<a href="' . esc_url( $link ) . '">ویرایش نوشته</a>' : '' ) );
	} else {
		senoobar_import_redirect( 'error', 'خطا در ساخت نوشته: ' . $r[1] );
	}
}

/* ───────────────────────────────────────────────
   ۴. ریدایرکت با اعلان
   ─────────────────────────────────────────────── */
function senoobar_import_redirect( $type, $msg ) {
	set_transient( 'senoobar_bulk_import_notice', array( 'type' => $type, 'msg' => $msg ), 60 );
	wp_safe_redirect( admin_url( 'edit.php?page=senoobar-bulk-import' ) );
	exit;
}
