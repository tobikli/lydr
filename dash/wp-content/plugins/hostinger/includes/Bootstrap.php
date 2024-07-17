<?php

namespace Hostinger;

use Hostinger\Admin\Ajax as AdminAjax;
use Hostinger\Admin\PluginSettings;
use Hostinger\Rest\Routes;
use Hostinger\Rest\SettingsRoutes;
use Hostinger\Admin\Assets as AdminAssets;
use Hostinger\Admin\Hooks as AdminHooks;
use Hostinger\Admin\Menu as AdminMenu;
use Hostinger\Admin\Redirects as AdminRedirects;
use Hostinger\Preview\Assets as PreviewAssets;

defined( 'ABSPATH' ) || exit;

class Bootstrap {
	protected Loader $loader;

	public function __construct() {
		$this->loader = new Loader();
	}

	public function run(): void {
		$this->load_dependencies();
		$this->set_locale();
		$this->loader->run();
	}

	private function load_dependencies(): void {
		$this->load_public_dependencies();

		if ( is_admin() ) {
			$this->load_admin_dependencies();
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			new Cli();
		}

        $plugin_settings = new PluginSettings();
        $plugin_options = $plugin_settings->get_plugin_settings();

		if ( $plugin_options->get_maintenance_mode() ) {
			require_once HOSTINGER_ABSPATH . 'includes/ComingSoon.php';
		}
	}

	private function set_locale() {
		$plugin_i18n = new I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	private function load_admin_dependencies(): void {
		new AdminAssets();
		new AdminHooks();
		new AdminMenu();
		new AdminRedirects();
		new AdminRedirects();
		new AdminAjax();
	}

	private function load_public_dependencies(): void {
		new PreviewAssets();
		new Hooks();

        $plugin_settings = new PluginSettings();

        $settings_routes = new SettingsRoutes( $plugin_settings );
        $routes = new Routes( $settings_routes );
        $routes->init();
	}
}
