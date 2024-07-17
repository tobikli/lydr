<?php

namespace Hostinger\Admin;


use Hostinger\Admin\Menu;
use Hostinger\WpMenuManager\Menus;
use Hostinger\Helper;
use Hostinger\WpHelper\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class Hostinger_Admin_Assets
 *
 * Handles the enqueueing of styles and scripts for the Hostinger admin pages.
 */
class Assets {
	/**
	 * @var Helper Instance of the Hostinger_Helper class.
	 */
	private Helper $helper;

	/**
	 * @var Utils
	 */
	private Utils $utils;

	public function __construct() {
		$this->helper = new Helper();
		$this->utils  = new Utils();

		// Load assets only on Hostinger admin pages.
		if ( $this->utils->isThisPage( 'wp-admin/admin.php?page=' . Menu::MENU_SLUG ) || $this->utils->isThisPage( 'wp-admin/admin.php?page=' . Menus::MENU_SLUG ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );
		}
		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'global_styles' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'global_scripts' ] );
		}
	}

	/**
	 * Enqueues styles for the Hostinger admin pages.
	 */
	public function admin_styles(): void {
		// Vue frontend styles.
		wp_enqueue_style(
			'hostinger_tools_styles',
			HOSTINGER_VUE_ASSETS_URL . '/main.min.css',
			[],
			HOSTINGER_VERSION
		);

		// Plugin rating styles.
		wp_enqueue_style(
			'hostinger_rating_styles',
			HOSTINGER_ASSETS_URL . '/css/plugin-rating.min.css',
			[],
			HOSTINGER_VERSION
		);
	}

	public function global_styles(): void {
		wp_enqueue_style(
			'hostinger_tools_global_styles',
			HOSTINGER_ASSETS_URL . 'css/hostinger-global.min.css',
			[],
			HOSTINGER_VERSION
		);
	}

	public function global_scripts(): void {
		wp_enqueue_script(
			'hostinger_tools_global_scripts',
			HOSTINGER_ASSETS_URL . 'js/hostinger-global-scripts.min.js',
			[],
			HOSTINGER_VERSION
		);
	}

	/**
	 * Enqueues scripts for the Hostinger admin pages.
	 */
	public function admin_scripts(): void {
		global $wp_version;

		wp_enqueue_script(
			'hostinger_tools_main_scripts',
			HOSTINGER_VUE_ASSETS_URL . '/main.min.js',
			array(
				'jquery',
				'wp-i18n',
			),
			HOSTINGER_VERSION,
			false
		);

		wp_localize_script(
			'hostinger_tools_main_scripts',
			'hostinger_tools_data',
			array(
				'home_url'      => home_url(),
				'site_url'      => get_site_url(),
				'plugin_url'    => HOSTINGER_PLUGIN_URL,
				'translations'  => array(
					'routes_hostinger_tools'                                  => __( 'Hostinger Tools', 'hostinger' ),
					'hostinger_tools_disable_public_access'                   => __( 'Disable public access to the site (WordPress admins will still be able to access)', 'hostinger' ),
					'hostinger_tools_skip_link_maintenance_mode'              => __( 'Skip-link that bypasses the maintenance mode', 'hostinger' ),
					'hostinger_tools_reset_link'                              => __( 'Reset link', 'hostinger' ),
					'hostinger_tools_disable_xml_rpc'                         => __( 'Disable XML-RPC', 'hostinger' ),
					'hostinger_tools_xml_rpc_description'                     => __( 'XML-RPC allows apps to connect to your WordPress site, but might expose your site\'s security. Disable this feature if you don\'t need it', 'hostinger' ),
					'hostinger_tools_force_https'                             => __( 'Force HTTPs', 'hostinger' ),
					'hostinger_tools_force_https_description'                 => __( 'Redirects all HTTP URLs to HTTPS sites', 'hostinger' ),
					'hostinger_tools_force_www'                               => __( 'Force WWW', 'hostinger' ),
					'hostinger_tools_force_www_description'                   => __( 'Redirects all WWW URLs to non-WWW ones', 'hostinger' ),
					'hostinger_tools_force_www_description_not_available'     => __( 'WWW and non-WWW domain records are not pointing to the same host. Redirect not possible.', 'hostinger' ),
					'hostinger_tools_php_version'                             => __( 'PHP version', 'hostinger' ),
					'hostinger_tools_wordpress_version'                       => __( 'WordPress version', 'hostinger' ),
					'hostinger_tools_php_version_description'                 => __( 'Various updates and fixes available in the newest version.', 'hostinger' ),
					'hostinger_tools_running_latest_version'                  => __( 'Running the latest version', 'hostinger' ),
					'hostinger_tools_update_to'                               => __( 'Update to', 'hostinger' ),
					'hostinger_tools_update_to_wordpress_version_description' => __( 'For improved security, ensure you use the latest version of WordPress', 'hostinger' ),
					'hostinger_tools_maintenance'                             => __( 'Maintenance', 'hostinger' ),
					'hostinger_tools_preview_my_website'                      => __( 'Preview my website', 'hostinger' ),
					'hostinger_tools_security'                                => __( 'Security', 'hostinger' ),
					'hostinger_tools_redirects'                               => __( 'Redirects', 'hostinger' ),
					'hostinger_tools_maintenance_mode'                        => __( 'Maintenance mode', 'hostinger' ),
					'hostinger_tools_bypass_link'                             => __( 'Bypass link', 'hostinger' ),
					'xml_security_modal_description'                          => __( ' Turning on XML-RPC might make your site less secure. Do you want to proceed?', 'hostinger' ),
					'xml_security_modal_title'                                => __( 'Disclaimer', 'hostinger' ),
					'xml_security_modal_cancel'                               => __( 'Cancel', 'hostinger' ),
					'xml_security_modal_proceed_anyway'                       => __( 'Proceed anyway', 'hostinger' ),
					'bypass_link_reset_modal_title'                           => __( 'Bypass link reset', 'hostinger' ),
					'bypass_link_reset_modal_description'                     => __( 'This will invalidate the currently generated link in use. This action cannot be undone, are you sure you want to proceed?', 'hostinger' ),
					'bypass_link_reset_modal_cancel'                          => __( 'Cancel', 'hostinger' ),
					'bypass_link_reset_modal_reset_link'                      => __( 'Reset link', 'hostinger' ),
					'bypass_link_reset_success'                               => __( 'Link has been reset', 'hostinger' ),
				),
				'rest_base_url' => esc_url_raw( rest_url() ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
				'wp_version'    => $wp_version,
				'php_version'   => phpversion(),
			)
		);
	}
}
