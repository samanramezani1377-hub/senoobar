<?php
/**
 * Senoobar — ایمپورت گروهی نوشته از فایل (ZIP / تکی).
 *
 * قابلیت‌ها:
 *   ۱. آپلود ZIP شامل چند فایل متنی (.txt / .md / .markdown / .html / .htm) — هر فایل به یک نوشته تبدیل می‌شود (عنوان = اولین خط غیرخالی).
 *   ۲. آپلود تکی با عنوان، دسته، خلاصه و تصویر شاخص.
 *   ۳. تبدیل Markdown → HTML (تیتر، لیست، پاراگراف، بولد، ایتالیک، لینک، عکس).
 *   ۴. پردازش کامل عکس‌های درون متن با هر تعداد و هر موقعیتی (منطق در bulk-post-importer-images.php).
 *   ۵. اولین عکس مقاله به‌صورت تصویر شاخص ست می‌شود.
 *   ۶. پشتیبانی از WEBP / PNG / JPG / GIF.
 *
 * @package Senoobar
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ۰. منوی ادمین (زیرمنوی «نوشته‌ها») */
add_action( 'admin_menu', 'senoobar_bulk_import_menu' );

function senoobar_bulk_import_menu() {
	add_posts_page(
		'ایمپورت گروهی نوشته‌ها',
		'ایمپورت گروهی',
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
		<h1>📥 ایمپورت گروهی نوشته‌ها</h1>

		<?php if ( $notice ) : ?>
			<div class="notice <?php echo esc_attr( $notice['type'] ); ?> is-dismissible"><p><?php echo wp_kses_post( $notice['msg'] ); ?></p></div>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:16px;">
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
				<h2 style="margin-top:0;">📦 آپلود دسته‌ای (ZIP)</h2>
				<p style="color:#555;font-size:.9rem;">یک فایل ZIP شامل چند فایل متنی (txt / md / html) را بارگذاری کنید تا هر فایل به یک نوشته تبدیل شود.</p>
				<p style="color:#1e3a2f;font-size:.9rem;background:#f0f7f4;padding:10px;border-radius:8px;">🖼️ عکس‌های درون متن (با هر تعداد و موقعیتی) به‌صورت خودکار از ZIP استخراج و به کتابخانه رسانه اضافه می‌شوند؛ اولین عکس، تصویر شاخص می‌شود.</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'senoobar_zip_import', 'senoobar_zip_nonce' ); ?>
					<input type="hidden" name="action" value="senoobar_zip_import">
					<p><label><strong>فایل ZIP:</strong><br><input type="file" name="zip_file" accept=".zip" required style="margin-top:6px;"></label></p>
					<p><label><strong>دسته (اختیاری):</strong><br><?php wp_dropdown_categories( array( 'show_option_all' => '— بدون دسته —', 'hide_empty' => 0, 'name' => 'zip_cat', 'taxonomy' => 'category' ) ); ?></label></p>
					<p><label><strong>وضعیت انتشار:</strong><br><select name="zip_status" style="margin-top:6px;"><option value="publish">منتشر شده</option><option value="draft">پیش‌نویس</option></select></label></p>
					<button type="submit" class="button button-primary button-large">افزودن نوشته‌ها</button>
				</form>
			</div>

			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
				<h2 style="margin-top:0;">📄 افزودن تک‌نوشته</h2>
				<p style="color:#555;font-size:.9rem;">یک فایل متنی (txt / md / html) بارگذاری کنید. عنوان از اولین خط گرفته می‌شود مگر اینکه خودتان عنوان بنویسید.</p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'senoobar_single_import', 'senoobar_single_nonce' ); ?>
					<input type="hidden" name="action" value="senoobar_single_import">
					<p><label><strong>فایل نوشته:</strong><br><input type="file" name="post_file" accept=".txt,.md,.html,.htm" required style="margin-top:6px;"></label></p>
					<p><label><strong>عنوان (اختیاری):</strong><br><input type="text" name="post_title" class="regular-text" style="margin-top:6px;" dir="rtl"></label></p>
					<p><label><strong>دسته (اختیاری):</strong><br><?php wp_dropdown_categories( array( 'show_option_all' => '— بدون دسته —', 'hide_empty' => 0, 'name' => 'single_cat', 'taxonomy' => 'category' ) ); ?></label></p>
					<p><label><strong>خلاصه (اختیاری):</strong><br><textarea name="post_excerpt" rows="2" class="large-text" style="margin-top:6px;" dir="rtl"></textarea></label></p>
					<p><label><strong>تصویر شاخص (اختیاری):</strong><br><input type="file" name="post_image" accept="image/*" style="margin-top:6px;"></label></p>
					<p><label><strong>وضعیت انتشار:</strong><br><select name="single_status" style="margin-top:6px;"><option value="publish">منتشر شده</option><option value="draft">پیش‌نویس</option></select></label></p>
					<button type="submit" class="button button-primary button-large">افزودن نوشته</button>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/* ۱. پسوندهای مجاز */
