<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<?php
/**
 * Fires immediately after the opening <head> tag.
 */
do_action( 'fl_head_open' );
?>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<?php
/**
 * The viewport meta tag HTML string output in the <head>.
 */
echo apply_filters( 'fl_theme_viewport', "<meta name='viewport' content='width=device-width, initial-scale=1.0' />\n" );
/**
 * The X-UA-Compatible meta tag HTML string output in the <head>.
 */
echo apply_filters( 'fl_theme_xua_compatible', "<meta http-equiv='X-UA-Compatible' content='IE=edge' />\n" );
?>
<link rel="profile" href="https://gmpg.org/xfn/11" />
<?php

wp_head();

FLTheme::head();

?>
</head>
<body <?php body_class(); ?><?php FLTheme::print_schema( ' itemscope="itemscope" itemtype="https://schema.org/WebPage"' ); ?>>
<?php

FLTheme::header_code();

/**
 * Fires immediately after the opening <body> tag.
 */
do_action( 'fl_body_open' );

?>
<div class="fl-page">
	<?php

	/**
	 * Fires at the opening of the main page wrapper div.
	 */
	do_action( 'fl_page_open' );

	FLTheme::fixed_header();

	/**
	 * Fires immediately before the top bar is rendered.
	 */
	do_action( 'fl_before_top_bar' );

	FLTheme::top_bar();

	/**
	 * Fires immediately after the top bar is rendered.
	 */
	do_action( 'fl_after_top_bar' );
	/**
	 * Fires immediately before the header is rendered.
	 */
	do_action( 'fl_before_header' );

	FLTheme::header_layout();

	/**
	 * Fires immediately after the header is rendered.
	 */
	do_action( 'fl_after_header' );
	/**
	 * Fires immediately before the main content area is rendered.
	 */
	do_action( 'fl_before_content' );

	?>
	<div id="fl-main-content" class="fl-page-content" itemprop="mainContentOfPage" role="main">

		<?php
		/**
		 * Fires at the opening of the main content area.
		 */
		do_action( 'fl_content_open' );
		?>
