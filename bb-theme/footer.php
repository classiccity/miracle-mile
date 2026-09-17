<?php
/**
 * Fires at the closing of the main content area.
 */
do_action( 'fl_content_close' );
?>

	</div><!-- .fl-page-content -->
	<?php

	/**
	 * Fires immediately after the main content area is rendered.
	 */
	do_action( 'fl_after_content' );

	if ( FLTheme::has_footer() ) :

		?>
	<footer class="fl-page-footer-wrap"<?php FLTheme::print_schema( ' itemscope="itemscope" itemtype="https://schema.org/WPFooter"' ); ?>  role="contentinfo">
		<?php

		/**
		 * Fires at the opening of the footer wrapper element.
		 */
		do_action( 'fl_footer_wrap_open' );
		/**
		 * Fires immediately before the footer widget areas are rendered.
		 */
		do_action( 'fl_before_footer_widgets' );

		FLTheme::footer_widgets();

		/**
		 * Fires immediately after the footer widget areas are rendered.
		 */
		do_action( 'fl_after_footer_widgets' );
		/**
		 * Fires immediately before the footer bar is rendered.
		 */
		do_action( 'fl_before_footer' );

		FLTheme::footer();

		/**
		 * Fires immediately after the footer bar is rendered.
		 */
		do_action( 'fl_after_footer' );
		/**
		 * Fires at the closing of the footer wrapper element.
		 */
		do_action( 'fl_footer_wrap_close' );

		?>
	</footer>
	<?php endif; ?>
	<?php
	/**
	 * Fires at the closing of the main page wrapper div.
	 */
	do_action( 'fl_page_close' );
	?>
</div><!-- .fl-page -->
<?php

wp_footer();

/**
 * Fires immediately before the closing </body> tag.
 */
do_action( 'fl_body_close' );

FLTheme::footer_code();

?>
</body>
</html>
