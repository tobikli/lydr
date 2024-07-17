<?php
namespace Hostinger\EasyOnboarding;

use Hostinger\EasyOnboarding\Config;
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Updates {
	private Config $config_handler;
	private const DEFAULT_PLUGIN_UPDATE_URI = 'https://hostinger-wp-updates.com?action=get_metadata&slug=hostinger-easy-onboarding';
	private const CANARY_PLUGIN_UPDATE_URI = 'https://hostinger-canary-wp-updates.com?action=get_metadata&slug=hostinger-easy-onboarding';


	public function __construct() {
		$this->config_handler = new Config();
		$this->updates();
	}

	private function should_use_canary_uri(): bool {
		return isset( $_SERVER['H_PLATFORM'] ) && $_SERVER['H_PLATFORM'] === 'Hostinger' && isset( $_SERVER['H_CANARY'] ) && $_SERVER['H_CANARY'] === true;
	}

	private function get_plugin_update_uri( string $default = self::DEFAULT_PLUGIN_UPDATE_URI ): string {
		if ( $this->should_use_canary_uri() ) {
			return self::CANARY_PLUGIN_UPDATE_URI;
		}

		return $this->config_handler->get_config_value( 'easy_onboarding_plugin_update_uri', $default );
	}

	public function updates(): void {
		$plugin_updater_uri = $this->get_plugin_update_uri();

		if ( class_exists( PucFactory::class ) ) {
			$hts_update_checker = PucFactory::buildUpdateChecker(
				$plugin_updater_uri,
				HOSTINGER_EASY_ONBOARDING_ABSPATH . 'hostinger-easy-onboarding.php',
				'hostinger-easy-onboarding'
			);
		}
	}

}
