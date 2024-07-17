<?php
/**
 * Pluggable functions for WPCode.
 *
 * @package WPCode
 * @since 2.1.9
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpcode_get_snippet' ) ) {
	/**
	 * Load a snippet by id, WP_Post or array.
	 *
	 * @param array|int|WP_Post $snippet Load a snippet by id, WP_Post or array.
	 *
	 * @return WPCode_Snippet
	 */
	function wpcode_get_snippet( $snippet ) {
		return new WPCode_Snippet( $snippet );
	}
}

if ( ! function_exists( 'wpcode_get_post_type' ) ) {
	/**
	 * Get the post type we use for snippets.
	 *
	 * @return string
	 */
	function wpcode_get_post_type() {
		return 'wpcode';
	}
}
