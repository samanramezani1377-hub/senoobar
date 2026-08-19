<?php
define('SENOOBAR_VERSION', '2.0.9');
define('SENOOBAR_DIR', get_template_directory());
define('SENOOBAR_URI', get_template_directory_uri());

require_once SENOOBAR_DIR . '/inc/class-senoobar-theme.php';
require_once SENOOBAR_DIR . '/inc/cart-handlers.php';
require_once SENOOBAR_DIR . '/inc/woocommerce-setup.php';
require_once SENOOBAR_DIR . '/inc/cart-page-setup.php';
require_once SENOOBAR_DIR . '/inc/newsletter-handlers.php';
require_once SENOOBAR_DIR . '/inc/push-handlers.php';
require_once SENOOBAR_DIR . '/inc/wishlist-page-setup.php';
require_once SENOOBAR_DIR . '/inc/legal-pages-setup.php';
require_once SENOOBAR_DIR . '/inc/account.php';
require_once SENOOBAR_DIR . '/inc/wishlist.php';
require_once SENOOBAR_DIR . '/inc/otp-login.php';