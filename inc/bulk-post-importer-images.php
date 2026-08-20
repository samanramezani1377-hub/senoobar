<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function senoobar_import_image( $file ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	senoobar_allow_webp();

	$id = media_handle_sideload( $file, 0 );
	return is_wp_error( $id ) ? false : (int) $id;
}

/* افزودن webp به فهرست mime مجاز */
function senoobar_allow_webp() {
	if ( has_filter( 'upload_mimes', 'senoobar_add_webp_mime' ) ) {
		return;
	}
	add_filter( 'upload_mimes', 'senoobar_add_webp_mime', 20 );
}

function senoobar_add_webp_mime( $mimes ) {
	if ( ! isset( $mimes['webp'] ) ) {
		$mimes['webp'] = 'image/webp';
	}
	return $mimes;
}

/* ریدایرکت با پیام */
function senoobar_import_redirect( $type, $msg ) {
	set_transient( 'senoobar_bulk_import_notice', array( 'type' => $type, 'msg' => $msg ), 60 );
	wp_safe_redirect( admin_url( 'edit.php?page=senoobar-bulk-import' ) );
	exit;
}

/* پردازش ZIP */
add_action( 'admin_post_senoobar_zip_import', 'senoobar_zip_import_handler' );

function senoobar_zip_import_handler() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'دسترسی ناکافی.' );
	}
	check_admin_referer( 'senoobar_zip_import', 'senoobar_zip_nonce' );

	if ( empty( $_FILES['zip_file'] ) || UPLOAD_ERR_OK !== $_FILES['zip_file']['error'] ) {
		senoobar_import_redirect( 'error', 'هیچ فایل ZIP انتخاب نشده است.' );
	}

	$tmp = $_FILES['zip_file']['tmp_name'];
	$zip = new ZipArchive();

	if ( true !== $zip->open( $tmp ) ) {
		senoobar_import_redirect( 'error', 'فایل ZIP قابل باز کردن نیست.' );
	}

	$status = isset( $_POST['zip_status'] ) ? sanitize_key( $_POST['zip_status'] ) : 'publish';
	$cat    = isset( $_POST['zip_cat'] ) && $_POST['zip_cat'] ? (int) $_POST['zip_cat'] : 0;

	$extract_dir = senoobar_temp_extract_dir();
	$zip->extractTo( $extract_dir );
	$zip->close();

	$images_map = senoobar_upload_zip_images( $extract_dir );

	$created = 0;
	$errors  = array();
	$text_files = senoobar_find_text_files( $extract_dir );

	foreach ( $text_files as $rel_path ) {
		$full    = trailingslashit( $extract_dir ) . $rel_path;
		$content = @file_get_contents( $full );
		if ( false === $content ) {
			continue;
		}

		$ext = strtolower( pathinfo( $rel_path, PATHINFO_EXTENSION ) );
		$r = senoobar_create_post_from_file(
			$content,
			$ext,
			array( 'status' => $status, 'category' => $cat ),
			$images_map
		);
		if ( $r[0] ) {
			$created++;
		} else {
			$errors[] = $rel_path . ': ' . $r[1];
		}
	}

	senoobar_cleanup_dir( $extract_dir );

	if ( $created > 0 ) {
		$msg  = sprintf( '✔ %d نوشته با موفقیت اضافه شد.', $created );
		$msg .= $errors ? ' (خطا در ' . count( $errors ) . ' فایل)' : '';
		$msg .= $images_map ? ' — 🖼️ ' . count( $images_map ) . ' عکس آپلود شد.' : '';
		senoobar_import_redirect( 'success', $msg );
	} else {
		senoobar_import_redirect( 'error', 'هیچ فایل متنی (txt/md/html) در ZIP پیدا نشد.' );
	}
}

/* پردازش تک‌فایل */
add_action( 'admin_post_senoobar_single_import', 'senoobar_single_import_handler' );

