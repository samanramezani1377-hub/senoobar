<?php
/**
 * Template Name: نمایشگاه مبلمان مدرن و راحت
 * Description: صفحه‌ی نمایشگاهی برای مبل و مبلمان.
 *
 * @package Senoobar
 */

get_header();

$data = senoobar_showroom_data( 'furniture' );

get_template_part( 'template-parts/showroom', 'layout', $data );

get_footer();
