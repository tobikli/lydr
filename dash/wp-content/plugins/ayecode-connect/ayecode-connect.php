<?php

/**
 * Plugin Name: AyeCode Connect
 * Plugin URI: https://ayecode.io/
 * Description: A service plugin letting users connect AyeCode Services to their site.
 * Version: 1.3.0
 * Author: AyeCode
 * Author URI: https://ayecode.io
 * Requires at least: 4.7
 * Tested up to: 6.5
 *
 * Text Domain: ayecode-connect
 * Domain Path: /languages/
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( !defined( 'AYECODE_CONNECT_VERSION' ) ) {
    define( 'AYECODE_CONNECT_VERSION', '1.3.0' );
}

if ( !defined( 'AYECODE_CONNECT_SSL_VERIFY' ) ) {
    define( 'AYECODE_CONNECT_SSL_VERIFY', true );
}

if ( !defined( 'AYECODE_CONNECT_PLUGIN_DIR' ) ) {
    define( 'AYECODE_CONNECT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

add_action( 'plugins_loaded', 'ayecode_connect' );

/**
 * Sets up the client
 */
function ayecode_connect() {
    global $ayecode_connect;

    /**
     * The libraries required.
     */
    require_once plugin_dir_path( __FILE__ )  . '/vendor/autoload.php';

    //Include the client connection class
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-ayecode-connect.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-ayecode-connect-settings.php';

    //Prepare client args
    $args   = ayecode_connect_args();

    $ayecode_connect = new AyeCode_Connect( $args );

    //Call the init method to register routes. This should be called exactly once per client (Preferably before the init hook).
    $ayecode_connect->init();

    // Load textdomain
    load_plugin_textdomain( 'ayecode-connect', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

/**
 * The AyeCode Connect arguments.
 *
 * @return array
 */
function ayecode_connect_args() {
	$base_url = 'https://ayecode.io';

	return array(
		'remote_url'            => $base_url, //URL to the WP site containing the WP_Service_Provider class
		'connection_url'        => $base_url . '/connect', //This should be a custom page the authinticates a user the calls the WP_Service_Provider::connect_site() method
		'api_url'               => $base_url . '/wp-json/', //Might be different for you
		'api_namespace'         => 'ayecode/v1',
		'local_api_namespace'   => 'ayecode-connect/v1', //Should be unique for each client implementation
		'prefix'                => 'ayecode_connect', //A unique prefix for things (accepts alphanumerics and underscores). Each client on a given site should have it's own unique prefix
		'textdomain'            => 'ayecode-connect',
		'version'               => AYECODE_CONNECT_VERSION,
	);
}

/**
 * Add settings link to plugins page.
 * 
 * @param $links
 *
 * @return mixed
 */
function ayecode_connect_settings_link( $links ) {
    $settings_link = '<a href="admin.php?page=ayecode-connect">' . __( 'Settings','ayecode-connect' ) . '</a>';
    array_push( $links, $settings_link );
    return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_{$plugin}", 'ayecode_connect_settings_link' );

/**
 * Remove wp cron on deactivation if set.
 */
register_deactivation_hook( __FILE__, 'ayecode_connect_deactivation' );
function ayecode_connect_deactivation() {
    $args = ayecode_connect_args();
    $prefix = $args['prefix'];
    wp_clear_scheduled_hook( $prefix.'_callback' );

    // destroy support user
    $support_user = get_user_by( 'login', 'ayecode_connect_support_user' );
    if ( ! empty( $support_user ) && isset( $support_user->ID ) && ! empty( $support_user->ID ) ) {
        require_once(ABSPATH.'wp-admin/includes/user.php');
        $user_id = absint($support_user->ID);
        // get all sessions for user with ID $user_id
        $sessions = WP_Session_Tokens::get_instance($user_id);
        // we have got the sessions, destroy them all!
        $sessions->destroy_all();
        $reassign = user_can( 1, 'manage_options' ) ? 1 : null;
        wp_delete_user( $user_id, $reassign );
        if ( is_multisite() ) {
            if ( ! function_exists( 'wpmu_delete_user' ) ) { 
                require_once( ABSPATH . 'wp-admin/includes/ms.php' );
            }
            revoke_super_admin( $user_id );
            wpmu_delete_user( $user_id );
        }
    }

    // Try to remove the must use plugin. This should fail silently even if file is missing.
    wp_delete_file( WPMU_PLUGIN_DIR."/ayecode-connect-filter-fix.php" );
}

/**
 * Sync licenses if connected.
 */
function ayecode_connect_sync_licenses() {
    global $ayecode_connect;
    if(method_exists($ayecode_connect,'sync_licences')){
        $ayecode_connect->sync_licences();
    }
}
add_action( 'ayecode_connect_sync_licenses', 'ayecode_connect_sync_licenses' );

function ayecode_connect_demo_import_redirect( $plugin ){
    if ( $plugin == plugin_basename( __FILE__ ) && !empty( $_SERVER['HTTP_REFERER'] ) ) {
        $parts = parse_url($_SERVER['HTTP_REFERER']);

        if ( ! empty( $parts['query'] ) ) {
            parse_str( $parts['query'], $query );
        } else {
            $query = array();
        }

        if(!empty($query['ac-demo-import'])){
            $demo = sanitize_title_with_dashes($query['ac-demo-import']);
            wp_redirect(admin_url( "admin.php?page=ayecode-demo-content&ac-demo-import=".esc_attr($demo) ));
            exit;
        }
    }
}
//register_activation_hook( __FILE__, 'ayecode_connect_demo_import_redirect' );
add_action( 'activated_plugin',  'ayecode_connect_demo_import_redirect'  );