function senoobar_single_import_handler() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( 'دسترسی ناکافی.' );
	}
	check_admin_referer( 'senoobar_single_import', 'senoobar_single_nonce' );

	if ( empty( $_FILES['post_file'] ) || UPLOAD_ERR_OK !== $_FILES['post_file']['error'] ) {
		senoobar_import_redirect( 'error', 'هیچ فایل نوشته‌ای انتخاب نشده است.' );
	}

	$name = isset( $_FILES['post_file']['name'] ) ? $_FILES['post_file']['name'] : '';
	$ext  = strtolower( pathinfo( $name, PATHINFO_EXTENSION ) );
	if ( ! in_array( $ext, senoobar_bulk_allowed_ext(), true ) ) {
		senoobar_import_redirect( 'error', 'فرمت فایل پشتیبانی نمی‌شود (مجاز: txt / md / html).' );
	}

	$content = (string) file_get_contents( $_FILES['post_file']['tmp_name'] );

	$images_map = array();

	$args = array(
		'title'    => isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '',
		'status'   => isset( $_POST['single_status'] ) ? sanitize_key( $_POST['single_status'] ) : 'publish',
		'excerpt'  => isset( $_POST['post_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['post_excerpt'] ) ) : '',
		'category' => isset( $_POST['single_cat'] ) && $_POST['single_cat'] ? (int) $_POST['single_cat'] : 0,
	);

	if ( ! empty( $_FILES['post_image'] ) && UPLOAD_ERR_OK === $_FILES['post_image']['error'] ) {
		$image_id = senoobar_import_image( $_FILES['post_image'] );
		if ( $image_id ) {
			$args['image_id'] = $image_id;
		}
	}

	$r = senoobar_create_post_from_file( $content, $ext, $args, $images_map );

	if ( $r[0] ) {
		$link = get_edit_post_link( $r[1], 'raw' );
		senoobar_import_redirect( 'success', '✔ نوشته ساخته شد. ' . ( $link ? '<a href="' . esc_url( $link ) . '">ویرایش نوشته</a>' : '' ) );
	} else {
		senoobar_import_redirect( 'error', 'خطا در ساخت نوشته: ' . $r[1] );
	}
}

/* ابزارهای کمکی */
function senoobar_temp_extract_dir() {
	$dir = wp_upload_dir();
	$tmp = trailingslashit( $dir['basedir'] ) . 'senoobar-bpi-' . uniqid();
	wp_mkdir_p( $tmp );
	return $tmp;
}

function senoobar_cleanup_dir( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ( $it as $f ) {
		$f->isDir() ? rmdir( $f->getPathname() ) : unlink( $f->getPathname() );
	}
	rmdir( $dir );
}

function senoobar_find_text_files( $dir ) {
	$allowed = array( 'txt', 'md', 'markdown', 'html', 'htm' );
	$files   = array();
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $dir, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $it as $file ) {
		if ( $file->isFile() ) {
			$ext = strtolower( pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) );
			if ( in_array( $ext, $allowed, true ) ) {
				$files[] = ltrim( str_replace( $dir, '', $file->getPathname() ), '/\\' );
			}
		}
	}
	sort( $files );
	return $files;
}

function senoobar_upload_zip_images( $extract_dir ) {
	$image_ext = array( 'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg' );
	$map = array();

	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $extract_dir, FilesystemIterator::SKIP_DOTS )
	);
	foreach ( $it as $file ) {
		if ( ! $file->isFile() ) {
			continue;
		}
		$ext = strtolower( pathinfo( $file->getFilename(), PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, $image_ext, true ) ) {
			continue;
		}

		$rel = ltrim( str_replace( $extract_dir, '', $file->getPathname() ), '/\\' );

		$file_array = array(
			'name'     => $file->getFilename(),
			'tmp_name' => $file->getPathname(),
			'type'     => senoobar_image_mime( $file->getPathname() ),
			'error'    => 0,
			'size'     => $file->getSize(),
		);

		$attach_id = senoobar_import_image( $file_array );
		if ( $attach_id ) {
			$map[ $rel ] = $attach_id;
		}
	}

	return $map;
}

function senoobar_image_mime( $path ) {
	$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
	$m = array(
		'png'  => 'image/png',
		'jpg'  => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'gif'  => 'image/gif',
		'webp' => 'image/webp',
		'svg'  => 'image/svg+xml',
	);
	return isset( $m[ $ext ] ) ? $m[ $ext ] : 'image/jpeg';
}
