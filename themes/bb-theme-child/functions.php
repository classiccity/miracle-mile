<?php
/**
 * Beaver Builder child theme for Miracle Mile Shops.
 *
 * Keep this file to setup and includes only. Site-specific behavior lives in inc/,
 * one file per concern.
 */

// Defines
define( 'FL_CHILD_THEME_DIR', get_stylesheet_directory() );
define( 'FL_CHILD_THEME_URL', get_stylesheet_directory_uri() );

// Classes
require_once 'classes/class-fl-child-theme.php';

// Actions
add_action( 'wp_enqueue_scripts', 'FLChildTheme::enqueue_scripts', 1000 );

// Includes, one per concern
$mms_includes = array(
	'inc/theme-options.php',                // ACF "Theme Options" page
	'inc/post-types.php',                   // custom post types (was Custom Post Type UI)
	'inc/page-taxonomies.php',              // categories + tags on pages (was Create And Assign Categories For Pages)
	'inc/acf-readonly-fields.php',          // ACF fields locked in the admin
	'inc/gravity-forms-coupon-counter.php', // coupon-code counter on Gravity Forms form 2
);
foreach ( $mms_includes as $mms_include ) {
	require_once FL_CHILD_THEME_DIR . '/' . $mms_include;
}
unset( $mms_includes, $mms_include );