function senoobar_bulk_allowed_ext() {
	return array( 'txt', 'md', 'markdown', 'html', 'htm' );
}

/* ۲. تبدیل فایل → HTML */
function senoobar_file_to_html( $content, $ext ) {
	$content = trim( (string) $content );

	if ( 'md' === $ext || 'markdown' === $ext ) {
		$content = senoobar_simple_markdown( $content );
	} elseif ( 'html' === $ext || 'htm' === $ext ) {
		if ( preg_match( '/<body[^>]*>(.*?)<\/body>/is', $content, $m ) ) {
			$content = $m[1];
		}
		return wp_kses_post( $content );
	} else {
		$content = wp_strip_all_tags( $content );
		$content = esc_html( $content );
		$content = preg_replace( "/\r?\n/", "\n", $content );
		$paras   = preg_split( "/\n\s*\n/", $content );
		$paras   = array_map( function ( $p ) { return '<p>' . nl2br( trim( $p ) ) . '</p>'; }, array_filter( $paras, 'trim' ) );
		$content = implode( "\n", $paras );
	}

	return wp_kses_post( $content );
}

/* تبدیل markdown ساده (عکس به تگ <img> با data-src نگه‌داری می‌شود) */
function senoobar_simple_markdown( $text ) {
	$text  = str_replace( "\r\n", "\n", $text );
	$lines = explode( "\n", $text );

	$html = array();
	$list = null;
	$list_buf = array();

	$flush_list = function () use ( &$list, &$list_buf, &$html ) {
		if ( $list !== null ) {
			$html[] = '<' . $list . '>' . implode( '', $list_buf ) . '</' . $list . '>';
			$list   = null;
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

		if ( preg_match( '/^(#{1,6})\s+(.*)$/', $trim, $m ) ) {
			$flush_list();
			$lvl = min( 6, strlen( $m[1] ) );
			$html[] = '<h' . $lvl . '>' . senoobar_md_inline( $m[2] ) . '</h' . $lvl . '>';
			continue;
		}

		if ( preg_match( '/^[-*+]\s+(.*)$/', $trim, $m ) ) {
			if ( $list !== 'ul' ) { $flush_list(); $list = 'ul'; }
			$list_buf[] = '<li>' . senoobar_md_inline( $m[1] ) . '</li>';
			continue;
		}

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

/* اینلاین markdown */
function senoobar_md_inline( $text ) {
	$text = esc_html( $text );

	// عکس
	$text = preg_replace_callback(
		'/!\[([^\]]*)\]\(([^)\s]+)(?:\s+"([^"]*)")?\)/',
		function ( $m ) {
			$alt   = $m[1];
			$src   = $m[2];
			$title = isset( $m[3] ) ? $m[3] : '';
			$title_attr = $title ? ' title="' . esc_attr( $title ) . '"' : '';
			return '<img src="' . esc_attr( $src ) . '" alt="' . esc_attr( $alt ) . '"' . $title_attr . ' class="senoobar-bpi-img" data-src="' . esc_attr( $src ) . '" />';
		},
		$text
	);

	// لینک
	$text = preg_replace_callback(
		'/\[([^\]\[]+)\]\(([^)]+)\)/',
		function ( $m ) {
			return '<a href="' . esc_url( $m[2] ) . '" target="_blank" rel="noopener">' . esc_html( $m[1] ) . '</a>';
		},
		$text
	);

	$text = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text );
	$text = preg_replace( '/\*([^*]+)\*/s', '<em>$1</em>', $text );

	return $text;
}

/* ۳. جداسازی عنوان و بدنه */
function senoobar_split_title_body( $raw ) {
	$raw  = str_replace( "\r\n", "\n", (string) $raw );
	$raw  = str_replace( "\r", "\n", $raw );
	$lines = preg_split( '/\n/', $raw );
	$title = '';
	$body_start = 0;

	foreach ( $lines as $i => $line ) {
		$t = trim( $line );
		if ( '' !== $t ) {
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

	if ( function_exists( 'mb_strlen' ) && mb_strlen( $title ) > 200 ) {
		$title = mb_substr( $title, 0, 200 );
	}

	return array( $title, $body );
}

/* ۴. ساخت نوشته از فایل */
function senoobar_create_post_from_file( $content, $ext, $args = array(), $images_map = array() ) {
	list( $auto_title, $body ) = senoobar_split_title_body( $content );

	$title = isset( $args['title'] ) && trim( $args['title'] ) !== '' ? trim( $args['title'] ) : $auto_title;
	if ( '' === $title ) {
		$title = 'نوشته بدون عنوان ' . gmdate( 'Y-m-d H:i:s' );
	}

	$html = senoobar_file_to_html( $body, $ext );

	$processed = senoobar_process_inline_images( $html, $images_map );
	$html      = $processed['html'];

	$postarr = array(
		'post_type'    => 'post',
		'post_status'  => isset( $args['status'] ) ? $args['status'] : 'publish',
		'post_title'   => wp_strip_all_tags( $title ),
		'post_content' => $html,
		'post_excerpt' => isset( $args['excerpt'] ) ? $args['excerpt'] : '',
	);

	$post_id = wp_insert_post( $postarr, true );

	if ( is_wp_error( $post_id ) ) {
		return array( false, $post_id->get_error_message() );
	}

	if ( ! empty( $args['category'] ) ) {
		wp_set_post_categories( $post_id, array( (int) $args['category'] ) );
	}

	if ( ! empty( $processed['first_image_id'] ) ) {
		set_post_thumbnail( $post_id, (int) $processed['first_image_id'] );
	} elseif ( ! empty( $args['image_id'] ) ) {
		set_post_thumbnail( $post_id, (int) $args['image_id'] );
	}

	return array( true, $post_id );
}

/* ۵. پردازش عکس‌های درون متن */
function senoobar_process_inline_images( $html, $images_map = array() ) {
	$first_image_id = 0;
	$image_count    = 0;

	$html = preg_replace_callback(
		'/<img\s+[^>]*data-src="([^"]+)"[^>]*>/i',
		function ( $m ) use ( $images_map, &$first_image_id, &$image_count ) {
			$full_tag = $m[0];
			$src      = html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' );

			if ( preg_match( '/^https?:\/\//i', $src ) ) {
				$image_count++;
				return preg_replace( '/\s+class="senoobar-bpi-img"\s+data-src="[^"]*"/i', '', $full_tag );
			}

			$clean_src = ltrim( $src, './\\' );
			if ( isset( $images_map[ $clean_src ] ) ) {
				$attach_id = (int) $images_map[ $clean_src ];
				$url = wp_get_attachment_url( $attach_id );
				if ( $url ) {
					if ( ! $first_image_id ) {
						$first_image_id = $attach_id;
					}
					$image_count++;
					$clean_tag = preg_replace( '/\s+class="senoobar-bpi-img"\s+data-src="[^"]*"/i', '', $full_tag );
					$clean_tag = preg_replace( '/\s+src="[^"]*"/i', ' src="' . esc_attr( $url ) . '"', $clean_tag );
					return $clean_tag;
				}
			}

			return '';
		},
		$html
	);

	return array(
		'html'           => $html,
		'first_image_id' => $first_image_id,
		'image_count'    => $image_count,
	);
}

require_once __DIR__ . '/bulk-post-importer-images.php';
