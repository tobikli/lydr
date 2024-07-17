<?php

namespace Hostinger\EasyOnboarding;

use Hostinger\EasyOnboarding\Bootstrap;

defined( 'ABSPATH' ) || exit;

class EasyOnboarding {
	protected string $plugin_name = 'Hostinger Easy Onboarding';
	protected string $version;

	public function bootstrap(): void {
		$this->version = $this->get_plugin_version();
		$bootstrap     = new Bootstrap();
		$bootstrap->run();
	}

	public function run(): void {
		$this->bootstrap();
	}

	/**
	 * Define constant
	 *
	 * @param string $name Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( string $name, $value ): void {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private function get_plugin_version(): string {
		if ( defined( 'HOSTINGER_EASY_ONBOARDING_VERSION' ) ) {
			return HOSTINGER_EASY_ONBOARDING_VERSION;
		}

		return '1.0.0';
	}
}
