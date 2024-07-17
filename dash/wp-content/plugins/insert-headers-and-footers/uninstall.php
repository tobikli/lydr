<?php
/**
 * Uninstall WPCode.
 *
 * Remove:
 * - custom capabilities.
 *
 * @package WPCode
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// If the function already exists we shouldn't run the uninstall as another version of the plugin is active.
if ( function_exists( 'WPCode' ) ) {
	return;
}

require_once 'ihaf.php';

if ( class_exists( 'WPCode_Capabilities' ) ) {
	// Remove custom capabilities on uninstall.
	WPCode_Capabilities::uninstall();
}

if ( class_exists( 'WPCode_Notifications' ) ) {
	WPCode_Notifications::delete_notifications_data();
}

if ( function_exists( 'wp_unschedule_hook' ) ) {
	wp_unschedule_hook( 'wpcode_usage_tracking_cron' );
}

delete_option( 'wpcode_send_usage_last_run' );
delete_option( 'wpcode_usage_tracking_config' );

// Let's see if the uninstall_data option is set.
$settings = get_option( 'wpcode_settings', array() );

if ( ! empty( $settings['uninstall_data'] ) ) {
	// Delete the revisions table.
	global $wpdb;
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wpcode_revisions" );

	// Delete settings.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'wpcode\_%'" );
	// Delete ihaf data.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'ihaf\_%'" );
	// Delete plugin user meta.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'wpcode\_%'" );
	// Delete post meta.
	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE 'wpcode\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key LIKE '_wpcode\_%'" );

	// Remove any transients we've left behind.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_wpcode\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_wpcode\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_wpcode\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_wpcode\_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_wpcode\_transient\_%'" );

	// Delete wpcode post types.
	$wpcode_posts = get_posts(
		array(
			'post_type'   => array( 'wpcode', 'wpcode-blocks' ),
			'post_status' => array( 'publish', 'draft', 'trash' ),
			'numberposts' => - 1,
			'fields'      => 'ids',
		)
	);

	if ( $wpcode_posts ) {
		foreach ( $wpcode_posts as $wpcode_post ) {
			wp_delete_post( $wpcode_post, true );
		}
	}

	if ( function_exists( 'wpcode_register_taxonomies' ) ) {
		wpcode_register_taxonomies();
	}

	// Delete all taxonomy terms.
	$wpcode_taxonomies = array(
		'wpcode_type',
		'wpcode_location',
		'wpcode_tags',
	);
	foreach ( $wpcode_taxonomies as $wpcode_taxonomy ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $wpcode_taxonomy,
				'hide_empty' => false,
				'fields'     => 'ids',
			)
		);
		if ( $terms ) {
			foreach ( $terms as $wpcode_term ) {
				wp_delete_term( $wpcode_term, $wpcode_taxonomy );
			}
		}
	}


	global $wp_filesystem;
	// Remove uploaded files.
	$uploads_directory = wp_upload_dir();

	if ( empty( $uploads_directory['error'] ) ) {
		$wp_filesystem->rmdir( $uploads_directory['basedir'] . '/wpcode/', true );
		$wp_filesystem->rmdir( $uploads_directory['basedir'] . '/wpcode-logs/', true );
	}

	// Remove translation files.
	$languages_directory = defined( 'WP_LANG_DIR' ) ? trailingslashit( WP_LANG_DIR ) : trailingslashit( WP_CONTENT_DIR ) . 'languages/';
	$translations        = glob( wp_normalize_path( $languages_directory . 'plugins/wpcode-*' ) );

	if ( ! empty( $translations ) ) {
		foreach ( $translations as $file ) {
			$wp_filesystem->delete( $file );
		}
	}

	$translations = glob( wp_normalize_path( $languages_directory . 'plugins/insert-headers-and-footers-*' ) );

	if ( ! empty( $translations ) ) {
		foreach ( $translations as $file ) {
			$wp_filesystem->delete( $file );
		}
	}
}
