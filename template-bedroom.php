<?php
/**
 * Template Name: نمایشگاه اتاق خواب رویایی شما
 * Description: صفحه‌ی نمایشگاهی برای سرویس خواب و اتاق خواب.
 *
 * @package Senoobar
 */

get_header();

$data = senoobar_showroom_data( 'bedroom' );

get_template_part( 'template-parts/showroom', 'layout', $data );

get_footer();
