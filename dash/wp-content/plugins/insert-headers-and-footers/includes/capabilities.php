<?php
/**
 * Map capabilites with backwards compatibility.
 *
 * @package WPCode
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'map_meta_cap', 'wpcode_map_meta_cap', 10, 4 );

/**
 * Map capabilites with backwards compatibility.
 *
 * @param string[] $caps Primitive capabilities required of the user.
 * @param string   $cap Capability being checked.
 * @param int      $user_id The user ID.
 * @param array    $args Adds context to the capability check, typically
 *                          starting with an object ID.
 *
 * @return array
 */
function wpcode_map_meta_cap( $caps, $cap, $user_id, $args ) {

	$custom_capabilities = array(
		'wpcode_edit_php_snippets',
		'wpcode_edit_html_snippets',
		'wpcode_manage_conversion_pixels',
		'wpcode_file_editor',
		'wpcode_manage_settings',
	);

	if ( in_array( $cap, $custom_capabilities, true ) ) {
		return array( 'wpcode_edit_snippets' );
	}

	return $caps;
}

/**
 * Get an array of the custom capabilities that WPCode uses.
 *
 * @return array[]
 */
function wpcode_custom_capabilities() {
	return array(
		'wpcode_edit_text_snippets'       => array(
			'label'       => __( 'Edit Text/Blocks Snippets', 'insert-headers-and-footers' ),
			'description' => __( 'This enables users to edit just text & blocks snippets, no HTML code is allowed.', 'insert-headers-and-footers' ),
		),
		'wpcode_edit_html_snippets'       => array(
			'label'       => __( 'Edit HTML, JavaScript & CSS Snippets', 'insert-headers-and-footers' ),
			'description' => __( 'This enables users to add and manage HTML, JavaScript & CSS snippets but also Text & Blocks snippets.', 'insert-headers-and-footers' ),
		),
		'wpcode_edit_php_snippets'        => array(
			'label'       => __( 'Edit PHP Snippets', 'insert-headers-and-footers' ),
			'description' => __( 'This enables users to add and manage PHP snippets and all the other types of snippets.', 'insert-headers-and-footers' ),
		),
		'wpcode_manage_conversion_pixels' => array(
			'label'       => __( 'Manage Conversion Pixels Settings', 'insert-headers-and-footers' ),
			'description' => __( 'This enables users to manage the conversion pixels settings.', 'insert-headers-and-footers' ),
		),
		'wpcode_file_editor'              => array(
			'label'       => __( 'Use the File Editor', 'insert-headers-and-footers' ),
			'description' => __( 'This enables users to use the file editor.', 'insert-headers-and-footers' ),
		),
	);
}

/**
 * Return just the keys to avoid a gettext call that causes an endless loop with TranslatePress.
 *
 * @return string[]
 */
function wpcode_custom_capabilities_keys() {
	return array(
		'wpcode_edit_text_snippets',
		'wpcode_edit_html_snippets',
		'wpcode_edit_php_snippets',
		'wpcode_manage_conversion_pixels',
		'wpcode_file_editor',
	);
}

