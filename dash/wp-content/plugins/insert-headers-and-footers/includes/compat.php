<?php

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'str_contains' ) ) {
	/**
	 * Polyfill for str_contains() function added in PHP 8.0.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle The substring to search for in the haystack.
	 *
	 * @return bool True if $needle is in $haystack, otherwise false.
	 */
	function str_contains( $haystack, $needle ) {
		return ( '' === $needle || false !== strpos( $haystack, $needle ) );
	}
}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
	/**
	 * Determines whether the current request is a WordPress Ajax request.
	 *
	 * @return bool True if it's a WordPress Ajax request, false otherwise.
	 * @since 4.7.0
	 *
	 */
	function wp_doing_ajax() {
		/**
		 * Filters whether the current request is a WordPress Ajax request.
		 *
		 * @param bool $wp_doing_ajax Whether the current request is a WordPress Ajax request.
		 *
		 * @since 4.7.0
		 *
		 */
		return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
	}
}

if ( ! function_exists( 'wp_doing_cron' ) ) {
	/**
	 * Determines whether the current request is a WordPress cron request.
	 *
	 * @return bool True if it's a WordPress cron request, false otherwise.
	 * @since 4.8.0
	 *
	 */
	function wp_doing_cron() {
		/**
		 * Filters whether the current request is a WordPress cron request.
		 *
		 * @param bool $wp_doing_cron Whether the current request is a WordPress cron request.
		 *
		 * @since 4.8.0
		 *
		 */
		return apply_filters( 'wp_doing_cron', defined( 'DOING_CRON' ) && DOING_CRON );
	}
}

if ( ! function_exists( 'sanitize_textarea_field' ) ) {
	/**
	 * Sanitizes a multiline string from user input or from the database.
	 *
	 * The function is like sanitize_text_field(), but preserves
	 * new lines (\n) and other whitespace, which are legitimate
	 * input in textarea elements.
	 *
	 * @param string $str String to sanitize.
	 *
	 * @return string Sanitized string.
	 * @see sanitize_text_field()
	 *
	 * @since 4.7.0
	 *
	 */
	function sanitize_textarea_field( $str ) {
		$filtered = _sanitize_text_fields( $str, true );

		/**
		 * Filters a sanitized textarea field string.
		 *
		 * @param string $filtered The sanitized string.
		 * @param string $str The string prior to being sanitized.
		 *
		 * @since 4.7.0
		 *
		 */
		return apply_filters( 'sanitize_textarea_field', $filtered, $str );
	}
}

if ( ! function_exists( '_sanitize_text_fields' ) ) {
	/**
	 * Internal helper function to sanitize a string from user input or from the database.
	 *
	 * @param string $str String to sanitize.
	 * @param bool   $keep_newlines Optional. Whether to keep newlines. Default: false.
	 *
	 * @return string Sanitized string.
	 * @since 4.7.0
	 * @access private
	 *
	 */
	function _sanitize_text_fields( $str, $keep_newlines = false ) {
		if ( is_object( $str ) || is_array( $str ) ) {
			return '';
		}

		$str = (string) $str;

		$filtered = wp_check_invalid_utf8( $str );

		if ( strpos( $filtered, '<' ) !== false ) {
			$filtered = wp_pre_kses_less_than( $filtered );
			// This will strip extra whitespace for us.
			$filtered = wp_strip_all_tags( $filtered, false );

			/*
			 * Use HTML entities in a special case to make sure that
			 * later newline stripping stages cannot lead to a functional tag.
			 */
			$filtered = str_replace( "<\n", "&lt;\n", $filtered );
		}

		if ( ! $keep_newlines ) {
			$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
		}
		$filtered = trim( $filtered );

		// Remove percent-encoded characters.
		$found = false;
		while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
			$filtered = str_replace( $match[0], '', $filtered );
			$found    = true;
		}

		if ( $found ) {
			// Strip out the whitespace that may now exist after removing percent-encoded characters.
			$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
		}

		return $filtered;
	}
}

add_filter( 'pto/posts_orderby/ignore', 'wpcode_exclude_post_types_order', 15, 3 );

/**
 * Exclude the wpcode post type from the Post Types Order plugin.
 *
 * @param bool     $ignore Whether to ignore the post type.
 * @param string   $order_by The order by param.
 * @param WP_Query $query The WP_Query instance.
 *
 * @return bool
 */
function wpcode_exclude_post_types_order( $ignore, $order_by, $query ) {
	if ( ! method_exists( $query, 'get' ) ) {
		return $ignore;
	}

	if ( 'wpcode' === $query->get( 'post_type' ) ) {
		$ignore = true;
	}

	return $ignore;
}
