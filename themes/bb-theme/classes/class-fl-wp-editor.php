<?php
/**
 * Handle integration with the new (gutenberg) editor.
 *
 * @since 1.7
 */
class FLWPEditor {

	/**
	* Per-request guard so the CSS cache is only rebuilt once per load.
	*
	* Set by whichever refresh_css() caller fires first: customize_preview_init,
	* customize_save_after, after_switch_theme, enqueue_styles(), or
	* inject_editor_styles(). Any later caller in the same request is skipped.
	*
	* @var bool
	*/
	static private $refreshed = false;

	/**
	* Init the editor styles manager
	*
	* @access public
	* @return void
	*/
	static public function init() {
		add_action( 'customize_preview_init', 'FLWPEditor::refresh_css' );
		add_action( 'customize_save_after', 'FLWPEditor::refresh_css' );
		add_action( 'after_switch_theme', 'FLWPEditor::refresh_css' );
		add_action( 'enqueue_block_editor_assets', 'FLWPEditor::enqueue_styles' );
		add_filter( 'block_editor_settings_all', 'FLWPEditor::inject_editor_styles' );
	}

	/**
	* Get the base filename slug
	*
	* @access public
	* @return string
	*/
	static public function slug() {
		return 'editor';
	}

	/**
	* Get the option prefix
	*
	* @access public
	* @return string
	*/
	static public function prefix() {
		return 'fl_theme_css_key';
	}

	/**
	* Get the url to the generated editor stylesheet
	*
	* @access public
	* @return string
	*/
	static public function css_url() {
		$cache_dir = FLCustomizer::get_cache_dir();
		$key       = get_option( self::prefix() . '-' . self::slug() );
		return $cache_dir['url'] . self::slug() . '-' . $key . '.css';
	}

	/**
	* Compile and write the editor stylesheet
	*
	* @access public
	* @return void
	*/
	static public function compile_css() {
		$cache_dir   = FLCustomizer::get_cache_dir();
		$new_key     = uniqid();
		$slug        = self::slug();
		$prefix      = self::prefix();
		$option_name = $prefix . '-' . $slug;
		$filename    = $cache_dir['path'] . $slug . '-' . $new_key . '.css';
		$vars        = FLCustomizer::_get_less_vars();

		/**
		 * The array of LESS file paths to compile into the WP editor styles.
		 */
		$paths = apply_filters( 'fl_theme_compile_editor_less_paths', array(
			FL_THEME_DIR . '/less/mixins.less',
			FL_THEME_DIR . '/less/editor.less',
		));

		// Loop over paths and get contents
		$css = FLCSS::paths_get_contents( $paths );

		// Filter less before compiling
		/**
		 * The compiled LESS/CSS string before URL token replacement and caching.
		 */
		$css = apply_filters( 'fl_theme_compile_less', $css );

		// Replace {FL_THEME_URL} placeholder.
		$css = FLCSS::replace_tokens( $css );

		// Compile LESS
		$css = FLCSS::compile_less( $vars . $css );

		/**
		 * Make sure $css is not a WP Error object.
		 */
		if ( is_wp_error( $css ) ) {
			return false;
		}

		// Compress
		if ( ! WP_DEBUG ) {
			$css = FLCSS::compress_css( $css );
		}

		// Save the new css.
		if ( 'file' === FLTheme::get_asset_enqueue_method() ) {
			fl_theme_filesystem()->file_put_contents( $filename, $css );
		} else {
			FLTheme::update_cached_css( 'editor', $css );
			return $css;
		}

		// Save the new css key.
		update_option( $option_name, $new_key );
	}

	/**
	* Clear any editor stylesheets in the cache directory
	*
	* @access public
	* @return void
	*/
	static public function clear_css_cache() {
		$dir_name  = basename( FL_THEME_DIR );
		$cache_dir = FLCustomizer::get_cache_dir();

		if ( ! empty( $cache_dir['path'] ) && stristr( $cache_dir['path'], $dir_name ) ) {

			$css = glob( $cache_dir['path'] . self::slug() . '-*' );

			foreach ( $css as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file );
				}
			}
		}
	}

	/**
	* Dump any existing editor stylesheets and recompile
	*
	* @access public
	* @return void
	*/
	static public function refresh_css() {
		if ( self::$refreshed ) {
			return;
		}
		self::clear_css_cache();
		self::compile_css();
		self::$refreshed = true;
	}

	/**
	* Enqueue the editor stylesheet.
	*
	* Fallback for WP < 5.8 only. From 5.8 on, inject_editor_styles()
	* (block_editor_settings_all) is the single source of editor CSS and reaches
	* every block editor surface, iframed or not, so running this enqueue too
	* would apply the same rules twice (once here, once via $settings['styles'])
	* on the non-iframed 5.8-6.x canvas. get_block_editor_settings() only exists
	* from 5.8, so its presence means the injection path is available.
	*
	* @access public
	* @return void
	*/
	static public function enqueue_styles() {
		if ( function_exists( 'get_block_editor_settings' ) ) {
			return;
		}
		self::refresh_css();
		$url = self::css_url();
		if ( 'file' === FLTheme::get_asset_enqueue_method() ) {
			wp_enqueue_style( 'fl-automator-editor', $url, array(), FL_THEME_VERSION );
		} else {
			wp_enqueue_style( 'bb-theme-style', get_stylesheet_uri() );
			wp_add_inline_style( 'bb-theme-style', FLTheme::get_cached_css( 'editor' ) );
		}
	}

	/**
	 * Inject the compiled editor stylesheet into the iframed block editor canvas.
	 *
	 * WP 7.0 renders the post-content editor inside an iframe; styles enqueued
	 * via enqueue_block_editor_assets stay in the parent frame. Pushing the CSS
	 * into block_editor_settings_all's $settings['styles'] forwards it into the
	 * iframe so the canvas inherits the theme's Customizer-driven typography.
	 *
	 * @param array $settings Block editor settings.
	 * @return array
	 */
	static public function inject_editor_styles( $settings ) {
		// block_editor_settings_all also fires on the REST settings endpoint
		// (GET /wp/v2/block-editor/), which runs in its own request where the
		// per-request $refreshed guard cannot dedupe. Only rebuild on the
		// editor page load itself; REST calls just read the existing cache.
		if ( ! wp_is_json_request() ) {
			self::refresh_css();
		}

		if ( 'file' === FLTheme::get_asset_enqueue_method() ) {
			$cache_dir = FLCustomizer::get_cache_dir();
			$key       = get_option( self::prefix() . '-' . self::slug() );
			$path      = $cache_dir['path'] . self::slug() . '-' . $key . '.css';
			$css       = fl_theme_filesystem()->file_get_contents( $path );
			if ( false === $css ) {
				// Cache file missing/unreadable (stale key, fresh install, or
				// externally cleared cache). We still degrade gracefully below,
				// but log it so a Times-New-Roman canvas is debuggable.
				error_log( 'FLWPEditor: editor CSS cache file missing: ' . $path );
			}
		} else {
			$css = FLTheme::get_cached_css( 'editor' );
		}

		if ( ! empty( $css ) ) {
			$settings['styles'][] = array( 'css' => $css );
		}

		return $settings;
	}
}
FLWPEditor::init();
