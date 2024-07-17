<?php
/**
 * Plugin Name: Hostinger Tools
 * Plugin URI: https://hostinger.com
 * Description: Hostinger WordPress plugin.
 * Version: 3.0.2
 * Requires at least: 5.5
 * Requires PHP: 8.0
 * Author: Hostinger
 * License: GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Author URI: https://www.hostinger.com
 * Text Domain: hostinger
 * Domain Path: /languages
 *
 * @package Hostinger
 */

use Hostinger\Hostinger;
use Hostinger\Activator;
use Hostinger\Deactivator;
use Hostinger\WpMenuManager\Manager;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'HOSTINGER_VERSION' ) ) {
	define( 'HOSTINGER_VERSION', '3.0.2' );
}

if ( ! defined( 'HOSTINGER_ABSPATH' ) ) {
	define( 'HOSTINGER_ABSPATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'HOSTINGER_PLUGIN_FILE' ) ) {
	define( 'HOSTINGER_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'HOSTINGER_PLUGIN_URL' ) ) {
	define( 'HOSTINGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'HOSTINGER_ASSETS_URL' ) ) {
    define( 'HOSTINGER_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'assets/' );
}

if ( ! defined( 'HOSTINGER_VUE_ASSETS_URL' ) ) {
	define( 'HOSTINGER_VUE_ASSETS_URL', plugin_dir_url( __FILE__ ) . 'vue-frontend/dist' );
}

if ( ! defined( 'HOSTINGER_WP_CONFIG_PATH' ) ) {
	define( 'HOSTINGER_WP_CONFIG_PATH', ABSPATH . '.private/config.json' );
}

if ( ! defined( 'HOSTINGER_WP_TOKEN' ) ) {
	$hostinger_dir_parts        = explode( '/', __DIR__ );
	$hostinger_server_root_path = '/' . $hostinger_dir_parts[1] . '/' . $hostinger_dir_parts[2];
	define( 'HOSTINGER_WP_TOKEN', $hostinger_server_root_path . '/.api_token' );
}

if ( ! defined( 'HOSTINGER_REST_URI' ) ) {
	define( 'HOSTINGER_REST_URI', 'https://rest-hosting.hostinger.com' );
}

if ( ! defined( 'HOSTINGER_PLUGIN_SETTINGS_OPTION' ) ) {
	define( 'HOSTINGER_PLUGIN_SETTINGS_OPTION', 'hostinger_tools' );
}

if ( ! defined( 'HOSTINGER_PLUGIN_REST_API_BASE' ) ) {
    define( 'HOSTINGER_PLUGIN_REST_API_BASE', 'hostinger-tools-plugin/v1' );
}

if ( ! defined( 'HOSTINGER_PLUGIN_MINIMUM_PHP_VERSION' ) ) {
    define( 'HOSTINGER_PLUGIN_MINIMUM_PHP_VERSION', '8.0' );
}

if ( ! version_compare( phpversion(), HOSTINGER_PLUGIN_MINIMUM_PHP_VERSION, '>=' ) ) {

    add_action( 'admin_notices', function() {
        ?>
        <div class="notice notice-error is-dismissible hts-theme-settings">
            <p>
                <?php /* translators: %s: PHP version */ ?>
                <strong><?php echo __( 'Attention:', 'hostinger' ); ?></strong> <?php echo sprintf( __( 'The Hostinger plugin requires minimum PHP version of <b>%s</b>. ', 'hostinger' ), HOSTINGER_PLUGIN_MINIMUM_PHP_VERSION ); ?>
            </p>
            <p>
                <?php /* translators: %s: PHP version */ ?>
                <?php echo sprintf( __( 'You are running <b>%s</b> PHP version.', 'hostinger' ), phpversion() ); ?>
            </p>
        </div>
        <?php
        }
    );

    return;
}

$vendor_file = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if ( file_exists( $vendor_file ) ) {
	require_once $vendor_file;
}

/**
 * Plugin activation hook.
 */
function hostinger_activate(): void {
	Activator::activate();
}

/**
 * Plugin deactivation hook.
 */
function hostinger_deactivate(): void {
	Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'hostinger_activate' );
register_deactivation_hook( __FILE__, 'hostinger_deactivate' );

if( !function_exists('hostinger_load_menus') ) {
    function hostinger_load_menus(): void
    {
        $manager = Manager::getInstance();
        $manager->boot();
    }
}

if ( ! has_action( 'plugins_loaded', 'hostinger_load_menus' ) ) {
    add_action('plugins_loaded', 'hostinger_load_menus');
}

$hostinger = new Hostinger();
$hostinger->run();
