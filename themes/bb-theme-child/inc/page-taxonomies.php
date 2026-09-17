<?php
/**
 * Categories and tags on pages. Replaces the abandoned "Create And Assign Categories
 * For Pages" plugin (removed 2026-09-17); the only part of it this site used.
 */

add_action( 'init', function () {
	register_taxonomy_for_object_type( 'category', 'page' );
	register_taxonomy_for_object_type( 'post_tag', 'page' );
} );
