// Quick links config (label => [key, icon, auto_url, default_url])
$snb_quick_defs = [
    'account'  => ['حساب کاربری', 'account', $snb_account_url],
    'wishlist' => ['علاقه‌مندی‌ها', 'wishlist', $snb_wish_url],
    'cart'     => ['سبد خرید', 'cart', $snb_cart_url],
    'about'    => ['درباره ما', 'text', function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('about') ? senoobar_legal_page_url('about') : home_url('/about/')],
    'contact'  => ['تماس با ما', 'text', function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('contact') ? senoobar_legal_page_url('contact') : home_url('/contact/')],
    'faq'      => ['سوالات متداول', 'text', function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('faq') ? senoobar_legal_page_url('faq') : home_url('/faq/')],
    'terms'    => ['شرایط و ضوابط', 'text', function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('terms') ? senoobar_legal_page_url('terms') : home_url('/terms-and-conditions/')],
    'privacy'  => ['حریم خصوصی', 'text', function_exists('senoobar_legal_page_url') && senoobar_legal_page_url('privacy') ? senoobar_legal_page_url('privacy') : home_url('/privacy-policy/')],
];