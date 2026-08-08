<?php
define('SENOOBAR_VERSION','1.0.0');
define('SENOOBAR_DIR',get_template_directory());
define('SENOOBAR_URI',get_template_directory_uri());
require_once SENOOBAR_DIR.'/inc/class-senoobar-theme.php';
function senoobar_init(){Senoobar_Theme::get_instance()->init();}
add_action('after_setup_theme','senoobar_init');